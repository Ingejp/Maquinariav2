<?php

namespace App\Models\Catalogs;

use Illuminate\Database\Eloquent\Model;

/**
 * Catálogo fijo de 2 valores (Activo/Deshabilitado) compartido por Field,
 * Status, MachineryType y Machinery. No tiene CRUD propio — se mantiene tal
 * cual del esquema legacy (ver docs/analisis-tecnico-TFPB-Maquinaria.md, 4.4).
 */
class ConfigStatus extends Model
{
    protected $table = 'config_status';

    public const ACTIVE = 1;

    public const DISABLED = 2;
}
