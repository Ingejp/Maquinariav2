import { createRouter, createWebHistory } from 'vue-router';
import YardSelect from './steps/YardSelect.vue';
import TypeSelect from './steps/TypeSelect.vue';
import MachineSelect from './steps/MachineSelect.vue';
import RegisterForm from './steps/RegisterForm.vue';

// Flujo mobile-first de 4 pasos, con router propio de esta isla (no hay
// SPA global — ver resources/js/app.js). Cada paso deriva su URL de las
// selecciones anteriores para que refrescar la página a la mitad no rompa.
const routes = [
    { path: '/', name: 'report.yard', component: YardSelect },
    { path: '/yarda/:fieldId/tipo', name: 'report.type', component: TypeSelect, props: true },
    { path: '/yarda/:fieldId/tipo/:typeId/maquina', name: 'report.machine', component: MachineSelect, props: true },
    {
        path: '/yarda/:fieldId/tipo/:typeId/maquina/:machineId/registrar',
        name: 'report.register',
        component: RegisterForm,
        props: true,
    },
    // Cualquier sub-ruta que no matchee (link viejo, typo) vuelve al paso 1
    // en vez de dejar la isla en blanco.
    { path: '/:pathMatch(.*)*', redirect: { name: 'report.yard' } },
];

export default createRouter({
    history: createWebHistory('/reportar'),
    routes,
});
