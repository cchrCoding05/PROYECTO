import React, { useState, useEffect, useCallback } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { negotiationService } from '../../services/negotiationService';
import { productService } from '../../services/productService';
import { useAuth } from '../../hooks/useAuth';
import AlertMessage from '../Layout/AlertMessage';
import './Negotiation.css';
import { Button } from 'react-bootstrap';
import '../../styles/variables.css';

const ProductNegotiation = () => {
  const { id } = useParams();
  const navigate = useNavigate();
  const { isAuthenticated, user, loading: authLoading } = useAuth();
  const [product, setProduct] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [proposedPrice, setProposedPrice] = useState('');
  const [negotiationError, setNegotiationError] = useState(null);
  const [negotiationSuccess, setNegotiationSuccess] = useState(false);
  const [negotiations, setNegotiations] = useState([]);
  const [actionMessage, setActionMessage] = useState(null);
  const [loadingNegotiations, setLoadingNegotiations] = useState(false);

  // Función para cargar solo el producto
  const loadProduct = useCallback(async () => {
    if (!isAuthenticated || !user) return;

    try {
      setLoading(true);
      setError(null);
      const result = await productService.getById(id);
      
      if (result && result.success === false) {
        setError(result.message || 'Error al cargar el producto');
        return;
      }

      // Asegurarnos de que estamos usando los datos correctos de la respuesta
      const productData = result.data || result;
      console.log('Datos del producto recibidos:', productData);

      if (!productData) {
        setError('Producto no encontrado');
        return;
      }

      const processedProduct = {
        id: productData.id || productData.id_objeto || 0,
        name: productData.titulo || productData.name || productData.title || 'Producto sin nombre',
        credits: productData.creditos || productData.credits || 0,
        image: productData.imagen || productData.image || 'https://via.placeholder.com/400?text=Sin+Imagen',
        state: productData.estado || productData.state || 1,
        seller: {
          id: productData.seller?.id || productData.usuario?.id_usuario || 0,
          username: productData.seller?.name || productData.seller?.username || 
                   productData.usuario?.nombreUsuario || 'Vendedor desconocido'
        }
      };

      console.log('Producto procesado:', processedProduct);
      setProduct(processedProduct);
    } catch (err) {
      console.error('Error al cargar producto:', err);
      setError('Error al cargar el producto');
    } finally {
      setLoading(false);
    }
  }, [isAuthenticated, user, id]);

  // Función para cargar solo las negociaciones
  const loadNegotiations = useCallback(async () => {
    if (!isAuthenticated || !user) return;

    try {
      setLoadingNegotiations(true);
      const result = await productService.getNegotiations(id);
      
      if (result && result.success === false) {
        console.error('Error al cargar negociaciones:', result.message);
        return;
      }

      const sortedNegotiations = Array.isArray(result.data) 
        ? result.data.sort((a, b) => new Date(b.createdAt) - new Date(a.createdAt))
        : [];
      
      setNegotiations(sortedNegotiations);
    } catch (err) {
      console.error('Error al cargar negociaciones:', err);
    } finally {
      setLoadingNegotiations(false);
    }
  }, [isAuthenticated, user, id]);

  // Función para determinar si el usuario es el propietario
  const isOwner = useCallback(() => {
    if (!user || !product) {
      console.log('No hay usuario o producto para verificar propiedad');
      return false;
    }

    const esPropietario = user.id === product.seller?.id;
    console.log('Verificando propiedad:', {
      userId: user.id,
      sellerId: product.seller?.id,
      productId: product.id,
      seller: product.seller,
      esPropietario,
      user: {
        id: user.id,
        username: user.username
      },
      product: {
        id: product.id,
        name: product.name,
        seller: product.seller
      }
    });
    return esPropietario;
  }, [user, product]);

  // Función para determinar si el usuario es el comprador de una negociación
  const isBuyer = useCallback((negotiation) => {
    if (!user || !negotiation) {
      console.log('No hay usuario o negociación para verificar comprador');
      return false;
    }

    const esComprador = user.id === negotiation.user?.id;
    console.log('Verificando comprador:', {
      userId: user.id,
      negotiationUserId: negotiation.user?.id,
      negotiationId: negotiation.id,
      esComprador,
      user: {
        id: user.id,
        username: user.username
      },
      negotiation: {
        id: negotiation.id,
        userId: negotiation.user?.id,
        username: negotiation.user?.username
      }
    });
    return esComprador;
  }, [user]);

  // Efecto para la carga inicial
  useEffect(() => {
    const initializeData = async () => {
      if (authLoading) return;

      if (!isAuthenticated || !user) {
        navigate('/login', { state: { from: `/negotiate/product/${id}` } });
        return;
      }

      await loadProduct();
      await loadNegotiations();
    };

    initializeData();
  }, [isAuthenticated, user, authLoading, id, navigate, loadProduct, loadNegotiations]);

  // Efecto para actualizar solo las negociaciones cada 30 segundos
  useEffect(() => {
    if (!isAuthenticated || !user) return;

    const intervalId = setInterval(loadNegotiations, 30000);
    return () => clearInterval(intervalId);
  }, [isAuthenticated, user, loadNegotiations]);

  const handleProposePrice = async (e) => {
    e.preventDefault();
    setNegotiationError(null);
    setNegotiationSuccess(false);
    if (!proposedPrice || parseInt(proposedPrice) < 1) {
      setNegotiationError('El monto debe ser al menos 1 punto');
      return;
    }
    // Validar saldo del comprador si no es el vendedor
    if (user && product && user.id !== product.seller.id && user.credits < parseInt(proposedPrice)) {
      setNegotiationError('No tienes suficientes puntos para ofertar');
      return;
    }
    try {
      const result = await productService.proposePrice(id, { price: parseInt(proposedPrice) });
      if (result && result.success === false) {
        setNegotiationError(result.message || 'Error al proponer precio');
        return;
      }
      setNegotiationSuccess(true);
      setProposedPrice('');
      loadNegotiations();
    } catch (err) {
      setNegotiationError('Error al proponer precio');
    }
  };

  const handleAcceptOffer = async (negotiationId) => {
    try {
      setActionMessage(null);
      console.log('Aceptando oferta:', { negotiationId, productId: id });
      const result = await productService.acceptOffer(id, negotiationId);
      if (result && result.success === false) {
        setNegotiationError(result.message || 'Error al aceptar la oferta');
        return;
      }
      setActionMessage({ type: 'success', text: '¡Oferta aceptada con éxito!' });
      setNegotiationSuccess(true);
      // Recargar tanto el producto como las negociaciones
      await Promise.all([loadProduct(), loadNegotiations()]);
    } catch (err) {
      console.error('Error al aceptar oferta:', err);
      setNegotiationError('Error al aceptar la oferta');
    }
  };

  const handleRejectOffer = async (negotiationId) => {
    try {
      setActionMessage(null);
      console.log('Rechazando oferta:', { negotiationId, productId: id });
      const result = await productService.rejectOffer(id, negotiationId);
      if (result && result.success === false) {
        setNegotiationError(result.message || 'Error al rechazar la oferta');
        return;
      }
      setActionMessage({ type: 'success', text: '¡Oferta rechazada con éxito!' });
      // Recargar tanto el producto como las negociaciones
      await Promise.all([loadProduct(), loadNegotiations()]);
    } catch (err) {
      console.error('Error al rechazar oferta:', err);
      setNegotiationError('Error al rechazar la oferta');
    }
  };

  const handleEditProduct = () => {
    navigate(`/edit-product/${id}`);
  };

  if (authLoading || loading) {
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

  if (!isAuthenticated || !user) {
    console.log('Renderizando mensaje de no autenticado');
    return (
      <div className="container py-4">
        <AlertMessage 
          message="Debes iniciar sesión para negociar precios" 
          type="warning" 
        />
        <div className="text-center mt-3">
          <Button 
            variant="primary" 
            onClick={() => navigate('/login', { state: { from: `/negotiate/product/${id}` } })}
          >
            Ir a iniciar sesión
          </Button>
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

  const isOwnerValue = isOwner();
  console.log('Estado del propietario:', { 
    userId: user?.id, 
    sellerId: product?.seller?.id, 
    isOwner: isOwnerValue 
  });

  return (
    <div className="container py-4">
      <h2 className="text-center display-5 mb-4 text-primary">
        Negociación: {product.name}
      </h2>
      <div className="row">
        <div className="col-md-8">
          <div className="card shadow-sm">
            <div className="card-body">
              <h5 className="card-title mb-4">Detalles del Producto</h5>
              {actionMessage && (
                <AlertMessage 
                  message={actionMessage.text} 
                  type={actionMessage.type} 
                />
              )}
              <div className="row">
                <div className="col-md-6">
                  <img 
                    src={product.image} 
                    alt={product.name} 
                    className="img-fluid rounded mb-3"
                    style={{ maxHeight: '300px', objectFit: 'cover' }}
                  />
                </div>
                <div className="col-md-6">
                  <div className="product-details">
                    <h3>{product.name}</h3>
                    <p className="product-description">{product.description}</p>
                    <div className="product-info">
                      <p><strong>Estado:</strong> {product.state === 1 ? 'Disponible' : product.state === 2 ? 'Reservado' : 'Intercambiado'}</p>
                      <p><strong>Precio:</strong> {product.credits} créditos</p>
                      <p><strong>Vendedor:</strong> {product.seller.username}</p>
                    </div>
                  </div>
                  {isOwnerValue && product.state !== 1 && (
                    <div className="alert alert-info">
                      <i className="bi bi-info-circle me-2"></i>
                      {product.state === 2 
                        ? 'Este producto está reservado. Las negociaciones están en pausa.'
                        : 'Este producto ya ha sido intercambiado.'}
                    </div>
                  )}
                </div>
              </div>
            </div>
          </div>
          {product.state !== 3 && (
            <div className="card shadow-sm mt-4">
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
        <div className="col-md-4">
          <div className="card shadow-sm">
            <div className="card-body">
              <h5 className="card-title mb-4">
                {isOwnerValue ? 'Ofertas recibidas' : 'Historial de negociaciones'}
              </h5>
              {loadingNegotiations && (
                <div className="text-center py-2">
                  <div className="spinner-border spinner-border-sm text-primary" role="status">
                    <span className="visually-hidden">Actualizando...</span>
                  </div>
                </div>
              )}
              {!loadingNegotiations && negotiations.length === 0 ? (
                <div className="text-center py-4">
                  <i className="bi bi-chat-square-text display-4 text-muted mb-3"></i>
                  <p className="text-muted">
                    {isOwnerValue 
                      ? 'Aún no has recibido ofertas para este producto'
                      : 'No hay negociaciones activas para este producto'}
                  </p>
                </div>
              ) : (
                <div className="list-group">
                  {negotiations.map(negotiation => {
                    if (!negotiation || !negotiation.user) {
                      console.warn('Negociación inválida:', negotiation);
                      return null;
                    }

                    // Determinar los estados de la negociación
                    const yaAceptado = negotiation.accepted === true;
                    const esVendedor = isOwner();
                    const esComprador = isBuyer(negotiation);
                    
                    // Modificar la lógica de mostrarBotones
                    const mostrarBotonesVendedor = esVendedor && !yaAceptado && product.state !== 3;
                    const mostrarBotonesComprador = esComprador && !yaAceptado && product.state !== 3 && !esVendedor;
                    const mostrarBotones = mostrarBotonesVendedor || mostrarBotonesComprador;

                    console.log('Renderizando negociación:', {
                      negotiationId: negotiation.id,
                      yaAceptado,
                      esVendedor,
                      esComprador,
                      mostrarBotonesVendedor,
                      mostrarBotonesComprador,
                      mostrarBotones,
                      productState: product.state,
                      userId: user?.id,
                      negotiationUserId: negotiation.user?.id,
                      sellerId: product.seller?.id,
                      user: {
                        id: user?.id,
                        username: user?.username
                      },
                      product: {
                        id: product.id,
                        seller: product.seller,
                        state: product.state
                      },
                      negotiation: {
                        id: negotiation.id,
                        user: negotiation.user,
                        accepted: negotiation.accepted
                      }
                    });

                    return (
                      <div 
                        key={negotiation.id} 
                        className={`list-group-item ${yaAceptado ? 'accepted-negotiation' : ''}`}
                      >
                        <div className="d-flex justify-content-between align-items-start">
                          <div className="flex-grow-1">
                            <div className="d-flex align-items-center mb-2">
                              <h6 className="mb-0 me-2">
                                {negotiation.user?.username || 'Usuario desconocido'}
                              </h6>
                              {yaAceptado && (
                                <span className="badge bg-success ms-2">
                                  Aceptada
                                </span>
                              )}
                            </div>
                            <p className="mb-1 h5 text-primary">
                              {negotiation.proposedCredits} créditos
                            </p>
                            <small className="text-muted d-block mb-2">
                              {new Date(negotiation.createdAt).toLocaleString()}
                            </small>
                            {mostrarBotones && (
                              <div className="mt-2">
                                {esVendedor && !esComprador && (
                                  <p className="text-info small mb-1">
                                    <i className="bi bi-info-circle me-1"></i>
                                    Como vendedor, puedes aceptar o rechazar esta oferta
                                  </p>
                                )}
                                {esComprador && !esVendedor && (
                                  <p className="text-info small mb-1">
                                    <i className="bi bi-info-circle me-1"></i>
                                    Como comprador, puedes rechazar esta oferta
                                  </p>
                                )}
                              </div>
                            )}
                          </div>
                          {mostrarBotones && (
                            <div className="d-flex gap-2 ms-3">
                              {mostrarBotonesVendedor && (
                                <>
                                  <Button
                                    variant="success"
                                    size="sm"
                                    onClick={() => handleAcceptOffer(negotiation.id)}
                                  >
                                    <i className="bi bi-check-lg me-1"></i>
                                    Aceptar
                                  </Button>
                                  <Button
                                    variant="danger"
                                    size="sm"
                                    onClick={() => handleRejectOffer(negotiation.id)}
                                  >
                                    <i className="bi bi-x-lg me-1"></i>
                                    Rechazar
                                  </Button>
                                </>
                              )}
                              {mostrarBotonesComprador && (
                                <Button
                                  variant="danger"
                                  size="sm"
                                  onClick={() => handleRejectOffer(negotiation.id)}
                                >
                                  <i className="bi bi-x-lg me-1"></i>
                                  Rechazar
                                </Button>
                              )}
                            </div>
                          )}
                        </div>
                      </div>
                    );
                  })}
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