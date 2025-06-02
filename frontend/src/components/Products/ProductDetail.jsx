import React, { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { productService } from '../../services/productService';
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

  useEffect(() => {
    if (isAuthenticated) {
      loadProduct();
    }
  }, [isAuthenticated, id]);

  const loadProduct = async () => {
    try {
      setLoading(true);
      setError(null);
      
      const result = await productService.getById(id);
      
      if (result && result.success === false) {
        setError(result.message || 'Error al cargar el producto');
        return;
      }
      
      const productData = result.data || result;
      const estado = parseInt(productData.estado) || parseInt(productData.state) || 1;
      
      const processedProduct = {
        id: productData.id || productData.id_objeto || 0,
        name: productData.titulo || productData.name || productData.title || 'Producto sin nombre',
        description: productData.descripcion || productData.description || 'Sin descripción',
        credits: productData.creditos || productData.credits || 0,
        imageUrl: productData.imagen || productData.image || 'https://via.placeholder.com/150',
        state: estado,
        seller: {
          id: productData.seller?.id || productData.usuario?.id_usuario || 0,
          username: productData.seller?.name || productData.seller?.username || 
                   productData.usuario?.nombreUsuario || 'Vendedor desconocido'
        }
      };
      
      setProduct(processedProduct);
      
    } catch (err) {
      console.error('Error al cargar el producto:', err);
      setError('Error al cargar el producto');
    } finally {
      setLoading(false);
    }
  };

  const getStateText = (state) => {
    const estadoNum = parseInt(state);
    switch (estadoNum) {
      case 1: return 'Disponible';
      case 2: return 'Reservado';
      case 3: return 'Intercambiado';
      default: return 'Desconocido';
    }
  };

  const getStateClass = (state) => {
    const estadoNum = parseInt(state);
    switch (estadoNum) {
      case 1: return 'text-success';
      case 2: return 'text-warning';
      case 3: return 'text-danger';
      default: return 'text-muted';
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
            Estado: {getStateText(product.state)}
          </p>
          <p className="text-muted">
            Vendedor: {product.seller.username}
          </p>
          
          {!isOwner && (
            <button 
              className="btn btn-primary mt-3"
              onClick={() => navigate(`/negotiate/product/${product.id}`)}
              disabled={!isAvailable}
            >
              Negociar
            </button>
          )}
          
          {isOwner && (
            <div className="mt-3">
              <button 
                className="btn btn-primary me-2"
                onClick={() => navigate(`/edit-product/${id}`)}
              >
                Editar
              </button>
              <button 
                className="btn btn-danger"
                onClick={() => navigate(`/products/${id}/delete`)}
              >
                Eliminar
              </button>
            </div>
          )}
        </div>
      </div>
    </div>
  );
};

export default ProductDetail;