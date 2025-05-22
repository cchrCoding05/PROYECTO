import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { Form, Button, Alert, Container, Card } from 'react-bootstrap';
import { useAuth } from '../../hooks/useAuth';
import './Auth.css';

const Login = () => {
  const navigate = useNavigate();
  const { login } = useAuth();
  const [formData, setFormData] = useState({
    email: '',
    password: ''
  });
  const [error, setError] = useState('');
  const [loading, setLoading] = useState(false);

  // Efecto para cargar el error persistente al montar el componente
  useEffect(() => {
    const persistedError = localStorage.getItem('authError');
    if (persistedError) {
      setError(persistedError);
      localStorage.removeItem('authError');
    }
  }, []);

  const validateEmail = (email) => {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
  };

  const handleChange = (e) => {
    const { name, value } = e.target;
    setFormData(prev => ({
      ...prev,
      [name]: value.trim()
    }));
    setError('');
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setError('');
    setLoading(true);

    try {
      const response = await fetch(`${import.meta.env.VITE_API_URL}/login`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(formData)
      });

      const data = await response.json();

      if (!response.ok) {
        // Guardar el error en localStorage antes del refresco
        const errorMessage = data.error || data.message || 'Error al iniciar sesión';
        localStorage.setItem('authError', errorMessage);
        window.location.reload();
        return;
      }

      if (data.token) {
        // Usar el método login del hook useAuth para actualizar el estado
        await login(formData);
        navigate('/');
      }
    } catch (err) {
      // Guardar el error de conexión en localStorage
      localStorage.setItem('authError', 'Error al conectar con el servidor');
      window.location.reload();
    } finally {
      setLoading(false);
    }
  };

  return (
    <Container className="d-flex justify-content-center align-items-center min-vh-100">
      <Card className="p-4 shadow-sm" style={{ maxWidth: '400px', width: '100%' }}>
        <Card.Body>
          <h2 className="text-center mb-4">Iniciar Sesión</h2>
          
          {error && (
            <Alert 
              variant="danger" 
              onClose={() => {
                setError('');
                localStorage.removeItem('authError');
              }} 
              dismissible
              className="mb-3"
            >
              {error}
            </Alert>
          )}

          <Form onSubmit={handleSubmit}>
            <Form.Group className="mb-3">
              <Form.Label>Email</Form.Label>
              <Form.Control
                type="email"
                name="email"
                value={formData.email}
                onChange={handleChange}
                placeholder="Ingresa tu email"
                isInvalid={!!error}
                required
              />
            </Form.Group>

            <Form.Group className="mb-3">
              <Form.Label>Contraseña</Form.Label>
              <Form.Control
                type="password"
                name="password"
                value={formData.password}
                onChange={handleChange}
                placeholder="Ingresa tu contraseña"
                isInvalid={!!error}
                required
              />
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