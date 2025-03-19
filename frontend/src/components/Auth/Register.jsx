import React, { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { authService } from '../../services/api.jsx';
import AlertMessage from '../Layout/AlertMessage';
import './Auth.css';

const Register = () => {
  const [formData, setFormData] = useState({
    username: '',
    password: '',
    repeatPassword: '',
    profession: ''
  });
  const [alert, setAlert] = useState(null);
  const [professionOptions, setProfessionOptions] = useState([
    'Fontanero',
    'Electricista'
  ]);
  const [showProfessionDropdown, setShowProfessionDropdown] = useState(false);
  const [customProfession, setCustomProfession] = useState('');
  const navigate = useNavigate();

  const handleChange = (e) => {
    const { name, value } = e.target;
    setFormData({ ...formData, [name]: value });
  };

  const selectProfession = (profession) => {
    setFormData({ ...formData, profession });
    setShowProfessionDropdown(false);
  };

  const handleCustomProfession = (e) => {
    setCustomProfession(e.target.value);
  };

  const addCustomProfession = () => {
    if (customProfession.trim()) {
      setProfessionOptions([...professionOptions, customProfession]);
      selectProfession(customProfession);
      setCustomProfession('');
    }
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    
    // Validación básica
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
        password: formData.password,
        profession: formData.profession
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
            <label htmlFor="password">Contraseña</label>
            <input
              type="password"
              id="password"
              name="password"
              value={formData.password}
              onChange={handleChange}
              className="form-control"
              required
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
          
          <div className="form-layout">
            <div className="form-col">
              {/* Columna izquierda con datos básicos */}
            </div>
            <div className="form-col">
              <div className="form-group profession-select">
                <label>Profesión</label>
                <div className="dropdown-container">
                  <div 
                    className="selected-option"
                    onClick={() => setShowProfessionDropdown(!showProfessionDropdown)}
                  >
                    {formData.profession || 'AÑADIR'}
                  </div>
                  
                  {showProfessionDropdown && (
                    <div className="dropdown-options">
                      {professionOptions.map((option, index) => (
                        <div 
                          key={index} 
                          className="dropdown-option"
                          onClick={() => selectProfession(option)}
                        >
                          {option}
                        </div>
                      ))}
                      <div className="custom-option">
                        <input
                          type="text"
                          placeholder="Escribe aquí..."
                          value={customProfession}
                          onChange={handleCustomProfession}
                          onClick={(e) => e.stopPropagation()}
                        />
                      </div>
                    </div>
                  )}
                </div>
              </div>
            </div>
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