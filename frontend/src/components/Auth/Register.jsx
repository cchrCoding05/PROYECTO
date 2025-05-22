import React, { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { authService } from '../../services/authService';
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
    setAlert(null);
    
    // Validación de campos vacíos
    if (!formData.username.trim()) {
      setAlert({ 
        message: 'El nombre de usuario es obligatorio', 
        type: 'danger' 
      });
      return;
    }

    if (!formData.email.trim()) {
      setAlert({ 
        message: 'El correo electrónico es obligatorio', 
        type: 'danger' 
      });
      return;
    }

    if (!formData.password.trim()) {
      setAlert({ 
        message: 'La contraseña es obligatoria', 
        type: 'danger' 
      });
      return;
    }

    if (!formData.repeatPassword.trim()) {
      setAlert({ 
        message: 'Debes repetir la contraseña', 
        type: 'danger' 
      });
      return;
    }
    
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
      const response = await authService.register({
        username: formData.username.trim(),
        email: formData.email.trim(),
        password: formData.password
      });
      
      if (response.success) {
        setAlert({
          message: 'Usuario registrado con éxito. Redirigiendo al login...',
          type: 'success'
        });
        setTimeout(() => {
          navigate('/login');
        }, 2000);
      }
    } catch (error) {
      console.error('Error en el registro:', error);
      setAlert({ 
        message: error.message || 'Error al registrar usuario', 
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
              placeholder="Tu nombre de usuario"
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
              placeholder="tu@email.com"
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
              placeholder="Repite tu contraseña"
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