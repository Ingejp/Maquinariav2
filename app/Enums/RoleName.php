<?php

namespace App\Enums;

enum RoleName: string
{
    case SuperAdmin = 'SUPER ADMIN';
    case Administrador = 'ADMINISTRADOR';
    case Supervisor = 'SUPERVISOR';
}
