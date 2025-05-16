import React, { useState } from 'react';
import { useNavigate, useLocation } from 'react-router-dom';
import { useAuth } from '../../hooks/useAuth';
import AlertMessage from '../Layout/AlertMessage';
import './Auth.css';
import { Link } from 'react-router-dom';

const Login = () => {
  const [credentials, setCredentials] = useState({
    email: '',
    password: ''
  });
  const [error, setError] = useState('');
  const [isLoading, setIsLoading] = useState(false);
  const { login } = useAuth();
  const navigate = useNavigate();
  const location = useLocation();
  
  const handleChange = (e) => {
    const { name, value } = e.target;
    setCredentials(prev => ({
      ...prev,
      [name]: value
    }));
  };
  
  const handleSubmit = async (e) => {
    e.preventDefault();
    setIsLoading(true);
    setError('');

    // Validación de campos vacíos
    if (!credentials.email.trim()) {
      setError('El correo electrónico es obligatorio');
      setIsLoading(false);
      return;
    }

    if (!credentials.password.trim()) {
      setError('La contraseña es obligatoria');
      setIsLoading(false);
      return;
    }

    try {
      const result = await login({
        email: credentials.email.trim(),
        password: credentials.password
      });
      
      if (result.success) {
        // Redirigir a la página que intentaba visitar o a la página principal
        const from = location.state?.from?.pathname || '/';
        navigate(from, { replace: true });
      } else {
        setError(result.message || 'Error al iniciar sesión');
      }
    } catch (error) {
      console.error('Error durante el inicio de sesión:', error);
      setError('Error de conexión al servidor');
    } finally {
      setIsLoading(false);
    }
  };
  
  return (
    <div className="auth-container">
      <div className="auth-card">
        <h2>Iniciar sesión</h2>
        
        {error && <AlertMessage message={error} type="danger" onClose={() => setError('')} />}
        
        <form onSubmit={handleSubmit}>
          <div className="form-group">
            <label htmlFor="email">Correo electrónico</label>
            <input
              type="email"
              id="email"
              name="email"
              value={credentials.email}
              onChange={handleChange}
              className="form-control"
              placeholder="tu@email.com"
            />
          </div>
          
          <div className="form-group">
            <label htmlFor="password">Contraseña</label>
            <input
              type="password"
              id="password"
              name="password"
              value={credentials.password}
              onChange={handleChange}
              className="form-control"
              placeholder="Tu contraseña"
            />
          </div>
          
          <button 
            type="submit" 
            className="btn-primary" 
            disabled={isLoading}
          >
            {isLoading ? 'Cargando...' : 'Iniciar Sesión'}
          </button>
        </form>
        
        <div className="auth-footer">
          <p>¿No tienes cuenta? <Link to="/registro">Regístrate</Link></p>
        </div>
      </div>
    </div>
  );
};

export default Login;