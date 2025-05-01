import { useState, useEffect } from 'react';

const API_URL = 'http://localhost:8000/api';

// Función fetch mejorada con mejor gestión de errores
const fetchApi = async (endpoint, options = {}) => {
  const token = localStorage.getItem('token');
  const headers = {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    'Origin': window.location.origin,
    ...(token && { 'Authorization': `Bearer ${token}` }),
    ...options.headers,
  };

  try {
    const response = await fetch(`${API_URL}${endpoint}`, {
      ...options,
      headers,
      mode: 'cors',
      cache: 'no-cache',
    });

    if (response.status === 204) {
      return { success: true };
    }

    const data = await response.json();

    if (!response.ok) {
      if (response.status === 401) {
        // Si recibimos un 401, limpiamos el token
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
    console.error('Error en la petición:', error);
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
    await fetchApi('/api/logout', {
      method: 'POST'
    });
    localStorage.removeItem('token');
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
  
  updateProfile: (profileData) => 
    fetchApi("/user/profile", {
      method: "PUT",
      body: JSON.stringify(profileData),
    }),
    
  updateAvatar: (formData) => 
    fetchApi("/api/user/avatar", {
      method: "POST",
      headers: {}, // Vacío para que fetch configure automáticamente content-type para FormData
      body: formData,
    }),
};

// Servicios de profesionales
export const professionalService = {
  search: async (query = '') => {
    return fetchApi(`/professionals/search?query=${encodeURIComponent(query)}`);
  },
  
  get: async (id) => {
    return fetchApi(`/professionals/${id}`);
  },
  
  getRatings: async (id) => {
    return fetchApi(`/professionals/${id}/ratings`);
  },
};

// Servicios de productos
export const productService = {
  search: async (query = '') => {
    console.log('Llamando a la API de búsqueda de productos...');
    const response = await fetchApi(`/products/search?query=${encodeURIComponent(query)}`);
    console.log('Respuesta de la API de productos:', response);
    
    // Acceder a response.data si existe, o usar response directamente
    const results = response.data || response;
    console.log('Datos de productos:', results);
    
    // Asegurarnos de que cada producto tenga un estado
    const processedResults = Array.isArray(results) ? results.map(product => ({
      ...product,
      state: product.state || 1
    })) : [];
    console.log('Productos procesados:', processedResults);
    return processedResults;
  },
  
  get: async (id) => {
    console.log('Obteniendo producto con ID:', id);
    const response = await fetchApi(`/products/${id}`);
    console.log('Producto obtenido:', response);
    return response.data || response;
  },
  
  create: async (productData) => {
    return fetchApi('/products', {
      method: 'POST',
      body: JSON.stringify(productData)
    });
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
    // Primero verificamos el estado del producto
    const productState = await fetchApi(`/products/${productId}/state`);
    if (productState.state !== 1) { // 1 = Disponible
      throw new Error('No se puede proponer precio para un producto no disponible');
    }
    return fetchApi(`/products/${productId}/propose-price`, {
      method: 'POST',
      body: JSON.stringify(data)
    });
  },

  getNegotiations: async (productId) => {
    return fetchApi(`/products/${productId}/negotiations`);
  },

  acceptOffer: async (productId, negotiationId) => {
    // Verificamos el estado antes de aceptar
    const productState = await fetchApi(`/products/${productId}/state`);
    if (productState.state !== 1) { // 1 = Disponible
      throw new Error('No se puede aceptar una oferta para un producto no disponible');
    }
    return fetchApi(`/products/${productId}/negotiations/${negotiationId}/accept`, {
      method: 'POST'
    });
  },

  rejectOffer: async (productId, negotiationId) => {
    return fetchApi(`/products/${productId}/negotiations/${negotiationId}/reject`, {
      method: 'POST'
    });
  }
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
