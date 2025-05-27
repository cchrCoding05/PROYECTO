export const apiConfig = {
  apiUrl: import.meta.env.VITE_API_URL || 'http://api.helpex.com:22193/api'
};

// Función fetch mejorada con mejor gestión de errores
export const fetchApi = async (endpoint, options = {}) => {
    const token = localStorage.getItem('token');
    const defaultHeaders = {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
    };

    if (token) {
        defaultHeaders['Authorization'] = `Bearer ${token}`;
    }

    const config = {
        ...options,
        headers: {
            ...defaultHeaders,
            ...options.headers
        }
    };

    try {
        const response = await fetch(`${apiConfig.apiUrl}${endpoint}`, config);
        const data = await response.json();

        if (!response.ok) {
            throw new Error(data.message || 'Error en la petición');
        }

        return data;
    } catch (error) {
        throw error;
    }
};