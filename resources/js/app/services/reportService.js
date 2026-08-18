import http from '../utils/http';

const base = '/reportar/datos';

export default {
    fields() {
        return http.get(`${base}/yardas`).then((r) => r.data);
    },
    machineryTypes(fieldId) {
        return http.get(`${base}/yardas/${fieldId}/tipos`).then((r) => r.data);
    },
    machines(fieldId, typeId) {
        return http.get(`${base}/yardas/${fieldId}/tipos/${typeId}/maquinas`).then((r) => r.data);
    },
    statuses() {
        return http.get(`${base}/estados`).then((r) => r.data);
    },
    submit(payload) {
        return http.post(`${base}/registrar`, payload);
    },
};
