import api from './api';

export const canvasService = {
  upload: async (url: string, formData: FormData, config?: any): Promise<any> => {
    const { data } = await api.post(url, formData, config);
    return data;
  },

  update: async (url: string, formData: FormData, config?: any): Promise<any> => {
    formData.append('_method', 'put');
    const { data } = await api.post(url, formData, config);
    return data;
  },
};
