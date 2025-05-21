import { useState, useEffect } from 'react';
import axios from 'axios';

const API_URL = 'http://localhost:8000/api';

// Función fetch mejorada con mejor gestión de errores
const fetchApi = async (endpoint, options = {}) => {
  console.log('Iniciando fetchApi para endpoint:', endpoint);
  const token = localStorage.getItem('token');
  
  // Lista de endpoints públicos que no requieren token
  const publicEndpoints = [
    '/login',
    '/register',
    '/products/search',
    '/professionals/search',
    '/users/top-rated',
    '/products/top-rated-users',
    '/home'
  ];

  // Verificar si el endpoint es público
  const isPublicEndpoint = publicEndpoints.some(publicEndpoint => endpoint.startsWith(publicEndpoint));

  const headers = {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    'Origin': window.location.origin,
    ...(token && !isPublicEndpoint && { 'Authorization': `Bearer ${token}` }),
    ...options.headers,
  };
  console.log('Headers configurados:', headers);

  try {
    console.log('Realizando petición a:', `${API_URL}${endpoint}`);
    const response = await fetch(`${API_URL}${endpoint}`, {
      ...options,
      headers,
      mode: 'cors',
      cache: 'no-cache',
    });
    console.log('Respuesta recibida:', response.status, response.statusText);

    if (response.status === 204) {
      return { success: true };
    }

    const data = await response.json();
    console.log('Datos recibidos:', data);

    if (!response.ok) {
      console.error('Error en la respuesta:', {
        status: response.status,
        statusText: response.statusText,
        data: data
      });
      if (response.status === 401) {
        localStorage.removeItem('token');
        const error = new Error(data.message || 'Sesión expirada o inválida');
        error.status = 401;
        return Promise.reject(error);
      }
      const error = new Error(data.message || response.statusText);
      error.status = response.status;
      return Promise.reject(error);
    }

    return data;
  } catch (error) {
    console.error('Error en fetchApi:', {
      message: error.message,
      stack: error.stack,
      endpoint: endpoint
    });
    if (error.message.includes('Failed to fetch')) {
      throw new Error('No se pudo conectar con el servidor. Por favor, verifica tu conexión.');
    }
    throw error;
  }
};

// Hook personalizado para peticiones a la API
export const useApiCall = (endpoint, options = {}, dependencies = []) => {
  const [data, setData] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    let isMounted = true;
    
    const fetchData = async () => {
      try {
        setLoading(true);
        const result = await fetchApi(endpoint, options);
        if (isMounted) {
          setData(result);
          setError(null);
        }
      } catch (err) {
        if (isMounted) {
          setError(err);
        }
      } finally {
        if (isMounted) {
          setLoading(false);
        }
      }
    };

    fetchData();

    return () => {
      isMounted = false;
    };
  }, [...dependencies]);

  return { data, loading, error };
};

// Servicios de autenticación
export const authService = {
  login: async (credentials) => {
    const response = await fetchApi('/login', {
      method: 'POST',
      body: JSON.stringify(credentials)
    });
    if (response.token) {
      localStorage.setItem('token', response.token);
    }
    return response;
  },
  
  register: async (userData) => {
    return fetchApi('/register', {
      method: 'POST',
      body: JSON.stringify(userData)
    });
  },
  
  logout: async () => {
    try {
      // Primero eliminamos el token
      localStorage.removeItem('token');
      
      // Luego intentamos hacer logout en el servidor
      await fetchApi('/logout', {
        method: 'POST'
      });
    } catch (error) {
      console.error('Error en logout:', error);
      // Asegurarnos de que el token se elimine incluso si hay error
      localStorage.removeItem('token');
    }
  },
  
  getCurrentUser: async () => {
    return fetchApi('/user/profile');
  },

  getProfile: async () => {
    return fetchApi('/user/profile');
  },

  updateProfile: async (profileData) => {
    return fetchApi('/user/profile', {
      method: 'PUT',
      body: JSON.stringify(profileData)
    });
  },

  get isAuthenticated() {
    return !!localStorage.getItem('token');
  }
};

// Servicios de usuario
export const userService = {
  getProfile: () => fetchApi("/user/profile"),
  
  updateProfile: async (profileData) => {
    try {
      const response = await fetchApi("/user/profile", {
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

// Servicios de profesionales
export const professionalService = {
  search: async (query = '') => {
    try {
      const response = await fetchApi(`/professionals/search?query=${encodeURIComponent(query)}`);
      console.log('Respuesta del backend:', response);
      
      // Si la respuesta es un array, procesarlo
      if (Array.isArray(response)) {
        const processedData = response.map(professional => ({
          ...professional,
          foto_perfil: professional.profilePhoto || professional.photo || null
        }));
        console.log('Datos procesados (array):', processedData);
        return {
          success: true,
          data: processedData
        };
      }
      
      // Si la respuesta tiene la estructura { success, data }
      if (response.success && Array.isArray(response.data)) {
        const processedData = response.data.map(professional => ({
          ...professional,
          foto_perfil: professional.profilePhoto || professional.photo || null
        }));
        console.log('Datos procesados (success):', processedData);
        return {
          success: true,
          data: processedData
        };
      }
      
      // Si no es ninguno de los casos anteriores, devolver la respuesta tal cual
      return response;
    } catch (error) {
      console.error('Error en professionalService.search:', error);
      throw error;
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

// Servicios de créditos
export const creditService = {
  getBalance: async () => {
    return fetchApi('/credits/balance');
  },
  
  getHistory: async () => {
    return fetchApi('/credits/history');
  },
  
  transfer: async (data) => {
    return fetchApi('/credits/transfer', {
      method: 'POST',
      body: JSON.stringify(data)
    });
  },
};

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
