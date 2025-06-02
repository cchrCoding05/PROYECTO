// Configuración de la API
const API_URL = process.env.NODE_ENV === 'production' 
    ? 'http://api.helpex.com:22193/api'
    : 'http://localhost:8000/api';

// Función para realizar peticiones a la API
export const fetchApi = async (endpoint, options = {}) => {
    try {
        console.log('Realizando petición a:', `${API_URL}${endpoint}`);
        
        // Obtener el token del localStorage
        const token = localStorage.getItem('token');
        console.log('Token encontrado:', token ? 'Sí' : 'No');

        // Configurar las cabeceras por defecto
        const defaultHeaders = {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        };

        // Si hay token, agregarlo a las cabeceras con el formato correcto
        if (token) {
            defaultHeaders['Authorization'] = `Bearer ${token}`;
            console.log('Cabecera de autorización:', defaultHeaders['Authorization']);
        }

        // Combinar las cabeceras por defecto con las proporcionadas
        const headers = {
            ...defaultHeaders,
            ...options.headers
        };

        // Configuración de la petición
        const config = {
            ...options,
            headers,
            credentials: 'include' // Incluir cookies en la petición
        };

        console.log('Configuración completa:', {
            url: `${API_URL}${endpoint}`,
            method: config.method || 'GET',
            headers: config.headers
        });

        // Realizar la petición
        const response = await fetch(`${API_URL}${endpoint}`, config);
        
        // Si la respuesta no es exitosa, lanzar error
        if (!response.ok) {
            const errorData = await response.json().catch(() => ({}));
            console.error('Error en la respuesta:', errorData);
            throw new Error(errorData.message || 'Error en la petición');
        }

        // Procesar la respuesta
        const data = await response.json();
        return data;
    } catch (error) {
        console.error('Error en fetchApi:', error);
        throw error;
    }
};
