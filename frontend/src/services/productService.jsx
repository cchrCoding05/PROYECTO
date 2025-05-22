import { fetchApi } from './apiConfig';

// Servicios de productos
export const productService = {
  search: async (query) => {
    try {
      console.log('Iniciando búsqueda con query:', query);
      const response = await fetchApi(`/products/search?query=${encodeURIComponent(query)}`);
      console.log('Respuesta de búsqueda:', response);
      
      if (!response.success) {
        throw new Error(response.message || 'Error en la búsqueda');
      }
      
      return response.data;
    } catch (error) {
      console.error('Error en la búsqueda:', error);
      throw error;
    }
  },
  
  get: async (id) => {
    console.log('Obteniendo producto con ID:', id);
    const response = await fetchApi(`/products/${id}`);
    console.log('Producto obtenido:', response);
    return response.data || response;
  },
  
  create: async (productData) => {
    console.log('Creando producto con datos:', productData);
    try {
      // Validar que todos los campos obligatorios estén presentes
      if (!productData.name || !productData.description || !productData.price) {
        throw new Error('Faltan campos obligatorios');
      }

      const response = await fetchApi('/products', {
        method: 'POST',
        body: JSON.stringify({
          titulo: productData.name,
          descripcion: productData.description,
          creditos: parseInt(productData.price),
          estado: 1, // Estado por defecto: disponible (1)
          id_usuario: 1, // TODO: Obtener el ID del usuario actual
          imagen: productData.image // URL de la imagen de Cloudinary
        })
      });
      console.log('Respuesta de creación:', response);
      return response;
    } catch (error) {
      console.error('Error al crear producto:', error);
      if (error.response) {
        console.error('Detalles del error:', error.response);
      }
      throw error;
    }
  },
    
  update: async (id, productData) => {
    return fetchApi(`/products/${id}`, {
      method: 'PUT',
      body: JSON.stringify(productData)
    });
  },
    
  delete: async (id) => {
    return fetchApi(`/products/${id}`, {
      method: 'DELETE'
    });
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
    console.log('Iniciando getFromTopRatedUsers');
    try {
      const response = await fetchApi('/products/top-rated-users');
      console.log('Respuesta de getFromTopRatedUsers:', response);
      if (!response.success) {
        console.error('Error en la respuesta:', response);
        throw new Error(response.message || 'Error al obtener productos de usuarios mejor valorados');
      }
      return response;
    } catch (error) {
      console.error('Error detallado en getFromTopRatedUsers:', {
        message: error.message,
        stack: error.stack,
        response: error.response
      });
      throw error;
    }
  },

  getMyProducts: async () => {
    console.log('Iniciando getMyProducts en productService');
    try {
      const response = await fetchApi('/products/my-products');
      console.log('Respuesta raw de getMyProducts:', response);
      
      if (!response.success) {
        console.error('Error en getMyProducts:', response);
        throw new Error(response.message || 'Error al obtener mis productos');
      }
      
      console.log('Datos de productos obtenidos:', response.data);
      return response;
    } catch (error) {
      console.error('Error en getMyProducts:', error);
      console.error('Stack trace:', error.stack);
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