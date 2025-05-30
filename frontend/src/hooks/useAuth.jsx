import { createContext, useContext, useState, useEffect } from 'react';
import { authService } from '../services/authService';

const AuthContext = createContext();

export const AuthProvider = ({ children, onNavigate }) => {
  const [currentUser, setCurrentUser] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  const checkAuth = async () => {
    try {
      console.log('Verificando autenticación...');
      const token = localStorage.getItem('token');
      
      if (!token) {
        console.log('No hay token, usuario no autenticado');
        setCurrentUser(null);
        return;
      }

      console.log('Token encontrado, obteniendo datos del usuario...');
      const user = await authService.getCurrentUser();
      console.log('Datos del usuario obtenidos:', user);
      
      if (user) {
        setCurrentUser(user);
      } else {
        console.log('No se pudo obtener datos del usuario, limpiando sesión');
        localStorage.removeItem('token');
        setCurrentUser(null);
      }
    } catch (error) {
      console.error('Error al verificar autenticación:', error);
      localStorage.removeItem('token');
      setCurrentUser(null);
      setError(error.message);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    checkAuth();
    
    // Verificar autenticación cada 5 minutos
    const interval = setInterval(checkAuth, 300000);
    return () => clearInterval(interval);
  }, []);

 const login = async (credentials) => {
    try {
        setLoading(true);
        setError(null);
        console.log('Iniciando proceso de login...');
        
        const response = await authService.login(credentials);
        console.log('Respuesta del login:', response);
        
        if (response.token) {
            localStorage.setItem('token', response.token);
            // Establecer el usuario actual con la estructura correcta
            setCurrentUser({
                data: response.user,
                token: response.token
            });
        }
        
        if (onNavigate) onNavigate('/dashboard');
        return response;
    } catch (error) {
        console.error('Error en login:', error);
        setError(error.message);
        throw error;
    } finally {
        setLoading(false);
    }
};

  const logout = async () => {
    try {
      setLoading(true);
      console.log('Iniciando proceso de logout...');
      
      await authService.logout();
      localStorage.removeItem('token');
      setCurrentUser(null);
      
      if (onNavigate) onNavigate('/login');
    } catch (error) {
      console.error('Error en logout:', error);
      setError(error.message);
      // Asegurarnos de que el usuario se desloguee incluso si hay error
      localStorage.removeItem('token');
      setCurrentUser(null);
    } finally {
      setLoading(false);
    }
  };

  const register = async (userData) => {
    try {
      setLoading(true);
      setError(null);
      const response = await authService.register(userData);
      if (onNavigate) onNavigate('/login');
      return response;
    } catch (error) {
      setError(error.message);
      throw error;
    } finally {
      setLoading(false);
    }
  };

  const value = {
    currentUser,
    loading,
    error,
    login,
    logout,
    register,
    isAuthenticated: !!currentUser,
    user: currentUser?.data || null
  };

  return (
    <AuthContext.Provider value={value}>
      {!loading && children}
    </AuthContext.Provider>
  );
};

export const useAuth = () => {
  const context = useContext(AuthContext);
  if (!context) {
    throw new Error('useAuth debe ser usado dentro de un AuthProvider');
  }
  return context;
};
