<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Catalogs\Machinery;
use App\Models\Catalogs\MachineryType;
use App\Models\Catalogs\Status;
use App\Models\ReportStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Toda la agregación acá es SQL (GROUP BY / WHERE) o se apoya en relaciones
 * Eloquent eficientes (latestOfMany) — nunca se traen miles de filas de
 * report_status para filtrar/agrupar en PHP (hallazgo 5.7 del análisis).
 */
class DashboardController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        if ($request->wantsJson()) {
            return $this->summary();
        }

        // Sin tipo seleccionado → pantalla de selector
        if (! $request->has('machinery_type_id')) {
            $types = MachineryType::query()
                ->active()
                ->withCount(['machinery' => fn ($q) => $q->active()])
                ->orderBy('description')
                ->get();

            return view('dashboard.selector', compact('types'));
        }

        return view('dashboard.index');
    }

    /**
     * Estado actual de la flota: último reporte conocido de cada máquina
     * activa, agrupado por estado. Solo toca las ~50 máquinas activas, no
     * las 159k filas históricas de report_status.
     */
    public function summary(): JsonResponse
    {
        $machines = Machinery::query()->active()->with('latestReport.status')->get();

        $byStatus = [];
        foreach ($machines as $machine) {
            $status = $machine->latestReport?->status;
            $key = $status?->description ?? 'Sin reportes';

            $byStatus[$key] ??= [
                'label' => $key,
                'class' => $status?->semanticClass() ?? 'neutral',
                'count' => 0,
            ];
            $byStatus[$key]['count']++;
        }

        return response()->json([
            'active_machines' => $machines->count(),
            'reports_today' => ReportStatus::whereDate('created_at', today())->count(),
            'by_status' => array_values($byStatus),
        ]);
    }

    /**
     * Listado filtrable y paginado — desktop-only (ver decisiones-proyecto.md).
     */
    public function reports(Request $request): JsonResponse
    {
        $query = ReportStatus::query()
            ->select('report_status.*')
            ->join('status', 'status.id', '=', 'report_status.status_id')
            ->join('machinery', 'machinery.id', '=', 'report_status.machinery_id')
            ->with(['machinery.field', 'machinery.machineryType', 'status', 'user'])
            ->when($request->integer('field_id'), function ($q, $fieldId) {
                $q->whereHas('machinery', fn ($mq) => $mq->where('field_id', $fieldId));
            })
            ->when($request->integer('machinery_type_id'), function ($q, $typeId) {
                $q->whereHas('machinery', fn ($mq) => $mq->where('machinery_type_id', $typeId));
            })
            ->when($request->integer('status_id'), fn ($q, $statusId) => $q->where('report_status.status_id', $statusId))
            ->when($request->date('from'), fn ($q, $from) => $q->whereDate('report_status.created_at', '>=', $from))
            ->when($request->date('to'), fn ($q, $to) => $q->whereDate('report_status.created_at', '<=', $to))
            ->orderByRaw("CASE
                WHEN status.description LIKE '%NO OPERATIVA%' THEN 2
                WHEN status.description LIKE '%LIMITAC%' THEN 1
                WHEN status.description LIKE '%OPERATIVA%' THEN 0
                ELSE 3
            END")
            ->orderBy('machinery.description');

        $reports = $query->paginate(25)->withQueryString();

        return response()->json([
            'data' => $reports->getCollection()->map(fn (ReportStatus $r) => [
                'id' => $r->id,
                'machinery' => $r->machinery->description,
                'field' => $r->machinery->field->description,
                'machinery_type' => $r->machinery->machineryType->description,
                'status' => $r->status->description,
                'status_class' => $r->status->semanticClass(),
                'observation' => $r->observation,
                'user' => $r->user->username,
                'created_at' => $r->created_at,
            ]),
            'current_page' => $reports->currentPage(),
            'last_page' => $reports->lastPage(),
            'total' => $reports->total(),
        ]);
    }

    /**
     * Registros agrupados por sesión (yarda + ventana de 30 min).
     * Una sesión = todas las máquinas reportadas en el mismo bloque horario.
     */
    public function sessions(Request $request): JsonResponse
    {
        $from = $request->date('from') ?? now()->subDays(6)->startOfDay();
        $to   = $request->date('to')   ?? now()->endOfDay();

        $rows = DB::table('report_status')
            ->join('machinery', 'machinery.id', '=', 'report_status.machinery_id')
            ->join('status',    'status.id',    '=', 'report_status.status_id')
            ->join('field',     'field.id',     '=', 'machinery.field_id')
            ->when($request->integer('field_id'),          fn ($q, $id) => $q->where('machinery.field_id', $id))
            ->when($request->integer('machinery_type_id'), fn ($q, $id) => $q->where('machinery.machinery_type_id', $id))
            ->whereBetween('report_status.created_at', [$from, $to])
            ->selectRaw("
                machinery.field_id,
                field.description         AS field_name,
                FLOOR(UNIX_TIMESTAMP(report_status.created_at) / 1800) AS bucket,
                report_status.created_at,
                machinery.description     AS machinery_name,
                status.description        AS status_name,
                CASE
                    WHEN status.description LIKE '%NO OPERATIVA%' THEN 2
                    WHEN status.description LIKE '%LIMITAC%'      THEN 1
                    WHEN status.description LIKE '%OPERATIVA%'    THEN 0
                    ELSE 3
                END AS status_order,
                report_status.observation
            ")
            ->orderByRaw('bucket DESC, status_order ASC')
            ->orderBy('machinery.description')
            ->get();

        $sessions = [];
        foreach ($rows as $row) {
            $key = $row->field_id . '_' . $row->bucket;

            if (! isset($sessions[$key])) {
                $sessions[$key] = [
                    'field'        => $row->field_name,
                    'session_time' => $row->created_at,
                    'summary'      => ['good' => 0, 'warn' => 0, 'critical' => 0],
                    'machines'     => [],
                ];
            } elseif ($row->created_at < $sessions[$key]['session_time']) {
                $sessions[$key]['session_time'] = $row->created_at;
            }

            $class = match ((int) $row->status_order) {
                2       => 'critical',
                1       => 'warn',
                0       => 'good',
                default => 'neutral',
            };

            $summaryKey = in_array($class, ['good', 'warn', 'critical']) ? $class : 'good';
            $sessions[$key]['summary'][$summaryKey]++;

            $sessions[$key]['machines'][] = [
                'machinery'    => $row->machinery_name,
                'status'       => $row->status_name,
                'status_class' => $class,
                'observation'  => $row->observation,
            ];
        }

        return response()->json(array_values($sessions));
    }

    /**
     * Reportes por día en el rango, agrupados por estado — para la gráfica.
     */
    public function chart(Request $request): JsonResponse
    {
        $from = $request->date('from') ?? now()->subDays(13)->startOfDay();
        $to = $request->date('to') ?? now()->endOfDay();

        $rows = DB::table('report_status')
            ->join('machinery', 'machinery.id', '=', 'report_status.machinery_id')
            ->join('status', 'status.id', '=', 'report_status.status_id')
            ->when($request->integer('field_id'), fn ($q, $fieldId) => $q->where('machinery.field_id', $fieldId))
            ->when($request->integer('machinery_type_id'), fn ($q, $typeId) => $q->where('machinery.machinery_type_id', $typeId))
            ->when($request->integer('status_id'), fn ($q, $statusId) => $q->where('report_status.status_id', $statusId))
            ->whereBetween('report_status.created_at', [$from, $to])
            ->selectRaw('DATE(report_status.created_at) as day, status.description as status_name, COUNT(*) as total')
            ->groupBy('day', 'status_name')
            ->orderBy('day')
            ->get();

        $days = $rows->pluck('day')->unique()->sort()->values();
        $statusNames = $rows->pluck('status_name')->unique()->values();
        $statusModels = Status::whereIn('description', $statusNames)->get()->keyBy('description');

        $lookup = [];
        foreach ($rows as $row) {
            $lookup[$row->day][$row->status_name] = (int) $row->total;
        }

        $datasets = $statusNames->map(fn ($name) => [
            'label' => $name,
            'class' => $statusModels->get($name)?->semanticClass() ?? 'neutral',
            'data' => $days->map(fn ($day) => $lookup[$day][$name] ?? 0)->values(),
        ]);

        return response()->json([
            'labels' => $days,
            'datasets' => $datasets,
        ]);
    }

    /**
     * Exporta los registros filtrados como CSV (Excel lo abre nativamente).
     * Columnas: # Horario Máquina Predio Limitante Semana Estatus Día Hora
     */
    public function export(Request $request): StreamedResponse
    {
        $from = $request->date('from') ?? now()->subDays(6)->startOfDay();
        $to   = $request->date('to')   ?? now()->endOfDay();

        $rows = DB::table('report_status')
            ->join('machinery', 'machinery.id', '=', 'report_status.machinery_id')
            ->join('status',    'status.id',    '=', 'report_status.status_id')
            ->join('field',     'field.id',     '=', 'machinery.field_id')
            ->when($request->integer('field_id'),          fn ($q, $id) => $q->where('machinery.field_id', $id))
            ->when($request->integer('machinery_type_id'), fn ($q, $id) => $q->where('machinery.machinery_type_id', $id))
            ->when($request->integer('status_id'),         fn ($q, $id) => $q->where('report_status.status_id', $id))
            ->whereBetween('report_status.created_at', [$from, $to])
            ->selectRaw("
                report_status.created_at,
                machinery.description AS machinery_name,
                field.description     AS field_name,
                report_status.observation,
                status.description    AS status_name,
                CASE
                    WHEN status.description LIKE '%NO OPERATIVA%' THEN 2
                    WHEN status.description LIKE '%LIMITAC%'      THEN 1
                    WHEN status.description LIKE '%OPERATIVA%'    THEN 0
                    ELSE 3
                END AS status_order
            ")
            ->orderByRaw('report_status.created_at DESC, status_order ASC')
            ->orderBy('machinery.description')
            ->get();

        $filename = 'reporte-flota-' . now()->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // BOM para que Excel abra UTF-8 correctamente
            fputcsv($out, ['#', 'Horario', 'Máquina', 'Predio', 'Limitante', 'Semana', 'Estatus', 'Día', 'Hora']);

            $i = 1;
            foreach ($rows as $row) {
                $dt = new \DateTime($row->created_at);
                fputcsv($out, [
                    $i++,
                    $dt->format('Y-m-d H:i:s'),
                    $row->machinery_name,
                    $row->field_name,
                    $row->observation ?? '',
                    $dt->format('W'),  // semana ISO
                    $row->status_name,
                    $dt->format('N'),  // día de semana (1=lun … 7=dom)
                    $dt->format('G'),  // hora sin cero inicial
                ]);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
