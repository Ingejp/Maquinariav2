<?php

namespace App\Http\Controllers\Catalogs;

use App\Http\Controllers\Controller;
use App\Http\Requests\Catalogs\CatalogRequest;
use App\Http\Resources\Catalogs\CatalogResource;
use App\Models\Catalogs\ConfigStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\View\View;

/**
 * Base compartida por los catálogos "simples" (Field, Status, MachineryType):
 * misma forma (description + configuration_status_id), mismo comportamiento
 * de listar/crear/editar/activar-desactivar/eliminar. Machinery tiene sus
 * propias relaciones (tipo, yarda) y no extiende de esta clase.
 */
abstract class CatalogController extends Controller
{
    /** @return class-string<Model> */
    abstract protected function modelClass(): string;

    abstract protected function view(): string;

    /**
     * Cantidad de registros dependientes que bloquearían un delete.
     * 0 = seguro para eliminar.
     */
    abstract protected function dependentsCount(Model $record): int;

    abstract protected function dependentsMessage(): string;

    public function index(Request $request): View|AnonymousResourceCollection
    {
        $modelClass = $this->modelClass();

        if ($request->wantsJson()) {
            $query = $modelClass::query()->with('configStatus');

            if ($search = trim((string) $request->string('search'))) {
                $query->where('description', 'like', "%{$search}%");
            }

            return CatalogResource::collection($query->orderBy('description')->get());
        }

        return view($this->view());
    }

    public function store(CatalogRequest $request): JsonResponse
    {
        $modelClass = $this->modelClass();

        $record = $modelClass::create([
            'description' => $request->string('description')->value(),
            'configuration_status_id' => ConfigStatus::ACTIVE,
        ]);

        return (new CatalogResource($record->load('configStatus')))->response()->setStatusCode(201);
    }

    public function update(CatalogRequest $request, int $id): CatalogResource
    {
        $record = $this->modelClass()::findOrFail($id);
        $record->update(['description' => $request->string('description')->value()]);

        return new CatalogResource($record->load('configStatus'));
    }

    public function toggleStatus(int $id): CatalogResource
    {
        $record = $this->modelClass()::findOrFail($id);

        $record->update([
            'configuration_status_id' => $record->isActive() ? ConfigStatus::DISABLED : ConfigStatus::ACTIVE,
        ]);

        return new CatalogResource($record->load('configStatus'));
    }

    public function destroy(int $id): JsonResponse
    {
        $record = $this->modelClass()::findOrFail($id);

        $dependents = $this->dependentsCount($record);

        if ($dependents > 0) {
            return response()->json([
                'message' => $this->dependentsMessage(),
            ], 422);
        }

        $record->delete();

        return response()->json(status: 204);
    }
}
