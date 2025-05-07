import React, { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import { Card, Button, Container, Row, Col, Alert } from 'react-bootstrap';
import { productService } from '../../services/api.jsx';

const MyProducts = () => {
  const [products, setProducts] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  const loadProducts = async () => {
    try {
      console.log('Iniciando carga de productos...');
      setLoading(true);
      setError(null);
      
      const response = await productService.getMyProducts();
      console.log('Respuesta de getMyProducts:', response);
      
      if (response.success) {
        console.log('Productos obtenidos:', response.data);
        setProducts(response.data);
      } else {
        console.error('Error en la respuesta:', response);
        setError(response.message || 'Error al cargar los productos');
      }
    } catch (err) {
      console.error('Error completo:', err);
      console.error('Stack trace:', err.stack);
      setError(err.message || 'Error al cargar los productos');
    } finally {
      setLoading(false);
    }
  };

  const handleDelete = async (productId) => {
    if (!window.confirm('¿Estás seguro de que deseas eliminar este producto?')) return;
    try {
      await productService.delete(productId);
      loadProducts();
    } catch (err) {
      alert('Error al eliminar el producto');
    }
  };

  useEffect(() => {
    console.log('MyProducts montado, iniciando carga...');
    loadProducts();
  }, []);

  if (loading) {
    return (
      <Container className="mt-4">
        <Alert variant="info">Cargando productos...</Alert>
      </Container>
    );
  }

  if (error) {
    return (
      <Container className="mt-4">
        <Alert variant="danger">
          Error: {error}
        </Alert>
      </Container>
    );
  }

  if (products.length === 0) {
    return (
      <Container className="mt-4">
        <Alert variant="info">
          No tienes productos publicados.
          <Link to="/upload-product" className="btn btn-primary ms-3">
            Subir Producto
          </Link>
        </Alert>
      </Container>
    );
  }

  return (
    <Container className="mt-4">
      <h2>Mis Productos</h2>
      <Row>
        {products.map(product => {
          console.log('Renderizando producto:', product);
          return (
            <Col key={product.id} md={4} className="mb-4">
              <Card>
                {product.image && (
                  <Card.Img 
                    variant="top" 
                    src={product.image} 
                    alt={product.name}
                    style={{ height: '200px', objectFit: 'cover' }}
                  />
                )}
                <Card.Body>
                  <Card.Title>{product.name}</Card.Title>
                  <Card.Text>{product.description}</Card.Text>
                  <Card.Text>
                    <strong>Precio:</strong> {product.price} créditos
                  </Card.Text>
                  <Card.Text>
                    <strong>Estado:</strong> {product.state === 1 ? 'Disponible' : 
                      product.state === 2 ? 'Reservado' : 'Intercambiado'}
                  </Card.Text>
                  <Link to={`/negotiation/${product.id}`} className="btn btn-primary">
                    Ver Negociación
                  </Link>
                  {product.state === 1 && (
                    <Button variant="danger" className="ms-2" onClick={() => handleDelete(product.id)}>
                      Eliminar
                    </Button>
                  )}
                </Card.Body>
              </Card>
            </Col>
          );
        })}
      </Row>
    </Container>
  );
};

export default MyProducts;