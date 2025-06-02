import { fetchApi } from './apiConfig';

// Servicios de productos
export const productService = {
  search: async (query) => {
    try {
      const response = await fetchApi(`/products/search?query=${encodeURIComponent(query)}`);
      return response;
    } catch (error) {
      throw error;
    }
  },
  
  getById: async (id) => {
    try {
      const response = await fetchApi(`/products/${id}`);
      return response;
    } catch (error) {
      throw error;
    }
  },
  
  create: async (productData) => {
    try {
      const response = await fetchApi('/products', {
        method: 'POST',
        body: JSON.stringify(productData)
      });
      return response;
    } catch (error) {
      throw error;
    }
  },
    
  update: async (id, productData) => {
    try {
      const response = await fetchApi(`/products/${id}`, {
        method: 'PUT',
        body: JSON.stringify(productData)
      });
      return response;
    } catch (error) {
      throw error;
    }
  },
    
  delete: async (id) => {
    try {
      const response = await fetchApi(`/products/${id}`, {
        method: 'DELETE'
      });
      if (!response.success) {
        throw new Error(response.message || 'Error al eliminar el producto');
      }
      return response;
    } catch (error) {
      console.error('Error al eliminar producto:', error);
      throw new Error(error.message || 'Error al eliminar el producto');
    }
  },

  // Nuevos endpoints para manejar estados
  updateState: async (id, state) => {
    return fetchApi(`/products/${id}/state`, {
      method: 'PUT',
      body: JSON.stringify({ state })
    });
  },

  getState: async (id) => {
    return fetchApi(`/products/${id}/state`);
  },

  // Endpoints de negociación con validaciones
  proposePrice: async (productId, data) => {
    // Eliminar comprobación de estado, llamar directamente
    return fetchApi(`/products/${productId}/propose-price`, {
      method: 'POST',
      body: JSON.stringify(data)
    });
  },

  getNegotiations: async (productId) => {
    return fetchApi(`/products/${productId}/negotiations`);
  },

  acceptOffer: async (productId, negotiationId) => {
    // Eliminar comprobación de estado, llamar directamente
    return fetchApi(`/products/${productId}/negotiations/${negotiationId}/accept`, {
      method: 'POST'
    });
  },

  rejectOffer: async (productId, negotiationId) => {
    return fetchApi(`/products/${productId}/negotiations/${negotiationId}/reject`, {
      method: 'POST'
    });
  },

  // Nueva función para obtener productos de usuarios mejor valorados
  getFromTopRatedUsers: async () => {
    try {
      const response = await fetchApi('/products/top-rated-users');
      return response;
    } catch (error) {
      throw error;
    }
  },

  getMyProducts: async () => {
    try {
      const response = await fetchApi('/products/my-products');
      return response;
    } catch (error) {
      throw error;
    }
  },

  updateProduct: async (productId, productData) => {
    try {
      const response = await fetchApi(`/products/${productId}`, {
        method: 'PUT',
        body: JSON.stringify(productData)
      });
      return response.data;
    } catch (error) {
      console.error('Error updating product:', error);
      return {
        success: false,
        message: error.response?.data?.message || 'Error al actualizar el producto'
      };
    }
  },
};