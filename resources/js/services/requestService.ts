import api from './api';

export interface RequestItem {
    item_id?: number | null;
    quantity: number;
    unit: string;
    item_description: string;
    request_detail_id?: number;
    released_quantity?: number;
}

export interface ReleaseItem {
    request_detail_id: number;
    quantity: number;
}

export interface CreateRequestPayload {
    request_date: string;
    purpose: string;
    status: string;
    department_id?: number;
    user_id: number;
    items: RequestItem[];
}

export interface SignatureData {
    image: string;
    signer_id: number;
    signer_name: string;
    signed_at: string;
}

export const requestService = {
    index: async (pageType: string = 'index'): Promise<any> => {
        const { data } = await api.get(`/api/requests?pageType=${pageType}`);
        return data;
    },

    create: async (payload: CreateRequestPayload): Promise<any> => {
        const { data } = await api.post('/api/requests', payload);
        return data;
    },

    updateItems: async (id: number, details: RequestItem[]): Promise<any> => {
        const { data } = await api.put(
            `/api/requests/${id}/items`,
            { details },
            {
                headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
            },
        );
        return data;
    },

    updatePurpose: async (id: number, purpose: string): Promise<any> => {
        const { data } = await api.put(
            `/api/requests/${id}/purpose`,
            { purpose },
            {
                headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
            },
        );
        return data;
    },

    release: async (id: number, payload: { items: ReleaseItem[]; notes: string; user_id?: number; signature?: SignatureData }): Promise<any> => {
        const { data } = await api.post(`/api/requests/${id}/release`, payload);
        return data;
    },

    updateStatus: async (id: number, payload: { status: string; password: string }): Promise<any> => {
        const { data } = await api.patch(`/api/requests/${id}/status`, payload);
        return data;
    },

    releaseData: async (id: number): Promise<any> => {
        const { data } = await api.get(`/api/requests/${id}/release-data`);
        return data;
    },

    showData: async (id: number): Promise<any> => {
        const { data } = await api.get(`/api/requests/${id}/show-data`);
        return data;
    },

    createData: async (): Promise<any> => {
        const { data } = await api.get('/api/requests/create-data');
        return data;
    },

    editData: async (id: number): Promise<any> => {
        const { data } = await api.get(`/api/requests/${id}/edit-data`);
        return data;
    },
};
