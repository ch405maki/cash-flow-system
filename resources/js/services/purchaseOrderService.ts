import api from './api';

export interface PODetail {
  quantity: number;
  unit: string;
  item_description: string;
  unit_price: number;
  amount: number;
}

export interface CreatePOPayload {
  payee: string;
  check_payable_to: string;
  date: string;
  purpose: string;
  tin_no?: string;
  status: string;
  user_id: number;
  department_id?: number;
  tagging: string;
  amount: number;
  details: PODetail[];
  file?: File | null;
}

export interface UpdateStatusPayload {
  status: string;
  password: string;
  remarks?: string;
}

export const purchaseOrderService = {
  indexData: async (params?: { status?: string; page?: number }): Promise<any> => {
    const { data } = await api.get('/api/purchase-order/index-data', { params });
    return data;
  },

  showData: async (id: number): Promise<any> => {
    const { data } = await api.get(`/api/purchase-order/${id}/show-data`);
    return data;
  },

  createData: async (): Promise<any> => {
    const { data } = await api.get('/api/purchase-order/create-data');
    return data;
  },

  create: async (payload: CreatePOPayload | FormData, config?: any): Promise<{ id: number }> => {
    const { data } = await api.post('/api/purchase-order', payload, config);
    return data;
  },

  updateStatus: async (id: number, payload: UpdateStatusPayload): Promise<any> => {
    const { data } = await api.patch(`/api/purchase-order/${id}/status`, payload);
    return data;
  },
};
