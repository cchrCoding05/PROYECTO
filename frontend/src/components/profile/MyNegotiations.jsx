import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { Card, Button, Container, Row, Col, Alert, Badge } from 'react-bootstrap';
import { negotiationService } from "../../services/negotiationService";
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
      // Agrupar negociaciones por producto y mantener solo la más reciente
      const negociacionesAgrupadas = data.reduce((acc, negotiation) => {
        const productId = negotiation.product.id;
        if (!acc[productId] || new Date(negotiation.date) > new Date(acc[productId].date)) {
          acc[productId] = negotiation;
        }
        return acc;
      }, {});
      const negociacionesFiltradas = Object.values(negociacionesAgrupadas);
      setNegotiations(negociacionesFiltradas);
    } catch (err) {
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

  const renderNegotiationCard = (negotiation) => (
    <Col key={negotiation.id}>
      <Card className="h-100 shadow-sm">
        <Card.Img
          variant="top"
          src={negotiation.product.image}
          alt={negotiation.product.name}
          style={{ height: '200px', objectFit: 'cover' }}
          onError={(e) => {
            e.target.onerror = null;
            e.target.src = 'https://via.placeholder.com/300?text=Sin+Imagen';
          }}
        />
        <Card.Body>
          <div className="d-flex justify-content-between align-items-start mb-2">
            {getRoleBadge(negotiation.isSeller)}
            {getStatusBadge(negotiation.status, negotiation.isActive)}
          </div>
          <h5 className="card-title text-primary mb-0">{negotiation.product.name}</h5>
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
          <Card.Text>
            <small className="text-muted">
              Última actualización: {new Date(negotiation.date).toLocaleString()}
            </small>
          </Card.Text>

          <Button 
            variant="primary" 
            onClick={() => navigate(`/negotiate/product/${negotiation.product.id}`)}
            className="mt-2 w-100"
          >
            Ver Negociación
          </Button>
        </Card.Body>
      </Card>
    </Col>
  );

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

  // Solo negociaciones con productos
  const negociacionesConObjetos = negotiations.filter(n => n.product && n.product.id);

  return (
    <Container className="mt-4">
      <h2 className="mb-4">Mis Negociaciones</h2>
      {negociacionesConObjetos.length === 0 ? (
        <div className="text-center my-4">
          <p className="text-muted mb-3">No tienes negociaciones activas con productos.</p>
          <Button 
            variant="primary" 
            onClick={() => navigate('/search')}
          >
            Buscar Productos
          </Button>
        </div>
      ) : (
        <Row xs={1} md={2} lg={3} className="g-4">
          {negociacionesConObjetos.map(renderNegotiationCard)}
        </Row>
      )}
    </Container>
  );
};

export default MyNegotiations; 