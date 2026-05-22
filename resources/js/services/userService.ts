import api from './api';

export interface CreateUserPayload {
  username: string;
  first_name: string;
  middle_name?: string;
  last_name: string;
  email: string;
  password: string;
  role: string;
  status: string;
  department_id?: number | null;
  access_id?: number | null;
  profile_picture_id?: number | null;
  is_petty_cash?: boolean;
  is_cash_advance?: boolean;
}

export interface UpdatePasswordPayload {
  password: string;
  password_confirmation: string;
}

export const userService = {
  create: async (payload: CreateUserPayload): Promise<any> => {
    const { data } = await api.post('/api/users', payload);
    return data;
  },

  update: async (id: number, payload: Partial<CreateUserPayload>): Promise<any> => {
    const { data } = await api.put(`/api/users/${id}`, payload);
    return data;
  },

  updatePassword: async (id: number, payload: UpdatePasswordPayload): Promise<any> => {
    const { data } = await api.put(`/api/users/${id}/password`, payload);
    return data;
  },

  toggleStatus: async (id: number, status: 'active' | 'inactive'): Promise<any> => {
    const { data } = await api.patch(`/api/users/${id}/status`, { status });
    return data;
  },

  delete: async (id: number): Promise<void> => {
    await api.delete(`/api/users/${id}`);
  },

  uploadBulk: async (file: File): Promise<any> => {
    const formData = new FormData();
    formData.append('file', file);
    const { data } = await api.post('/api/upload-users', formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    });
    return data;
  },
};
