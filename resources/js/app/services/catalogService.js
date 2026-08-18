import http from '../utils/http';

export default function createCatalogService(basePath) {
    return {
        list(params = {}) {
            return http.get(basePath, { params }).then((r) => r.data.data);
        },
        create(payload) {
            return http.post(basePath, payload).then((r) => r.data.data);
        },
        update(id, payload) {
            return http.put(`${basePath}/${id}`, payload).then((r) => r.data.data);
        },
        toggle(id) {
            return http.patch(`${basePath}/${id}/estado`).then((r) => r.data.data);
        },
        remove(id) {
            return http.delete(`${basePath}/${id}`);
        },
    };
}
