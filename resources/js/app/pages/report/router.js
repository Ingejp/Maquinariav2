import { createRouter, createWebHistory } from 'vue-router';
import YardSelect from './steps/YardSelect.vue';
import TypeSelect from './steps/TypeSelect.vue';
import MachineSelect from './steps/MachineSelect.vue';

const routes = [
    { path: '/', name: 'report.yard', component: YardSelect },
    { path: '/yarda/:fieldId/tipo', name: 'report.type', component: TypeSelect, props: true },
    { path: '/yarda/:fieldId/tipo/:typeId/maquina', name: 'report.machine', component: MachineSelect, props: true },
    { path: '/:pathMatch(.*)*', redirect: { name: 'report.yard' } },
];

export default createRouter({
    history: createWebHistory('/reportar'),
    routes,
});
