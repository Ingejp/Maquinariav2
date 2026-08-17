import { createRouter, createWebHistory } from 'vue-router';

// Cada módulo (reportar, dashboard, configuración, seguridad) agrega sus
// propias rutas aquí conforme se construye en las fases siguientes.
// El router vive dentro de cada isla de Vue, no reemplaza la navegación
// Blade entre módulos (ver sección 1.3 del análisis técnico).
const routes = [];

export default function createAppRouter(base) {
    return createRouter({
        history: createWebHistory(base),
        routes,
    });
}
