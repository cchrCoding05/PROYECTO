import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { useAuth } from '../../hooks/useAuth';
import AlertMessage from '../Layout/AlertMessage';
import './Auth.css';

const Login = () => {
  const [credentials, setCredentials] = useState({
    username: '',
    password: ''
  });
  const [error, setError] = useState('');
  const [isLoading, setIsLoading] = useState(false);
  const { login } = useAuth();
  const navigate = useNavigate();
  
  const handleChange = (e) => {
    const { name, value } = e.target;
    setCredentials(prev => ({
      ...prev,
      [name]: value
    }));
  };
  
  const handleSubmit = async (e) => {
    e.preventDefault();
    setError('');
    setIsLoading(true);
    
    try {
      if (!credentials.username || !credentials.password) {
        setError('Por favor, introduce tu email y contraseña');
        setIsLoading(false);
        return;
      }
      
      const response = await login(credentials);
      
      if (response.success) {
        console.log('Inicio de sesión exitoso, redirigiendo...');
        navigate('/buscar/profesionales');
      } else {
        setError(response.message || 'Error al iniciar sesión. Verifica tus credenciales.');
      }
    } catch (err) {
      console.error('Error durante el inicio de sesión:', err);
      setError('Error de conexión al servidor.');
    } finally {
      setIsLoading(false);
    }
  };
  
  return (
    <div className="auth-container">
      <div className="auth-card">
        <h2>Inicio sesión</h2>
        
        {error && <AlertMessage message={error} type="danger" onClose={() => setError('')} />}
        
        <form onSubmit={handleSubmit}>
          <div className="form-group">
            <label htmlFor="username">Email</label>
            <input
              type="email"
              id="username"
              name="username"
              value={credentials.username}
              onChange={handleChange}
              className="form-control"
              required
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
              required
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
          <p>Si aún no tienes cuenta, <a href="/registro">regístrate</a></p>
        </div>
      </div>
    </div>
  );
};

export default Login;