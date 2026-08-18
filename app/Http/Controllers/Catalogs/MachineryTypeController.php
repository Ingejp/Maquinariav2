<?php

namespace App\Http\Controllers\Catalogs;

use App\Models\Catalogs\MachineryType;
use Illuminate\Database\Eloquent\Model;

class MachineryTypeController extends CatalogController
{
    protected function modelClass(): string
    {
        return MachineryType::class;
    }

    protected function view(): string
    {
        return 'catalogs.machinery-type';
    }

    protected function dependentsCount(Model $record): int
    {
        /** @var MachineryType $record */
        return $record->machinery()->count();
    }

    protected function dependentsMessage(): string
    {
        return 'No se puede eliminar: hay máquinas de este tipo. Desactívalo en su lugar.';
    }
}
