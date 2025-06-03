import { fetchApi } from './apiConfig';

// Servicios de administración
export const adminService = {
  deleteUser: async (userId) => {
    try {
      const response = await fetchApi(`/admin/users/${userId}`, {
        method: 'DELETE',
        headers: {
          'Authorization': `Bearer ${localStorage.getItem('token')}`
        }
      });

      if (!response.success) {
        throw new Error(response.message || 'Error al eliminar el usuario');
      }

      return response;
    } catch (error) {
      console.error('Error en deleteUser:', error);
      // Propagar el mensaje de error del backend
      throw new Error(error.message || 'Error al eliminar el usuario');
    }
  },

  getAllUsers: async () => {
    try {
      const response = await fetchApi('/admin/users', {
        headers: {
          'Authorization': `Bearer ${localStorage.getItem('token')}`
        }
      });

      if (!response.success) {
        throw new Error(response.message || 'Error al obtener los usuarios');
      }

      return response; // Retornamos la respuesta completa
    } catch (error) {
      console.error('Error en getAllUsers:', error);
      throw error;
    }
  },

  // Método para verificar si un usuario puede ser eliminado
  checkUserDeletion: async (userId) => {
    try {
      const response = await fetchApi(`/admin/users/${userId}/check-deletion`, {
        headers: {
          'Authorization': `Bearer ${localStorage.getItem('token')}`
        }
      });

      return response;
    } catch (error) {
      console.error('Error en checkUserDeletion:', error);
      throw error;
    }
  },

  getAllProducts: async () => {
    try {
      const response = await fetchApi('/admin/products', {
        headers: {
          'Authorization': `Bearer ${localStorage.getItem('token')}`
        }
      });

      if (!response.success) {
        throw new Error(response.message || 'Error al obtener los productos');
      }

      return response;
    } catch (error) {
      console.error('Error en getAllProducts:', error);
      throw error;
    }
  },

  deleteProduct: async (productId) => {
    try {
      const response = await fetchApi(`/admin/products/${productId}`, {
        method: 'DELETE',
        headers: {
          'Authorization': `Bearer ${localStorage.getItem('token')}`
        }
      });

      if (!response.success) {
        throw new Error(response.message || 'Error al eliminar el producto');
      }

      return response;
    } catch (error) {
      console.error('Error en deleteProduct:', error);
      throw error;
    }
  },

  updateProduct: async (productId, productData) => {
    try {
      const response = await fetchApi(`/admin/products/${productId}`, {
        method: 'PUT',
        headers: {
          'Authorization': `Bearer ${localStorage.getItem('token')}`,
          'Content-Type': 'application/json'
        },
        body: JSON.stringify(productData)
      });

      if (!response.success) {
        throw new Error(response.message || 'Error al actualizar el producto');
      }

      return response;
    } catch (error) {
      console.error('Error en updateProduct:', error);
      throw error;
    }
  },

  updateUser: async (userId, userData) => {
    try {
      const response = await fetchApi(`/admin/users/${userId}`, {
        method: 'PUT',
        headers: {
          'Authorization': `Bearer ${localStorage.getItem('token')}`,
          'Content-Type': 'application/json'
        },
        body: JSON.stringify(userData)
      });

      if (!response.success) {
        throw new Error(response.message || 'Error al actualizar el usuario');
      }

      return response;
    } catch (error) {
      console.error('Error en updateUser:', error);
      throw error;
    }
  },

  getSystemStats: async () => {
    try {
      const response = await fetchApi('/admin/stats', {
        headers: {
          'Authorization': `Bearer ${localStorage.getItem('token')}`
        }
      });

      if (!response.success) {
        throw new Error(response.message || 'Error al obtener las estadísticas del sistema');
      }

      return response;
    } catch (error) {
      console.error('Error en getSystemStats:', error);
      throw error;
    }
  },

  getRecentActivity: async (limit = 10) => {
    try {
      const response = await fetchApi(`/admin/activity?limit=${limit}`, {
        headers: {
          'Authorization': `Bearer ${localStorage.getItem('token')}`
        }
      });

      if (!response.success) {
        throw new Error(response.message || 'Error al obtener la actividad reciente');
      }

      return response;
    } catch (error) {
      console.error('Error en getRecentActivity:', error);
      throw error;
    }
  }
};
