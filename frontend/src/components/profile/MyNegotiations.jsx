import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { Card, Button, Container, Row, Col, Alert, Badge } from 'react-bootstrap';
import { negotiationService } from '../../services/api.jsx';
import './Profile.css';

const MyNegotiations = () => {
  const [negotiations, setNegotiations] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const navigate = useNavigate();

  const loadNegotiations = async () => {
    try {
      setLoading(true);
      setError(null);
      const data = await negotiationService.getMyNegotiations();
      setNegotiations(data);
    } catch (err) {
      console.error('Error al cargar negociaciones:', err);
      setError(err.message || 'Error al cargar las negociaciones');
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    loadNegotiations();
  }, []);

  const getStatusBadge = (status, isActive) => {
    if (!isActive) {
      return <Badge bg="secondary">Finalizada</Badge>;
    }
    switch (status) {
      case 1:
        return <Badge bg="primary">Activa</Badge>;
      case 2:
        return <Badge bg="success">Aceptada</Badge>;
      case 3:
        return <Badge bg="danger">Rechazada</Badge>;
      default:
        return <Badge bg="secondary">Desconocido</Badge>;
    }
  };

  const getRoleBadge = (isSeller) => {
    return isSeller ? 
      <Badge bg="info">Vendedor</Badge> : 
      <Badge bg="warning" text="dark">Comprador</Badge>;
  };

  if (loading) {
    return (
      <Container className="mt-4">
        <Alert variant="info">Cargando negociaciones...</Alert>
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

  if (negotiations.length === 0) {
    return (
      <Container className="mt-4">
        <Alert variant="info">
          No tienes negociaciones activas.
        </Alert>
      </Container>
    );
  }

  return (
    <Container className="mt-4">
      <h3>Mis Negociaciones</h3>
      <Row>
        {negotiations.map(negotiation => (
          <Col key={negotiation.id} md={6} className="mb-4">
            <Card>
              <Card.Body>
                <div className="d-flex justify-content-between align-items-start mb-2">
                  {getStatusBadge(negotiation.status, negotiation.isActive)}
                  {getRoleBadge(negotiation.isSeller)}
                </div>
                
                <div className="d-flex align-items-center mb-3">
                  {negotiation.product.image && (
                    <img 
                      src={negotiation.product.image} 
                      alt={negotiation.product.name}
                      className="negotiation-product-image me-3"
                      style={{ width: '80px', height: '80px', objectFit: 'cover' }}
                    />
                  )}
                  <div>
                    <Card.Title>{negotiation.product.name}</Card.Title>
                    <Card.Text>
                      <small className="text-muted">
                        {new Date(negotiation.date).toLocaleDateString()}
                      </small>
                    </Card.Text>
                  </div>
                </div>

                <Card.Text>
                  <strong>Precio original:</strong> {negotiation.product.credits} créditos
                </Card.Text>
                <Card.Text>
                  <strong>Precio propuesto:</strong> {negotiation.proposedCredits} créditos
                </Card.Text>
                <Card.Text>
                  <strong>
                    {negotiation.isSeller ? 'Comprador:' : 'Vendedor:'}
                  </strong> {negotiation.isSeller ? negotiation.buyer.name : negotiation.seller.name}
                </Card.Text>

                <Button 
                  variant="primary" 
                  onClick={() => navigate(`/negotiation/${negotiation.product.id}`)}
                  className="mt-2"
                >
                  Ver Detalles
                </Button>
              </Card.Body>
            </Card>
          </Col>
        ))}
      </Row>
    </Container>
  );
};

export default MyNegotiations; 