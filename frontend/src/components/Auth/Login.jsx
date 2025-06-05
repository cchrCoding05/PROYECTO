import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { Form, Button, Container, Card } from 'react-bootstrap';
import { useAuth } from '../../hooks/useAuth';
import './Auth.css';

const Login = () => {
  const navigate = useNavigate();
  const { login } = useAuth();
  const [formData, setFormData] = useState({
    email: '',
    password: ''
  });
  const [errors, setErrors] = useState({
    email: '',
    password: '',
    general: ''
  });
  const [loading, setLoading] = useState(false);

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

  const handleChange = (e) => {
    const { name, value } = e.target;
    setFormData(prev => ({
      ...prev,
      [name]: value.trim()
    }));
    
    // Validar email en tiempo real
    if (name === 'email' && value.trim()) {
      const emailValidation = validateEmail(value.trim());
      if (!emailValidation.isValid) {
        setErrors(prev => ({ ...prev, email: emailValidation.message }));
      } else {
        setErrors(prev => ({ ...prev, email: '', general: '' }));
      }
    } else {
      setErrors(prev => ({ ...prev, [name]: '', general: '' }));
    }
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    e.stopPropagation();
    
    // Limpiar errores previos
    setErrors({ email: '', password: '', general: '' });
    setLoading(true);

    // Validaciones
    let hasErrors = false;
    const newErrors = { email: '', password: '', general: '' };

    if (!formData.email) {
      newErrors.email = 'El correo electrónico es requerido';
      hasErrors = true;
    } else {
      const emailValidation = validateEmail(formData.email);
      if (!emailValidation.isValid) {
        newErrors.email = emailValidation.message;
        hasErrors = true;
      }
    }

    if (!formData.password) {
      newErrors.password = 'La contraseña es requerida';
      hasErrors = true;
    }

    if (hasErrors) {
      setErrors(newErrors);
      setLoading(false);
      return;
    }

    try {
      const response = await login(formData);
      if (response && response.token) {
        navigate('/');
      }
    } catch (err) {
      console.error('Error en login:', err);
      const errorMessage = err.message || 'Error al iniciar sesión';
      alert(errorMessage);
      setErrors({
        email: '',
        password: '',
        general: errorMessage
      });
      setLoading(false);
    }
  };

  return (
    <Container className="d-flex justify-content-center align-items-center min-vh-100">
      <Card className="p-4 shadow-sm" style={{ maxWidth: '400px', width: '100%' }}>
        <Card.Body>
          <h2 className="text-center mb-4">Iniciar Sesión</h2>

          <Form onSubmit={handleSubmit} noValidate>
            <Form.Group className="mb-3">
              <Form.Label>Email</Form.Label>
              <Form.Control
                type="email"
                name="email"
                value={formData.email}
                onChange={handleChange}
                placeholder="Ingresa tu email"
                isInvalid={!!errors.email}
                required
              />
              <Form.Control.Feedback type="invalid">
                {errors.email}
              </Form.Control.Feedback>
            </Form.Group>

            <Form.Group className="mb-3">
              <Form.Label>Contraseña</Form.Label>
              <Form.Control
                type="password"
                name="password"
                value={formData.password}
                onChange={handleChange}
                placeholder="Ingresa tu contraseña"
                isInvalid={!!errors.password}
                required
              />
              <Form.Control.Feedback type="invalid">
                {errors.password}
              </Form.Control.Feedback>
            </Form.Group>

            <Button 
              variant="primary" 
              type="submit" 
              className="w-100"
              disabled={loading}
            >
              {loading ? 'Iniciando sesión...' : 'Iniciar Sesión'}
            </Button>

            <p className="text-center mt-3">
              ¿No tienes cuenta? <a href="/register">Regístrate</a>
            </p>
          </Form>
        </Card.Body>
      </Card>
    </Container>
  );
};

export default Login;
