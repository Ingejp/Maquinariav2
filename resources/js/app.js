import { createApp } from 'vue';

// Patrón "Blade + islas de Vue 3": cada vista Blade cascarón declara
// data-page="<carpeta>" en el div #app, y aquí se resuelve dinámicamente
// el componente raíz correspondiente en resources/js/app/pages/<carpeta>/index.vue.
const pages = import.meta.glob('./app/pages/**/index.vue');

// Si una isla necesita navegación interna (ej. Reportar: Yarda → Tipo →
// Máquina → Registrar), declara un router.js hermano exportando su propia
// instancia de Vue Router — no hay router global, cada isla es dueña de la suya.
const routers = import.meta.glob('./app/pages/**/router.js');

async function mountPage() {
    const el = document.getElementById('app');
    if (!el || !el.dataset.page) {
        return;
    }

    const page = el.dataset.page;
    const loader = pages[`./app/pages/${page}/index.vue`];

    if (!loader) {
        console.error(`[app.js] No se encontró la página Vue para "${page}".`);
        return;
    }

    const { default: PageComponent } = await loader();
    const props = el.dataset.props ? JSON.parse(el.dataset.props) : {};

    const app = createApp(PageComponent, props);

    const routerLoader = routers[`./app/pages/${page}/router.js`];
    if (routerLoader) {
        const { default: router } = await routerLoader();
        app.use(router);
    }

    app.mount(el);
}

mountPage();
