<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Catalogs\Machinery;
use App\Models\Catalogs\Status;
use App\Models\ReportStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

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
            ->with(['machinery.field', 'machinery.machineryType', 'status', 'user'])
            ->when($request->integer('field_id'), function ($q, $fieldId) {
                $q->whereHas('machinery', fn ($mq) => $mq->where('field_id', $fieldId));
            })
            ->when($request->integer('machinery_type_id'), function ($q, $typeId) {
                $q->whereHas('machinery', fn ($mq) => $mq->where('machinery_type_id', $typeId));
            })
            ->when($request->integer('status_id'), fn ($q, $statusId) => $q->where('status_id', $statusId))
            ->when($request->date('from'), fn ($q, $from) => $q->whereDate('created_at', '>=', $from))
            ->when($request->date('to'), fn ($q, $to) => $q->whereDate('created_at', '<=', $to))
            ->orderByDesc('created_at');

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
}
