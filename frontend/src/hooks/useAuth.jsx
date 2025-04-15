import { useState, useEffect, createContext, useContext } from "react";
import { authService } from "../services/api.jsx";

const AuthContext = createContext();

export const AuthProvider = ({ children }) => {
  const [currentUser, setCurrentUser] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  // Verificar si hay una sesión activa al cargar la aplicación
  useEffect(() => {
    const checkLoggedIn = async () => {
      try {
        setLoading(true);
        // Comprobar si hay un token en localStorage
        const token = localStorage.getItem('token');
        if (!token) {
          setCurrentUser(null);
          return;
        }
        
        // Si hay token, intentar obtener los datos del usuario
        const user = await authService.getCurrentUser();
        setCurrentUser(user);
      } catch (error) {
        console.error("Error al verificar sesión:", error);
        localStorage.removeItem('token'); // Eliminar el token si hay errores
        setCurrentUser(null);
      } finally {
        setLoading(false);
      }
    };

    checkLoggedIn();
  }, []);

  const login = async (credentials) => {
    try {
      setLoading(true);
      setError(null);
      
      console.log('Intentando iniciar sesión con:', {
        username: credentials.username,
        password: credentials.password ? '********' : null
      });
      
      const response = await authService.login(credentials);
      console.log('Respuesta del servidor:', response);
      
      // Si la respuesta tiene un success=false explícito, manejar como error
      if (response && response.success === false && response.message) {
        throw new Error(response.message);
      }
      
      if (response && response.token) {
        // Si el backend devuelve un token, podemos almacenarlo en localStorage
        localStorage.setItem("token", response.token);
        console.log('Token guardado en localStorage');
        
        // Guardamos los datos del usuario si vienen en la respuesta
        if (response.user) {
          console.log('Datos de usuario recibidos:', response.user);
          setCurrentUser(response.user);
          return { success: true };
        }
        
        // Si no vienen los datos del usuario, los obtenemos
        console.log('Obteniendo datos de usuario...');
        const userData = await authService.getCurrentUser();
        console.log('Datos de usuario obtenidos:', userData);
        setCurrentUser(userData);
        return { success: true };
      } else {
        console.error('No se recibió un token en la respuesta:', response);
        throw new Error("No se recibió un token de autenticación");
      }
    } catch (error) {
      console.error('Error durante el inicio de sesión:', error);
      setError(error.toString());
      return { 
        success: false, 
        message: error.toString() || "Error al iniciar sesión" 
      };
    } finally {
      setLoading(false);
    }
  };

  const register = async (userData) => {
    try {
      setLoading(true);
      setError(null);
      
      const response = await authService.register(userData);
      return { success: true };
    } catch (error) {
      setError(error.toString());
      return { 
        success: false, 
        message: error.toString() || "Error al registrarse" 
      };
    } finally {
      setLoading(false);
    }
  };

  const logout = async () => {
    try {
      setLoading(true);
      setError(null);
      
      console.log('Intentando cerrar sesión...');
      const response = await authService.logout();
      console.log('Respuesta del servidor al cerrar sesión:', response);
      
      localStorage.removeItem("token");
      setCurrentUser(null);
      
      return { success: true };
    } catch (error) {
      console.error('Error durante el cierre de sesión:', error);
      setError(error.toString());
      return { 
        success: false, 
        message: error.toString() || "Error al cerrar sesión" 
      };
    } finally {
      setLoading(false);
    }
  };

  return (
    <AuthContext.Provider 
      value={{ 
        currentUser, 
        loading, 
        error, 
        login, 
        register, 
        logout,
        isAuthenticated: !!currentUser
      }}
    >
      {children}
    </AuthContext.Provider>
  );
};

export const useAuth = () => {
  const context = useContext(AuthContext);
  if (!context) {
    throw new Error("useAuth debe ser usado dentro de un AuthProvider");
  }
  return context;
};

export default useAuth;
