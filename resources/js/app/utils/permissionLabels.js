// Los permisos viven en la BD como slugs técnicos (coinciden con los nombres
// usados en las rutas del backend, ver routes/web.php), pero un admin no
// tiene por qué memorizarlos. Este mapa los traduce a algo legible en la
// pantalla de Roles (y como referencia en la de Permisos). Un permiso nuevo
// que no esté acá simplemente muestra su nombre técnico tal cual — no rompe
// nada, solo pierde la traducción amigable.
export const PERMISSION_LABELS = {
    'catalogs.view': {
        label: 'Ver catálogos',
        description: 'Ver yardas, estados, tipos de maquinaria y máquinas.',
    },
    'catalogs.manage': {
        label: 'Gestionar catálogos',
        description: 'Crear, editar, activar/desactivar y eliminar yardas, estados, tipos y máquinas.',
    },
    'report.create': {
        label: 'Reportar estado de máquinas',
        description: 'Usar el flujo de Reportar (Yarda → Tipo → Máquina → Registrar).',
    },
    'dashboard.view': {
        label: 'Ver Dashboard',
        description: 'Ver los KPIs, la gráfica y el detalle de reportes.',
    },
    'security.manage': {
        label: 'Gestionar Seguridad',
        description: 'Administrar roles, permisos y usuarios del sistema.',
    },
};

export function permissionLabel(name) {
    return PERMISSION_LABELS[name] ?? { label: name, description: '' };
}
