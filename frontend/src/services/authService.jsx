import { fetchApi } from './apiConfig';

// Servicios de autenticación
export const authService = {
  async login(credentials) {
    try {
      console.log('Iniciando proceso de login con credenciales:', credentials);
      
      if (!credentials || !credentials.email || !credentials.password) {
        throw new Error('Email y contraseña son requeridos');
      }

      const response = await fetchApi('/login', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          email: credentials.email.trim(),
          password: credentials.password
        })
      });

      console.log('Respuesta del servidor:', response);

      if (!response.success) {
        throw new Error(response.message || 'Error al iniciar sesión');
      }

      if (response.token) {
        localStorage.setItem('token', response.token);
      }

      return response;
    } catch (error) {
      console.error('Error en login:', error);
      throw error;
    }
  },
  
  async register(userData) {
    try {
      const response = await fetchApi('/register', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(userData)
      });

      if (!response.success) {
        throw new Error(response.message || 'Error al registrar usuario');
      }

      return response;
    } catch (error) {
      console.error('Error en registro:', error);
      throw error;
    }
  },
  
  async logout() {
    try {
      // Primero eliminamos el token localmente
      localStorage.removeItem('token');
      
      // Intentamos hacer logout en el servidor
      try {
        const response = await fetchApi('/logout', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
          }
        });
        
        console.log('Respuesta del servidor en logout:', response);
        return response;
      } catch (error) {
        console.warn('Error al hacer logout en el servidor:', error);
        // Si falla el logout en el servidor, no es crítico
        // ya que ya hemos eliminado el token localmente
        return { success: true };
      }
    } catch (error) {
      console.error('Error en logout:', error);
      // Asegurarnos de que el token se elimine incluso si hay error
      localStorage.removeItem('token');
      return { success: true };
    }
  },
  
  async getCurrentUser() {
    try {
      const response = await fetchApi('/users/profile');
      return response;
    } catch (error) {
      console.error('Error al obtener usuario actual:', error);
      throw error;
    }
  },

  getProfile: async () => {
    try {
      return await fetchApi('/users/profile');
    } catch (error) {
      console.error('Error al obtener perfil:', error);
      throw error;
    }
  },

  updateProfile: async (profileData) => {
    try {
      return await fetchApi('/users/profile', {
        method: 'PUT',
        body: JSON.stringify(profileData)
      });
    } catch (error) {
      console.error('Error al actualizar perfil:', error);
      throw error;
    }
  },

  get isAuthenticated() {
    return !!localStorage.getItem('token');
  }
};