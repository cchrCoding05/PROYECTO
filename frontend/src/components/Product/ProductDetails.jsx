import React, { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { productService } from '../../services/api';
import { useAuth } from '../../hooks/useAuth';
import AlertMessage from '../Layout/AlertMessage';
import './ProductDetails.css';

const ProductDetails = () => {
  const { id } = useParams();
  const navigate = useNavigate();
  const { isAuthenticated } = useAuth();
  const [product, setProduct] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    const fetchProduct = async () => {
      try {
        const data = await productService.get(id);
        console.log('Product data:', data); // Para debug
        setProduct(data);
      } catch (err) {
        setError(err.message || 'Error al cargar el producto');
      } finally {
        setLoading(false);
      }
    };

    fetchProduct();
  }, [id]);

  const getStateText = (estado) => {
    switch (estado) {
      case 1: return 'Disponible';
      case 2: return 'Reservado';
      case 3: return 'Intercambiado';
      default: return 'Desconocido';
    }
  };

  const getStateClass = (estado) => {
    switch (estado) {
      case 1: return 'text-success';
      case 2: return 'text-warning';
      case 3: return 'text-danger';
      default: return 'text-muted';
    }
  };

  const handleContactSeller = () => {
    if (!isAuthenticated) {
      navigate('/login');
      return;
    }
    navigate(`/products/${id}/negotiate`);
  };

  if (loading) {
    return (
      <div className="container py-4">
        <div className="text-center">
          <div className="spinner-border text-primary" role="status">
            <span className="visually-hidden">Cargando...</span>
          </div>
          <p className="mt-2">Cargando detalles del producto...</p>
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
    return (
      <div className="container py-4">
        <AlertMessage 
          message="Producto no encontrado" 
          type="warning" 
        />
      </div>
    );
  }

  return (
    <div className="container py-4">
      <div className="product-details-card">
        <div className="row">
          {/* Sección de imagen */}
          <div className="col-md-6">
            <div className="product-image-container">
              <img
                src={product.image || 'https://via.placeholder.com/400'}
                alt={product.title}
                className="product-image"
                onError={(e) => {
                  e.target.onerror = null;
                  e.target.src = 'https://via.placeholder.com/400?text=Sin+Imagen';
                }}
              />
            </div>
          </div>

          {/* Sección de información */}
          <div className="col-md-6">
            <div className="product-info">
              <h1 className="product-title">{product.title}</h1>
              
              <div className="product-price">
                <span className="price-label">Precio:</span>
                <span className="price-value">{product.credits} créditos</span>
              </div>

              <div className="product-state">
                <span className="state-label">Estado:</span>
                <span className={`state-value ${getStateClass(product.estado)}`}>
                  {getStateText(product.estado)}
                </span>
              </div>

              <div className="product-description">
                <h3>Descripción</h3>
                <p>{product.description}</p>
              </div>

              <div className="product-seller">
                <h3>Vendedor</h3>
                <div className="seller-info">
                  <span className="seller-name">{product.seller?.name || 'Vendedor desconocido'}</span>
                  <span className="seller-rating">
                    <i className="bi bi-star-fill"></i> {product.seller?.rating || 'Sin valoración'}
                  </span>
                </div>
              </div>

              <div className="product-meta">
                <span className="creation-date">
                  Publicado el: {new Date(product.created_at).toLocaleDateString()}
                </span>
              </div>

              <div className="product-actions">
                <button
                  className="btn btn-primary w-100"
                  onClick={handleContactSeller}
                  disabled={product.estado !== 1}
                >
                  {product.estado === 1 ? 'Contactar con el vendedor' : 'Producto no disponible'}
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default ProductDetails; 