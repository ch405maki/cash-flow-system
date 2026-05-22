import api from './api';

export const configurationService = {
    getAll: async <T>(endpoint: string): Promise<T[]> => {
        const { data } = await api.get<T[]>(`/api/configuration/${endpoint}`);
        return data;
    },

    create: async <T>(endpoint: string, payload: any): Promise<T> => {
        const { data } = await api.post<T>(`/api/configuration/${endpoint}`, payload);
        return data;
    },

    update: async <T>(endpoint: string, id: number, payload: any): Promise<T> => {
        const { data } = await api.put<T>(`/api/configuration/${endpoint}/${id}`, payload);
        return data;
    },

    delete: async (endpoint: string, id: number): Promise<void> => {
        await api.delete(`/api/configuration/${endpoint}/${id}`);
    },
};

export const configEndpoints = {
    departments: 'departments',
    accounts: 'accounts',
    signatories: 'signatories',
    access: 'access',
} as const;
