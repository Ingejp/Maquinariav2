<?php

namespace App\Http\Controllers\Catalogs;

use App\Models\Catalogs\Status;
use Illuminate\Database\Eloquent\Model;

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
        /** @var Status $record */
        return $record->reportStatuses()->count();
    }

    protected function dependentsMessage(): string
    {
        return 'No se puede eliminar: hay reportes registrados con este estado. Desactívalo en su lugar.';
    }
}
