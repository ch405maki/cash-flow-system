import api from './api';

export const profilePictureService = {
  delete: async (id: number): Promise<{ message: string }> => {
    const { data } = await api.delete(`/api/profile-pictures/${id}`);
    return data;
  },
};
