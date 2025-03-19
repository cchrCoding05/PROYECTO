import { useState, useEffect, createContext, useContext } from "react";
import { authService } from "../services/api.jsx";

const AuthContext = createContext();

export const AuthProvider = ({ children }) => {
  const [currentUser, setCurrentUser] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    const checkLoggedIn = async () => {
      try {
        setLoading(true);
        const user = await authService.getCurrentUser();
        setCurrentUser(user);
      } catch (error) {
        console.error("Error al verificar sesión:", error);
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
      
      const response = await authService.login(credentials);
      
      if (response.token) {
        // Si el backend devuelve un token, podemos almacenarlo en localStorage
        localStorage.setItem("token", response.token);
      }
      
      // Obtenemos los datos del usuario actual
      const userData = await authService.getCurrentUser();
      setCurrentUser(userData);
      
      return { success: true };
    } catch (error) {
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
      
      await authService.logout();
      localStorage.removeItem("token");
      setCurrentUser(null);
      
      return { success: true };
    } catch (error) {
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
