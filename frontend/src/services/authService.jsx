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
        if (response.message === 'Credenciales inválidas') {
          throw new Error('Correo o contraseña incorrectos');
        }
        throw new Error(response.message || 'Error al iniciar sesión');
      }

      if (!response.token) {
        throw new Error('No se recibió el token de autenticación');
      }

      console.log('Token recibido, guardando en localStorage...');
      localStorage.setItem('token', response.token);
      
      // Esperar un momento para asegurar que el token se guardó
      await new Promise(resolve => setTimeout(resolve, 100));
      
      // Verificar que el token se guardó correctamente
      const savedToken = localStorage.getItem('token');
      console.log('Token guardado:', savedToken ? 'Sí' : 'No');
      console.log('Valor del token guardado:', savedToken);
      
      if (!savedToken) {
        throw new Error('Error al guardar el token');
      }
      
      // Intentar obtener el perfil del usuario
      console.log('Obteniendo perfil del usuario...');
      try {
        const userProfile = await this.getCurrentUser();
        console.log('Perfil obtenido:', userProfile);
        
        // Verificar que tenemos los datos del usuario
        if (!userProfile || !userProfile.data) {
          throw new Error('No se pudo obtener el perfil del usuario');
        }
        
        return {
          ...response,
          user: userProfile.data
        };
      } catch (profileError) {
        console.error('Error al obtener perfil:', profileError);
        // Si falla al obtener el perfil, al menos devolvemos los datos básicos
        return response;
      }
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
      console.log('Obteniendo datos del usuario actual...');
      const response = await fetchApi('/users/profile');
      console.log('Respuesta del perfil:', response);
      
      if (!response.success) {
        throw new Error(response.message || 'Error al obtener perfil');
      }

      // Asegurarnos de que tenemos todos los campos necesarios
      const userData = {
        ...response.data,
        id: response.data.id,
        username: response.data.username,
        email: response.data.email,
        credits: response.data.credits || 0,
        profession: response.data.profession || '',
        rating: response.data.rating || 0,
        sales: response.data.sales || 0,
        profilePhoto: response.data.profilePhoto || null,
        description: response.data.description || '',
        negotiations: response.data.negotiations || []
      };

      return {
        ...response,
        data: userData
      };
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
