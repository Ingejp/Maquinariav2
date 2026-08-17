import { createApp } from 'vue';

// Patrón "Blade + islas de Vue 3": cada vista Blade cascarón declara
// data-page="<carpeta>" en el div #app, y aquí se resuelve dinámicamente
// el componente raíz correspondiente en resources/js/app/pages/<carpeta>/index.vue.
const pages = import.meta.glob('./app/pages/**/index.vue');

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

    createApp(PageComponent, props).mount(el);
}

mountPage();
