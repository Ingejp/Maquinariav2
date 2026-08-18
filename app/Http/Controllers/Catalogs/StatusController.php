<?php

namespace App\Http\Controllers\Catalogs;

use App\Models\Catalogs\Status;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class StatusController extends CatalogController
{
    protected function modelClass(): string
    {
        return Status::class;
    }

    protected function view(): string
    {
        return 'catalogs.status';
    }

    protected function dependentsCount(Model $record): int
    {
        // report_status.status_id no tiene modelo propio todavía (Fase 3) —
        // se consulta la tabla directamente para no crear una dependencia
        // circular prematura.
        return DB::table('report_status')->where('status_id', $record->id)->count();
    }

    protected function dependentsMessage(): string
    {
        return 'No se puede eliminar: hay reportes registrados con este estado. Desactívalo en su lugar.';
    }
}
