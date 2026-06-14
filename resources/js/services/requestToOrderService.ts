import api from './api';

export const requestToOrderService = {
    index: async (pageType: string = 'index'): Promise<any> => {
        const { data } = await api.get(`/api/request-to-order?pageType=${pageType}`);
        return data;
    },

    createData: async (): Promise<any> => {
        const { data } = await api.get('/api/request-to-order/create-data');
        return data;
    },

    listData: async (): Promise<any> => {
        const { data } = await api.get('/api/request-to-order/list-data');
        return data;
    },

    showData: async (id: number): Promise<any> => {
        const { data } = await api.get(`/api/request-to-order/${id}`);
        return data;
    },

    store: async (payload: any): Promise<any> => {
        const { data } = await api.post('/api/request-to-order/store', payload);
        return data;
    },

    storeManual: async (payload: any): Promise<any> => {
        const { data } = await api.post('/api/request-to-order/store-manual', payload);
        return data;
    },

    approve: async (id: number, password: string): Promise<any> => {
        const { data } = await api.patch(`/api/request-to-order/${id}/approve`, { password });
        return data;
    },

    forEod: async (id: number, password: string): Promise<any> => {
        const { data } = await api.patch(`/api/request-to-order/${id}/for-eod`, { password });
        return data;
    },

    reject: async (id: number, password: string): Promise<any> => {
        const { data } = await api.patch(`/api/request-to-order/${id}/reject`, { password });
        return data;
    },

    releaseData: async (id: number): Promise<any> => {
        const { data } = await api.get(`/api/request-to-order/${id}/release-data`);
        return data;
    },

    release: async (id: number, payload: any): Promise<any> => {
        const { data } = await api.post(`/api/request-to-order/${id}/release`, payload);
        return data;
    },
};
