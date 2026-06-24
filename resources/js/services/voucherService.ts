import api from './api';

export interface VoucherPayload {
  po_id?: number;
  voucher_no: string;
  tin_no?: string;
  issue_date?: string;
  payment_date?: string;
  delivery_date?: string;
  voucher_date: string;
  purpose: string;
  payee: string;
  check_no?: string | null;
  check_payable_to: string;
  check_amount: number;
  status: string;
  type: string;
  user_id: number;
  check: Array<{
    id?: number | null;
    amount: number;
    charging_tag: string | null;
    hours: number | null;
    rate: number | null;
    account_id: string;
  }>;
}

export interface VoucherResponse {
  id: number;
  voucher_no: string;
  message?: string;
  [key: string]: any;
}

export const voucherService = {
  checkNumber: async (voucherNo: string): Promise<{ available: boolean }> => {
    const params = new URLSearchParams({ voucher_no: voucherNo });
    const { data } = await api.get<{ available: boolean }>(`/api/vouchers/check-number?${params}`);
    return data;
  },

  generateNumber: async (): Promise<{ success: boolean; voucher_number: string }> => {
    const { data } = await api.get('/api/vouchers/generate-number');
    return data;
  },

  create: async (payload: VoucherPayload): Promise<VoucherResponse> => {
    const { data } = await api.post<VoucherResponse>('/api/vouchers', payload);
    return data;
  },

  update: async (id: number, payload: any): Promise<VoucherResponse> => {
    const { data } = await api.put<VoucherResponse>(`/api/vouchers/${id}`, payload);
    return data;
  },

  uploadReceipt: async (id: number, formData: FormData): Promise<{ message: string; data: any }> => {
    const { data } = await api.post(`/api/vouchers/${id}/receipt`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    });
    return data;
  },

  addCheck: async (id: number, payload: { check_no: string; check_date: string; user_id: number }): Promise<{ data: any }> => {
    const { data } = await api.patch(`/api/vouchers/${id}/check`, payload);
    return data;
  },
};
