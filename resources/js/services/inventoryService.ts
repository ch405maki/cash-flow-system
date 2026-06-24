import api from './api';

export const inventoryService = {
  getItems: async (): Promise<{ data: any[] }> => {
    const { data } = await api.get('/api/inventory/items');
    return data;
  },

  getProducts: async (): Promise<{ success: boolean; data: any[] }> => {
    const { data } = await api.get('/api/inventory/products');
    return data;
  },
};
