import { onMounted, ref } from 'vue';
import createCatalogService from '../services/catalogService';

/**
 * Estado y acciones compartidas por las páginas de catálogo simple
 * (Field/Status/MachineryType): listar, crear, editar, activar/desactivar,
 * eliminar (con confirmación). Machinery usa su propia página por las
 * relaciones adicionales (tipo, yarda), pero reutiliza esta misma forma.
 */
export function useCatalogPage(basePath) {
    const service = createCatalogService(basePath);

    const items = ref([]);
    const loading = ref(true);
    const saving = ref(false);
    const errors = ref({});
    const editing = ref(null);
    const confirmingDelete = ref(null);
    const banner = ref(null);
    // Cambia en cada guardado exitoso; se usa como :key del form para forzar
    // que se remonte y limpie su estado interno (ver CatalogFormPanel.vue).
    const formKey = ref(0);

    async function fetchItems(params = {}) {
        loading.value = true;
        try {
            items.value = await service.list(params);
        } catch {
            banner.value = { type: 'error', message: 'No se pudo cargar el listado.' };
        } finally {
            loading.value = false;
        }
    }

    function startCreate() {
        editing.value = null;
        errors.value = {};
    }

    function startEdit(item) {
        editing.value = item;
        errors.value = {};
    }

    function cancelEdit() {
        editing.value = null;
        errors.value = {};
    }

    async function save(payload) {
        saving.value = true;
        errors.value = {};
        try {
            if (editing.value) {
                const updated = await service.update(editing.value.id, payload);
                const idx = items.value.findIndex((i) => i.id === updated.id);
                if (idx !== -1) items.value.splice(idx, 1, updated);
            } else {
                const created = await service.create(payload);
                items.value.push(created);
                items.value.sort((a, b) => a.description.localeCompare(b.description));
            }
            editing.value = null;
            formKey.value++;
            banner.value = { type: 'success', message: 'Guardado correctamente.' };
        } catch (error) {
            if (error.response?.status === 422 && error.response.data.errors) {
                errors.value = error.response.data.errors;
            } else {
                banner.value = { type: 'error', message: 'Ocurrió un error al guardar.' };
            }
        } finally {
            saving.value = false;
        }
    }

    async function toggleStatus(item) {
        try {
            const updated = await service.toggle(item.id);
            const idx = items.value.findIndex((i) => i.id === updated.id);
            if (idx !== -1) items.value.splice(idx, 1, updated);
        } catch {
            banner.value = { type: 'error', message: 'No se pudo cambiar el estado.' };
        }
    }

    function askDelete(item) {
        confirmingDelete.value = item;
    }

    function cancelDelete() {
        confirmingDelete.value = null;
    }

    async function confirmDelete() {
        if (!confirmingDelete.value) return;
        const id = confirmingDelete.value.id;
        try {
            await service.remove(id);
            items.value = items.value.filter((i) => i.id !== id);
        } catch (error) {
            banner.value = {
                type: 'error',
                message: error.response?.data?.message || 'No se pudo eliminar.',
            };
        } finally {
            confirmingDelete.value = null;
        }
    }

    onMounted(() => fetchItems());

    return {
        items,
        loading,
        saving,
        errors,
        editing,
        confirmingDelete,
        banner,
        formKey,
        fetchItems,
        startCreate,
        startEdit,
        cancelEdit,
        save,
        toggleStatus,
        askDelete,
        cancelDelete,
        confirmDelete,
    };
}
