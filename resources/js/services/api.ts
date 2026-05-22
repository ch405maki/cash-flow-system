import axios from 'axios';
import { useToast } from 'vue-toastification';

const api = axios.create({
    headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json',
    },
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
        return Promise.reject(error);
    }
);

export default api;
