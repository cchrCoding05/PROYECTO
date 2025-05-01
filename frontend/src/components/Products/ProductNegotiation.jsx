import React, { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { productService } from '../../services/api.jsx';
import { useAuth } from '../../hooks/useAuth';
import AlertMessage from '../Layout/AlertMessage';
import './Products.css';

const ProductNegotiation = () => {
  const { id } = useParams();
  const navigate = useNavigate();
  const { isAuthenticated, user } = useAuth();
  const [product, setProduct] = useState(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);
  const [proposedPrice, setProposedPrice] = useState('');
  const [negotiationError, setNegotiationError] = useState(null);
  const [negotiationSuccess, setNegotiationSuccess] = useState(false);
  const [negotiations, setNegotiations] = useState([]);

  useEffect(() => {
    if (isAuthenticated) {
      loadProduct();
      loadNegotiations();
    }
  }, [isAuthenticated, id]);

  const loadProduct = async () => {
    try {
      setLoading(true);
      setError(null);
      
      const result = await productService.get(id);
      
      if (result && result.success === false) {
        setError(result.message || 'Error al cargar el producto');
        return;
      }
      
      setProduct({
        id: result.id || 0,
        name: result.name || 'Producto sin nombre',
        credits: result.credits || 0,
        seller: {
          id: result.seller?.id || 0,
          username: result.seller?.username || 'Vendedor desconocido'
        }
      });
    } catch (err) {
      console.error('Error al cargar el producto:', err);
      setError('Error al cargar el producto');
    } finally {
      setLoading(false);
    }
  };

  const loadNegotiations = async () => {
    try {
      const result = await productService.getNegotiations(id);
      
      if (result && result.success === false) {
        console.error('Error al cargar negociaciones:', result.message);
        return;
      }
      
      setNegotiations(Array.isArray(result) ? result : []);
    } catch (err) {
      console.error('Error al cargar negociaciones:', err);
    }
  };

  const handleProposePrice = async (e) => {
    e.preventDefault();
    try {
      setNegotiationError(null);
      setNegotiationSuccess(false);
      
      const result = await productService.proposePrice(id, {
        proposedCredits: parseInt(proposedPrice)
      });
      
      if (result && result.success === false) {
        setNegotiationError(result.message || 'Error al proponer precio');
        return;
      }
      
      setNegotiationSuccess(true);
      setProposedPrice('');
      loadNegotiations(); // Recargar las negociaciones
    } catch (err) {
      console.error('Error al proponer precio:', err);
      setNegotiationError('Error al proponer precio');
    }
  };

  const handleAcceptOffer = async (negotiationId) => {
    try {
      const result = await productService.acceptOffer(id, negotiationId);
      
      if (result && result.success === false) {
        setNegotiationError(result.message || 'Error al aceptar la oferta');
        return;
      }
      
      setNegotiationSuccess(true);
      loadNegotiations(); // Recargar las negociaciones
    } catch (err) {
      console.error('Error al aceptar la oferta:', err);
      setNegotiationError('Error al aceptar la oferta');
    }
  };

  const handleRejectOffer = async (negotiationId) => {
    try {
      const result = await productService.rejectOffer(id, negotiationId);
      
      if (result && result.success === false) {
        setNegotiationError(result.message || 'Error al rechazar la oferta');
        return;
      }
      
      loadNegotiations(); // Recargar las negociaciones
    } catch (err) {
      console.error('Error al rechazar la oferta:', err);
      setNegotiationError('Error al rechazar la oferta');
    }
  };

  if (!isAuthenticated) {
    return (
      <div className="container py-4">
        <AlertMessage 
          message="Debes iniciar sesión para negociar precios" 
          type="warning" 
        />
      </div>
    );
  }

  if (loading) {
    return (
      <div className="container py-4">
        <div className="text-center">
          <div className="spinner-border text-primary" role="status">
            <span className="visually-hidden">Cargando...</span>
          </div>
        </div>
      </div>
    );
  }

  if (error) {
    return (
      <div className="container py-4">
        <AlertMessage 
          message={error} 
          type="danger" 
        />
      </div>
    );
  }

  if (!product) {
    return null;
  }

  const isOwner = user && user.id === product.seller.id;

  return (
    <div className="container py-4">
      <h2 className="text-center display-5 mb-4">
        Negociación: {product.name}
      </h2>
      
      <div className="row">
        <div className="col-md-6">
          <div className="card shadow-sm mb-4">
            <div className="card-body">
              <h5 className="card-title">Precio actual</h5>
              <p className="h3">{product.credits} créditos</p>
              <p className="text-muted">
                Vendedor: {product.seller.username}
              </p>
            </div>
          </div>
          
          {!isOwner && (
            <div className="card shadow-sm">
              <div className="card-body">
                <h5 className="card-title">Proponer nuevo precio</h5>
                {negotiationSuccess && (
                  <AlertMessage 
                    message="Precio propuesto con éxito" 
                    type="success" 
                  />
                )}
                {negotiationError && (
                  <AlertMessage 
                    message={negotiationError} 
                    type="danger" 
                  />
                )}
                <form onSubmit={handleProposePrice}>
                  <div className="input-group mb-3">
                    <input
                      type="number"
                      className="form-control"
                      value={proposedPrice}
                      onChange={(e) => setProposedPrice(e.target.value)}
                      placeholder="Ingresa tu oferta en créditos"
                      min="1"
                      required
                    />
                    <button 
                      className="btn btn-primary" 
                      type="submit"
                    >
                      Proponer
                    </button>
                  </div>
                </form>
              </div>
            </div>
          )}
        </div>
        
        <div className="col-md-6">
          <div className="card shadow-sm">
            <div className="card-body">
              <h5 className="card-title">Historial de negociaciones</h5>
              {negotiations.length === 0 ? (
                <p className="text-muted">No hay negociaciones aún</p>
              ) : (
                <div className="list-group">
                  {negotiations.map(negotiation => (
                    <div 
                      key={negotiation.id} 
                      className="list-group-item"
                    >
                      <div className="d-flex justify-content-between align-items-center">
                        <div>
                          <h6 className="mb-1">
                            {negotiation.buyer.username} propuso:
                          </h6>
                          <p className="mb-1">
                            {negotiation.proposedCredits} créditos
                          </p>
                          <small className="text-muted">
                            {new Date(negotiation.createdAt).toLocaleString()}
                          </small>
                        </div>
                        {isOwner && negotiation.status === 'pending' && (
                          <div>
                            <button
                              className="btn btn-success btn-sm me-2"
                              onClick={() => handleAcceptOffer(negotiation.id)}
                            >
                              Aceptar
                            </button>
                            <button
                              className="btn btn-danger btn-sm"
                              onClick={() => handleRejectOffer(negotiation.id)}
                            >
                              Rechazar
                            </button>
                          </div>
                        )}
                        {negotiation.status !== 'pending' && (
                          <span className={`badge bg-${negotiation.status === 'accepted' ? 'success' : 'danger'}`}>
                            {negotiation.status === 'accepted' ? 'Aceptada' : 'Rechazada'}
                          </span>
                        )}
                      </div>
                    </div>
                  ))}
                </div>
              )}
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default ProductNegotiation; 