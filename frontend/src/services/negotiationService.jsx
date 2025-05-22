import { fetchApi } from './apiConfig';

// Servicios de negociación
export const negotiationService = {
  getMyNegotiations: async () => {
    try {
      const response = await fetchApi('/negotiations/my-negotiations');
      if (!response.success) {
        throw new Error(response.message || 'Error al obtener las negociaciones');
      }
      return response.data;
    } catch (error) {
      console.error('Error en getMyNegotiations:', error);
      throw error;
    }
  }
};