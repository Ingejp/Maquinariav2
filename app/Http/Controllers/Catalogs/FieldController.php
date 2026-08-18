<?php

namespace App\Http\Controllers\Catalogs;

use App\Models\Catalogs\Field;
use Illuminate\Database\Eloquent\Model;

class FieldController extends CatalogController
{
    protected function modelClass(): string
    {
        return Field::class;
    }

    protected function view(): string
    {
        return 'catalogs.field';
    }

    protected function dependentsCount(Model $record): int
    {
        /** @var Field $record */
        return $record->machinery()->count();
    }

    protected function dependentsMessage(): string
    {
        return 'No se puede eliminar: hay máquinas asignadas a esta yarda. Desactívala en su lugar.';
    }
}
