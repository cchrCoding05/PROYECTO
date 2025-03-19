const BASE_URL = "http://localhost:8000/api"; // URL de la API que será redirigida a api.php

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
    console.error("Error al procesar la respuesta:", error);
    if (!response.ok) {
      return Promise.reject(response.statusText);
    }
    return { success: true }; // Para respuestas exitosas no-JSON
  }
};

// Función fetch mejorada con mejor gestión de errores
export const fetchApi = async (endpoint, options = {}) => {
  try {
    const url = `${BASE_URL}${endpoint}`;

    // Opciones predeterminadas
    const defaultOptions = {
      headers: {
        "Content-Type": "application/json",
      },
      mode: "cors"  // Explícitamente usar modo CORS
    };

    // Si hay un token en localStorage, añadirlo al header de Authorization
    const token = localStorage.getItem('token');
    if (token) {
      defaultOptions.headers['Authorization'] = `Bearer ${token}`;
    }

    console.log(`Realizando petición ${options.method || "GET"} a ${url}`);

    const response = await fetch(url, { ...defaultOptions, ...options });
    console.log(`Estado de respuesta: ${response.status}`);

    return await handleResponse(response);
  } catch (error) {
    console.error("Falló la petición a la API:", error);
    throw error;
  }
};

// Servicios específicos para autenticación
export const authService = {
  login: (credentials) => 
    fetchApi("/login_check", {
      method: "POST",
      body: JSON.stringify(credentials),
    }),
  
  register: (userData) =>
    fetchApi("/register", {
      method: "POST",
      body: JSON.stringify(userData),
    }),
    
  logout: () => fetchApi("/logout", { method: "POST" }),
  
  getCurrentUser: () => fetchApi("/user/current")
};

// Servicios para gestión de perfil de usuario
export const userService = {
  getProfile: () => fetchApi("/user/profile"),
  
  updateProfile: (profileData) => 
    fetchApi("/user/profile", {
      method: "PUT",
      body: JSON.stringify(profileData),
    }),
    
  updateAvatar: (formData) => 
    fetchApi("/user/avatar", {
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
    
    return fetchApi(`/professionals/search?${queryParams.toString()}`);
  },
  
  getById: (id) => fetchApi(`/professionals/${id}`),
  
  getRatings: (id) => fetchApi(`/professionals/${id}/ratings`),
};

// Servicios para gestión de objetos/productos
export const productService = {
  search: (query = "", filters = {}) => {
    const queryParams = new URLSearchParams();
    if (query) queryParams.append("q", query);
    
    Object.entries(filters).forEach(([key, value]) => {
      queryParams.append(key, value);
    });
    
    return fetchApi(`/products/search?${queryParams.toString()}`);
  },
  
  getById: (id) => fetchApi(`/products/${id}`),
  
  create: (productData) =>
    fetchApi("/products", {
      method: "POST",
      body: JSON.stringify(productData),
    }),
    
  update: (id, productData) =>
    fetchApi(`/products/${id}`, {
      method: "PUT",
      body: JSON.stringify(productData),
    }),
    
  delete: (id) =>
    fetchApi(`/products/${id}`, {
      method: "DELETE",
    }),
};

// Servicios para gestión de créditos
export const creditService = {
  getBalance: () => fetchApi("/credits/balance"),
  
  getHistory: () => fetchApi("/credits/history"),
  
  transferCredits: (transferData) =>
    fetchApi("/credits/transfer", {
      method: "POST",
      body: JSON.stringify(transferData),
    }),
    
  proposePrice: (productId, price) =>
    fetchApi(`/products/${productId}/propose-price`, {
      method: "POST",
      body: JSON.stringify({ price }),
    }),
}; 