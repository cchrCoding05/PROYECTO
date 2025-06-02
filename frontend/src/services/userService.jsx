import { fetchApi } from './apiConfig';

// Servicios de usuario
export const userService = {
  getProfile: () => fetchApi("/users/profile"),
  
  updateProfile: async (profileData) => {
    try {
      const response = await fetchApi("/users/profile", {
        method: "PUT",
        body: JSON.stringify(profileData),
      });
      
      if (!response.success) {
        throw new Error(response.message || 'Error al actualizar el perfil');
      }
      
      return response;
    } catch (error) {
      console.error('Error en updateProfile:', error);
      throw error;
    }
  },
};