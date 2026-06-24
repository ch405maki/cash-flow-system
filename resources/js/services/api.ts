import axios from 'axios';
import { useToast } from 'vue-toastification';

const api = axios.create({
    withCredentials: true,
    headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json',
    },
});

api.interceptors.request.use((config) => {
    const token = localStorage.getItem('auth_token');
    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
});

api.interceptors.response.use(
    (response) => response,
    (error) => {
        if (error.response?.status === 422) {
            const toast = useToast();
            const errors = error.response.data.errors;
            if (errors) {
                Object.values(errors).forEach((messages: any) => {
                    if (Array.isArray(messages)) {
                        messages.forEach((msg: string) => toast.error(msg));
                    }
                });
            }
        }
        if (error.response?.status === 401) {
            const token = localStorage.getItem('auth_token');
            if (token) {
                localStorage.removeItem('auth_token');
                window.location.href = '/login';
            }
        }
        return Promise.reject(error);
    }
);

export default api;
