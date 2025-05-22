const API_URL = 'http://localhost:8000/api';

// Función fetch mejorada con mejor gestión de errores
export const fetchApi = async (endpoint, options = {}) => {
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

  try {
    console.log('Realizando petición a:', `${API_URL}${endpoint}`);
    console.log('Opciones de la petición:', {
      method: options.method || 'GET',
      headers,
      body: options.body
    });

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