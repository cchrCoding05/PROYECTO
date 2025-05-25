import { fetchApi } from './apiConfig';

// Servicios de profesionales
export const professionalService = {
  search: async (query = '') => {
    try {
      // Usar el endpoint correcto para buscar profesionales
      const response = await fetchApi(`/professionals/search${query ? `?query=${encodeURIComponent(query)}` : ''}`);
      console.log('Respuesta del backend:', response);
      
      if (!response.success) {
        throw new Error(response.message || 'Error al obtener profesionales');
      }

      // Procesar los datos
      const processedData = (response.data || []).map(professional => ({
        ...professional,
        foto_perfil: professional.profilePhoto || professional.photo || null,
        rating: parseFloat(professional.rating) || 0,
        reviews_count: parseInt(professional.reviews_count) || 0
      }));

      return {
        success: true,
        data: processedData
      };
    } catch (error) {
      console.error('Error en professionalService.search:', error);
      return {
        success: false,
        message: error.message || 'Error al buscar profesionales',
        data: []
      };
    }
  },
  
  get: async (id) => {
    const response = await fetchApi(`/professionals/${id}`);
    return {
      ...response,
      foto_perfil: response.profilePhoto || response.photo || null
    };
  },
  
  getRatings: async (id) => {
    return fetchApi(`/professionals/${id}/ratings`);
  },

  // Nueva función para obtener usuarios mejor valorados
  getTopRated: async () => {
    try {
      const response = await fetchApi('/users/top-rated');
      if (!response.success) {
        throw new Error(response.message || 'Error al obtener usuarios mejor valorados');
      }
      return response;
    } catch (error) {
      console.error('Error en getTopRated:', error);
      throw error;
    }
  }
};