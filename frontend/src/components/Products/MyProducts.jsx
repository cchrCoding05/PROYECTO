import React, { useState, useEffect } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { Card, Button, Container, Row, Col, Alert } from 'react-bootstrap';
import { productService } from '../../services/productService';
import AlertMessage from '../Layout/AlertMessage';

const MyProducts = () => {
  const [products, setProducts] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [success, setSuccess] = useState(null);
  const navigate = useNavigate();

  const fetchProducts = async () => {
    try {
      const response = await productService.getMyProducts();
      if (response.success) {
        setProducts(response.data);
      }
    } catch (error) {
      setError(error.message);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchProducts();
  }, []);

  const handleDelete = async (productId) => {
    if (!window.confirm('¿Estás seguro de que deseas eliminar este producto?')) return;
    try {
      const response = await productService.delete(productId);
      if (response.success) {
        setSuccess('Producto eliminado correctamente');
        setTimeout(() => {
          window.location.reload();
        }, 1000);
      } else {
        setError(response.message || 'Error al eliminar el producto');
      }
    } catch (err) {
      setError(err.message || 'Error al eliminar el producto');
    }
  };

  if (loading) {
    return (
      <Container className="mt-4">
        <h2>Mis Productos</h2>
        <AlertMessage message="Cargando productos..." type="info" />
      </Container>
    );
  }

  if (error) {
    return (
      <Container className="mt-4">
        <h2>Mis Productos</h2>
        <AlertMessage message={error} type="danger" onClose={() => setError(null)} />
      </Container>
    );
  }

  if (success) {
    return (
      <Container className="mt-4">
        <h2>Mis Productos</h2>
        <AlertMessage message={success} type="success" onClose={() => setSuccess(null)} />
      </Container>
    );
  }

  return (
    <Container className="mt-4">
      <h2>Mis Productos</h2>
      {products.length === 0 ? (
        <AlertMessage message={<span>No tienes productos publicados. <Link to="/upload-product" className="btn btn-primary ms-3">Subir Producto</Link></span>} type="info" />
      ) : (
        <Row>
          {products.map(product => {
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
                    <div className="product-actions">
                      <Button 
                        variant="primary" 
                        onClick={() => navigate(`/negotiate/product/${product.id}`)}
                        className="mt-2 w-100"
                      >
                        Ver Negociación
                      </Button>
                      <Button 
                        variant="secondary" 
                        onClick={() => navigate(`/edit-product/${product.id}`)}
                      >
                        Editar Producto
                      </Button>
                    </div>
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
      )}
    </Container>
  );
};

export default MyProducts;