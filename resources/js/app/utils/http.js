import axios from 'axios';

const http = axios.create({
    baseURL: '/',
    headers: {
        'X-Requested-With': 'XMLHttpRequest',
    },
});

const csrfToken = document.querySelector('meta[name="csrf-token"]');
if (csrfToken) {
    http.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken.content;
}

export default http;
