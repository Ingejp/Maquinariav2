<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Http\Requests\Report\ReportStatusRequest;
use App\Http\Resources\Catalogs\CatalogResource;
use App\Models\Catalogs\Field;
use App\Models\Catalogs\Machinery;
use App\Models\Catalogs\MachineryType;
use App\Models\Catalogs\Status;
use App\Models\ReportStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    /**
     * Página (isla Vue con su propio router interno) o, si viene por Axios,
     * el listado de yardas activas para el primer paso del flujo.
     */
    public function index(Request $request): View|JsonResponse
    {
        if ($request->wantsJson()) {
            $fields = Field::query()
                ->active()
                ->withCount(['machinery' => fn ($query) => $query->active()])
                ->orderBy('description')
                ->get();

            return response()->json($fields->map(fn (Field $field) => [
                'id' => $field->id,
                'description' => $field->description,
                'machinery_count' => $field->machinery_count,
            ]));
        }

        return view('report.index');
    }

    /**
     * Tipos de maquinaria con al menos una máquina activa en esa yarda.
     */
    public function machineryTypes(Field $field): JsonResponse
    {
        $types = MachineryType::query()
            ->active()
            ->whereHas('machinery', function ($query) use ($field) {
                $query->where('field_id', $field->id)->active();
            })
            ->orderBy('description')
            ->get();

        return response()->json(CatalogResource::collection($types)->resolve());
    }

    /**
     * Máquinas activas de esa yarda + tipo, con su último reporte conocido.
     */
    public function machines(Field $field, MachineryType $machineryType): JsonResponse
    {
        $machines = Machinery::query()
            ->active()
            ->where('field_id', $field->id)
            ->where('machinery_type_id', $machineryType->id)
            ->with('latestReport.status')
            ->orderBy('description')
            ->get();

        return response()->json($machines->map(fn (Machinery $machine) => [
            'id' => $machine->id,
            'description' => $machine->description,
            'last_report' => $machine->latestReport ? [
                'status' => $machine->latestReport->status->description,
                'reported_at' => $machine->latestReport->created_at,
            ] : null,
        ]));
    }

    /**
     * Estados disponibles para el paso final de registro.
     */
    public function statuses(): JsonResponse
    {
        $statuses = Status::query()->active()->orderBy('description')->get();

        return response()->json(CatalogResource::collection($statuses)->resolve());
    }

    public function store(ReportStatusRequest $request): JsonResponse
    {
        ReportStatus::create([
            ...$request->validated(),
            'user_id' => $request->user()->id,
        ]);

        return response()->json(['message' => 'Reporte registrado correctamente.'], 201);
    }
}
