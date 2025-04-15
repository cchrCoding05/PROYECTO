import { useState, useEffect } from 'react';

const BASE_URL = "http://localhost:8000"; 

// Manejador de respuestas con gestión de errores
const handleResponse = async (response) => {
  // Verificar si la respuesta está vacía (como 204 No Content)
  if (response.status === 204) {
    return { success: true };
  }

  try {
    const data = await response.json();

    if (!response.ok) {
      const error = (data && data.message) || response.statusText;
      return Promise.reject(error);
    }

    return data;
  } catch (error) {
    console.error("Error parsing response:", error);
    if (!response.ok) {
      return Promise.reject(response.statusText);
    }
    return { success: true };
  }
};

// Función fetch mejorada con mejor gestión de errores
export const fetchApi = async (endpoint, options = {}) => {
  try {
    const url = `${BASE_URL}${endpoint}`;

    const defaultOptions = {
      headers: {
        "Content-Type": "application/json",
        "Accept": "application/json"
      },
      credentials: "include",
      mode: "cors"
    };

    // Si hay un token en localStorage, añadirlo al header de Authorization
    const token = localStorage.getItem('token');
    if (token) {
      defaultOptions.headers['Authorization'] = `Bearer ${token}`;
    }

    console.log(`Fetching ${options.method || "GET"} ${url}`);

    const response = await fetch(url, { ...defaultOptions, ...options });
    console.log(`Response status: ${response.status}`);

    return await handleResponse(response);
  } catch (error) {
    console.error("API request failed:", error);
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

// Servicios específicos para autenticación
export const authService = {
  login: (credentials) => {
    // Depuración: Veamos qué credenciales estamos enviando
    console.log('Enviando credenciales:', {
      username: credentials.username,
      password: credentials.password ? '********' : null
    });
    
    return fetchApi("/api/login_check", {
      method: "POST",
      body: JSON.stringify({
        email: credentials.username,
        password: credentials.password
      }),
    });
  },
  
  register: (userData) =>
    fetchApi("/api/register", {
      method: "POST",
      body: JSON.stringify(userData),
    }),
    
  logout: () => {
    console.log('Ejecutando cierre de sesión...');
    return fetchApi("/api/logout", { 
      method: "POST" 
    }).then(response => {
      console.log('Cierre de sesión completado con éxito:', response);
      return response;
    }).catch(error => {
      console.error('Error en la petición de cierre de sesión:', error);
      throw error; // Re-lanzamos el error para que sea manejado por useAuth
    });
  },
  
  getCurrentUser: () => {
    // Si hay un token en localStorage, intentamos obtener el usuario actual
    const token = localStorage.getItem('token');
    if (!token) {
      return Promise.resolve(null); // Si no hay token, no hay usuario autenticado
    }
    
    return fetchApi("/api/user/current").catch(error => {
      // Si hay un error (ej. 401), limpiamos el token y devolvemos null
      localStorage.removeItem('token');
      console.log("Sesión expirada o inválida");
      return null;
    });
  }
};

// Servicios para gestión de perfil de usuario
export const userService = {
  getProfile: () => fetchApi("/api/user/profile"),
  
  updateProfile: (profileData) => 
    fetchApi("/api/user/profile", {
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

// Servicios para gestión de profesionales
export const professionalService = {
  search: (query = "", filters = {}) => {
    const queryParams = new URLSearchParams();
    if (query) queryParams.append("q", query);
    
    Object.entries(filters).forEach(([key, value]) => {
      queryParams.append(key, value);
    });
    
    return fetchApi(`/api/professionals/search?${queryParams.toString()}`);
  },
  
  getById: (id) => fetchApi(`/api/professionals/${id}`),
  
  getRatings: (id) => fetchApi(`/api/professionals/${id}/ratings`),
};

// Servicios para gestión de objetos/productos
export const productService = {
  search: (query = "", filters = {}) => {
    const queryParams = new URLSearchParams();
    if (query) queryParams.append("q", query);
    
    Object.entries(filters).forEach(([key, value]) => {
      queryParams.append(key, value);
    });
    
    return fetchApi(`/api/products/search?${queryParams.toString()}`);
  },
  
  getById: (id) => fetchApi(`/api/products/${id}`),
  
  create: (productData) =>
    fetchApi("/api/products", {
      method: "POST",
      body: JSON.stringify(productData),
    }),
    
  update: (id, productData) =>
    fetchApi(`/api/products/${id}`, {
      method: "PUT",
      body: JSON.stringify(productData),
    }),
    
  delete: (id) =>
    fetchApi(`/api/products/${id}`, {
      method: "DELETE",
    }),
};

// Servicios para gestión de créditos
export const creditService = {
  getBalance: () => fetchApi("/api/credits/balance"),
  
  getHistory: () => fetchApi("/api/credits/history"),
  
  transferCredits: (transferData) =>
    fetchApi("/api/credits/transfer", {
      method: "POST",
      body: JSON.stringify(transferData),
    }),
    
  proposePrice: (productId, price) =>
    fetchApi(`/api/products/${productId}/propose-price`, {
      method: "POST",
      body: JSON.stringify({ price }),
    }),
};
