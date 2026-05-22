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

export const purchaseOrderService = {
  create: async (payload: CreatePOPayload | FormData, config?: any): Promise<{ id: number }> => {
    const { data } = await api.post('/api/purchase-orders', payload, config);
    return data;
  },
};
