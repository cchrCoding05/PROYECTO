import React, { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { authService } from '../../services/api.jsx';
import AlertMessage from '../Layout/AlertMessage';
import './Auth.css';

const Register = () => {
  const [formData, setFormData] = useState({
    username: '',
    email: '',
    password: '',
    repeatPassword: ''
  });
  const [alert, setAlert] = useState(null);
  const navigate = useNavigate();

  const handleChange = (e) => {
    const { name, value } = e.target;
    setFormData({ ...formData, [name]: value });
  };

  const validatePassword = (password) => {
    const hasUpperCase = /[A-Z]/.test(password);
    const hasNumber = /[0-9]/.test(password);
    return hasUpperCase && hasNumber;
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    
    // Validación de contraseña
    if (!validatePassword(formData.password)) {
      setAlert({ 
        message: 'La contraseña debe contener al menos una mayúscula y un número', 
        type: 'danger' 
      });
      return;
    }

    if (formData.password !== formData.repeatPassword) {
      setAlert({ 
        message: 'Las contraseñas no coinciden', 
        type: 'danger' 
      });
      return;
    }

    try {
      const userData = {
        username: formData.username,
        email: formData.email,
        password: formData.password
      };
      
      const result = await authService.register(userData);
      
      if (result.success) {
        navigate('/login');
      } else {
        setAlert({ 
          message: result.message || 'Error en el registro', 
          type: 'danger' 
        });
      }
    } catch (error) {
      setAlert({ 
        message: 'Error de conexión al servidor', 
        type: 'danger' 
      });
    }
  };

  return (
    <div className="auth-container">
      <div className="auth-card">
        <h2 className="text-center">Registrarse</h2>
        
        {alert && (
          <AlertMessage 
            message={alert.message} 
            type={alert.type} 
            onClose={() => setAlert(null)} 
          />
        )}
        
        <form onSubmit={handleSubmit}>
          <div className="form-group">
            <label htmlFor="username">Usuario</label>
            <input
              type="text"
              id="username"
              name="username"
              value={formData.username}
              onChange={handleChange}
              className="form-control"
              required
            />
          </div>

          <div className="form-group">
            <label htmlFor="email">Correo electrónico</label>
            <input
              type="email"
              id="email"
              name="email"
              value={formData.email}
              onChange={handleChange}
              className="form-control"
              required
            />
          </div>
          
          <div className="form-group">
            <label htmlFor="password">Contraseña</label>
            <input
              type="password"
              id="password"
              name="password"
              value={formData.password}
              onChange={handleChange}
              className="form-control"
              required
              placeholder="Debe contener una mayúscula y un número"
            />
          </div>
          
          <div className="form-group">
            <label htmlFor="repeatPassword">Repetir Contraseña</label>
            <input
              type="password"
              id="repeatPassword"
              name="repeatPassword"
              value={formData.repeatPassword}
              onChange={handleChange}
              className="form-control"
              required
            />
          </div>
          
          <button 
            type="submit" 
            className="btn-primary"
          >
            Registrarse
          </button>
        </form>
      </div>
    </div>
  );
};

export default Register;