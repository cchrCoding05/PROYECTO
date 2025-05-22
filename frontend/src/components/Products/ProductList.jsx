import React, { useState, useCallback, useEffect } from 'react';
import { Link } from 'react-router-dom';
import { productService } from '../../services/productService';
import { useAuth } from '../../hooks/useAuth';
import useAutoRefresh from '../../hooks/useAutoRefresh';
import AlertMessage from '../Layout/AlertMessage';
import './Products.css';

const ProductList = () => {
  const { isAuthenticated } = useAuth();
  const [products, setProducts] = useState([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);

  const loadProducts = useCallback(async () => {
    if (!isAuthenticated) {
      console.log('Usuario no autenticado, saltando carga de productos');
      return;
    }

    console.log('Iniciando carga de productos...');
    try {
      setLoading(true);
      setError(null);
      
      console.log('Llamando a productService.search...');
      const results = await productService.search('');
      console.log('Resultados recibidos:', results);
      
      if (results && results.success === false) {
        console.error('Error en la respuesta:', results);
        setError(results.message || 'Error al cargar productos');
        setProducts([]);
        return;
      }
      
      const productsArray = Array.isArray(results) ? results : [];
      console.log('Número de productos encontrados:', productsArray.length);
      
      const validatedProducts = productsArray.map(product => ({
        id: product.id || 0,
        name: product.name || product.title || 'Producto sin nombre',
        description: product.description || 'Sin descripción',
        credits: product.credits || 0,
        imageUrl: product.imageUrl || product.image || 'https://via.placeholder.com/150',
        seller: {
          id: product.seller?.id || product.user?.id || 0,
          username: product.seller?.username || product.user?.username || 'Vendedor desconocido'
        }
      }));
      
      console.log('Productos validados:', validatedProducts.length);
      setProducts(validatedProducts);
    } catch (err) {
      console.error('Error al cargar productos:', err);
      setError('Error al cargar los productos');
      setProducts([]);
    } finally {
      console.log('Finalizando carga de productos');
      setLoading(false);
    }
  }, [isAuthenticated]);

  // Usar el hook de actualización automática
  useAutoRefresh(loadProducts, [isAuthenticated]);

  if (!isAuthenticated) {
    return (
      <div className="container py-4">
        <AlertMessage 
          message="Debes iniciar sesión para ver los productos" 
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

  return (
    <div className="container py-4">
      <h2 className="text-center display-5 mb-4">LISTA DE PRODUCTOS</h2>
      
      <div className="row row-cols-1 row-cols-md-3 g-4">
        {products.map(product => (
          <div key={product.id} className="col">
            <div className="card h-100 shadow-sm">
              <img 
                src={product.imageUrl} 
                className="card-img-top" 
                alt={product.name}
                style={{ height: '200px', objectFit: 'cover' }}
              />
              <div className="card-body">
                <h5 className="card-title">{product.name}</h5>
                <p className="card-text">{product.description}</p>
                <p className="card-text">
                  <small className="text-muted">
                    {product.credits} créditos
                  </small>
                </p>
                <p className="card-text">
                  <small className="text-muted">
                    Vendedor: {product.seller.username}
                  </small>
                </p>
              </div>
              <div className="card-footer bg-transparent">
                <Link 
                  to={`/products/${product.id}`}
                  className="btn btn-primary w-100"
                >
                  Ver detalles
                </Link>
              </div>
            </div>
          </div>
        ))}
      </div>
    </div>
  );
};

export default ProductList; 