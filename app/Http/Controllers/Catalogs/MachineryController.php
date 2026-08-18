<?php

namespace App\Http\Controllers\Catalogs;

use App\Http\Controllers\Controller;
use App\Http\Requests\Catalogs\MachineryRequest;
use App\Http\Resources\Catalogs\CatalogResource;
use App\Http\Resources\Catalogs\MachineryResource;
use App\Models\Catalogs\ConfigStatus;
use App\Models\Catalogs\Field;
use App\Models\Catalogs\Machinery;
use App\Models\Catalogs\MachineryType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\View\View;

class MachineryController extends Controller
{
    public function index(Request $request): View|AnonymousResourceCollection
    {
        if ($request->wantsJson()) {
            $query = Machinery::query()->with(['machineryType', 'field', 'configStatus']);

            if ($search = trim((string) $request->string('search'))) {
                $query->where('description', 'like', "%{$search}%");
            }

            if ($fieldId = $request->integer('field_id')) {
                $query->where('field_id', $fieldId);
            }

            if ($typeId = $request->integer('machinery_type_id')) {
                $query->where('machinery_type_id', $typeId);
            }

            return MachineryResource::collection($query->orderBy('description')->get());
        }

        return view('catalogs.machinery');
    }

    public function options(): JsonResponse
    {
        return response()->json([
            'fields' => CatalogResource::collection(Field::query()->active()->orderBy('description')->get())->resolve(),
            'machinery_types' => CatalogResource::collection(MachineryType::query()->active()->orderBy('description')->get())->resolve(),
        ]);
    }

    public function store(MachineryRequest $request): JsonResponse
    {
        $record = Machinery::create([
            ...$request->validated(),
            'configuration_status_id' => ConfigStatus::ACTIVE,
        ]);

        return (new MachineryResource($record->load(['machineryType', 'field', 'configStatus'])))
            ->response()
            ->setStatusCode(201);
    }

    public function update(MachineryRequest $request, int $id): MachineryResource
    {
        $record = Machinery::findOrFail($id);
        $record->update($request->validated());

        return new MachineryResource($record->load(['machineryType', 'field', 'configStatus']));
    }

    public function toggleStatus(int $id): MachineryResource
    {
        $record = Machinery::findOrFail($id);

        $record->update([
            'configuration_status_id' => $record->isActive() ? ConfigStatus::DISABLED : ConfigStatus::ACTIVE,
        ]);

        return new MachineryResource($record->load(['machineryType', 'field', 'configStatus']));
    }

    public function destroy(int $id): JsonResponse
    {
        $record = Machinery::findOrFail($id);

        $reports = $record->reportStatuses()->count();

        if ($reports > 0) {
            return response()->json([
                'message' => 'No se puede eliminar: esta máquina tiene reportes registrados. Desactívala en su lugar.',
            ], 422);
        }

        $record->delete();

        return response()->json(status: 204);
    }
}
