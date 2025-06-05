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
    const hasMinLength = password.length >= 8;
    return hasUpperCase && hasNumber && hasMinLength;
  };

  const validateEmail = (email) => {
    // Validación más estricta del formato de email
    const emailRegex = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/;
    if (!emailRegex.test(email)) {
      return {
        isValid: false,
        message: 'El formato del correo electrónico no es válido'
      };
    }
    
    // Validar que no tenga espacios
    if (email.includes(' ')) {
      return {
        isValid: false,
        message: 'El correo electrónico no debe contener espacios'
      };
    }
    
    // Validar que tenga un dominio válido
    const parts = email.split('@');
    if (parts.length !== 2 || !parts[1].includes('.')) {
      return {
        isValid: false,
        message: 'El correo debe tener un dominio válido (ejemplo: usuario@dominio.com)'
      };
    }
    
    return { isValid: true };
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

    // Validación de correo personalizada
    const emailValidation = validateEmail(formData.email);
    if (!emailValidation.isValid) {
      setAlert({ 
        message: emailValidation.message, 
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
    
    // Validación de contraseña personalizada
    if (!validatePassword(formData.password)) {
      setAlert({ 
        message: 'La contraseña debe tener al menos 8 caracteres, una mayúscula y un número', 
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
        return;
      }

      // Mensajes personalizados para usuario/correo ya registrados
      if (response.message) {
        if (response.message.toLowerCase().includes('usuario') && response.message.toLowerCase().includes('existe')) {
          setAlert({
            message: 'Este usuario ya está registrado',
            type: 'danger'
          });
          return;
        }
        if (response.message.toLowerCase().includes('correo') && response.message.toLowerCase().includes('existe')) {
          setAlert({
            message: 'Este correo ya está registrado',
            type: 'danger'
          });
          return;
        }
      }

      // Mensaje genérico si no es ninguno de los anteriores
      setAlert({
        message: response.message || 'Error al registrar usuario',
        type: 'danger'
      });
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
          <div className="alert-container">
            <AlertMessage 
              message={alert.message} 
              type={alert.type} 
              onClose={() => setAlert(null)} 
            />
          </div>
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
              type="text"
              id="email"
              name="email"
              value={formData.email}
              onChange={handleChange}
              className="form-control"
              placeholder="Tu correo electrónico"
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