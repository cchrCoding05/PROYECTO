import React, { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { productService } from '../../services/api.jsx';
import { useAuth } from '../../hooks/useAuth';
import AlertMessage from '../Layout/AlertMessage';
import './Products.css';

const ProductDetail = () => {
  const { id } = useParams();
  const navigate = useNavigate();
  const { isAuthenticated, user } = useAuth();
  const [product, setProduct] = useState(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);
  const [proposedPrice, setProposedPrice] = useState('');
  const [negotiationError, setNegotiationError] = useState(null);
  const [negotiationSuccess, setNegotiationSuccess] = useState(false);

  useEffect(() => {
    if (isAuthenticated) {
      loadProduct();
    }
  }, [isAuthenticated, id]);

  const loadProduct = async () => {
    try {
      setLoading(true);
      setError(null);
      
      const result = await productService.get(id);
      console.log('Resultado completo de la API:', result);
      
      if (result && result.success === false) {
        setError(result.message || 'Error al cargar el producto');
        return;
      }
      
      // Asegurarnos de que el estado sea un número
      const estado = parseInt(result.estado) || parseInt(result.state) || 1;
      console.log('Estado parseado:', estado);
      
      const productData = {
        id: result.id || 0,
        name: result.title || result.name || 'Producto sin nombre',
        description: result.description || 'Sin descripción',
        credits: result.credits || 0,
        imageUrl: result.image || result.imageUrl || 'https://via.placeholder.com/150',
        state: estado,
        seller: {
          id: result.seller?.id || 0,
          username: result.seller?.username || result.seller?.name || 'Vendedor desconocido'
        }
      };
      
      console.log('Datos del producto a guardar:', productData);
      setProduct(productData);
      
    } catch (err) {
      console.error('Error al cargar el producto:', err);
      setError('Error al cargar el producto');
    } finally {
      setLoading(false);
    }
  };

  const getStateText = (state) => {
    const estadoNum = parseInt(state);
    console.log('Estado para texto:', estadoNum);
    switch (estadoNum) {
      case 1: return 'Disponible';
      case 2: return 'Reservado';
      case 3: return 'Intercambiado';
      default: return 'Desconocido';
    }
  };

  const getStateClass = (state) => {
    const estadoNum = parseInt(state);
    console.log('Estado para clase:', estadoNum);
    switch (estadoNum) {
      case 1: return 'text-success';
      case 2: return 'text-warning';
      case 3: return 'text-danger';
      default: return 'text-muted';
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
    } catch (err) {
      console.error('Error al proponer precio:', err);
      setNegotiationError(err.message || 'Error al proponer precio');
    }
  };

  if (!isAuthenticated) {
    return (
      <div className="container py-4">
        <AlertMessage 
          message="Debes iniciar sesión para ver los detalles del producto" 
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
  const isAvailable = product.state === 1;

  return (
    <div className="container py-4">
      <div className="row">
        <div className="col-md-6">
          <img 
            src={product.imageUrl} 
            className="img-fluid rounded shadow" 
            alt={product.name}
            style={{ maxHeight: '400px', objectFit: 'cover' }}
            onError={(e) => {
              e.target.onerror = null;
              e.target.src = 'https://via.placeholder.com/400?text=Sin+Imagen';
            }}
          />
        </div>
        <div className="col-md-6">
          <h1 className="display-4 mb-4">{product.name}</h1>
          <p className="lead">{product.description}</p>
          <p className="h3 mb-4">{product.credits} créditos</p>
          <p className={`h5 mb-4 ${getStateClass(product.state)}`}>
            Estado: {getStateText(product.state)} ({product.state})
          </p>
          <p className="text-muted">
            Vendedor: {product.seller.username}
          </p>
          
          {!isOwner && (
            <div className="mt-4">
              <h4>Proponer precio</h4>
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
              {product.state !== 1 && (
                <AlertMessage 
                  message="Este producto no está disponible para negociación" 
                  type="warning" 
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
                    disabled={product.state !== 1}
                  />
                  <button 
                    className="btn btn-primary" 
                    type="submit"
                    disabled={product.state !== 1}
                  >
                    Proponer
                  </button>
                </div>
              </form>
            </div>
          )}
          
          {isOwner && (
            <div className="mt-4">
              <button 
                className="btn btn-primary me-2"
                onClick={() => navigate(`/products/${id}/edit`)}
                disabled={!isAvailable}
              >
                Editar producto
              </button>
              <button 
                className="btn btn-danger"
                onClick={() => navigate(`/products/${id}/delete`)}
                disabled={!isAvailable}
              >
                Eliminar producto
              </button>
            </div>
          )}
        </div>
      </div>
    </div>
  );
};

export default ProductDetail; 