import { ref } from 'vue';
import { useToast } from 'vue-toastification';
import api from '@/services/api';

export function useCrud<T extends { id: number }>(endpoint: string) {
    const items = ref<T[]>([]);
    const toast = useToast();
    const loading = ref(false);

    async function fetchItems(): Promise<void> {
        loading.value = true;
        try {
            const { data } = await api.get<T[]>(endpoint);
            items.value = data;
        } catch {
            toast.error('Failed to load data');
        } finally {
            loading.value = false;
        }
    }

    async function createItem(payload: any, onSuccess?: (item: T) => void): Promise<T | null> {
        try {
            const { data } = await api.post<T>(endpoint, payload);
            items.value.push(data);
            toast.success('Created successfully');
            onSuccess?.(data);
            return data;
        } catch (error: any) {
            if (!error.response?.status || error.response.status !== 422) {
                toast.error('Failed to create');
            }
            return null;
        }
    }

    async function updateItem(id: number, payload: any, onSuccess?: (item: T) => void): Promise<T | null> {
        try {
            const { data } = await api.put<T>(`${endpoint}/${id}`, payload);
            const index = items.value.findIndex((item) => item.id === id);
            if (index !== -1) items.value[index] = data;
            toast.success('Updated successfully');
            onSuccess?.(data);
            return data;
        } catch (error: any) {
            if (!error.response?.status || error.response.status !== 422) {
                toast.error('Failed to update');
            }
            return null;
        }
    }

    async function deleteItem(id: number, onSuccess?: () => void): Promise<boolean> {
        try {
            await api.delete(`${endpoint}/${id}`);
            items.value = items.value.filter((item) => item.id !== id);
            toast.success('Deleted successfully');
            onSuccess?.();
            return true;
        } catch {
            toast.error('Failed to delete');
            return false;
        }
    }

    return {
        items,
        loading,
        fetchItems,
        createItem,
        updateItem,
        deleteItem,
    };
}
