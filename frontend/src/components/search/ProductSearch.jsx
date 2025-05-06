import React, { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import { productService } from '../../services/api.jsx';
import { useAuth } from '../../hooks/useAuth';
import AlertMessage from '../Layout/AlertMessage';
import './Search.css';

const ProductSearch = () => {
  const { isAuthenticated } = useAuth();
  const [searchQuery, setSearchQuery] = useState('');
  const [products, setProducts] = useState([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);
  const [noResults, setNoResults] = useState(false);

  useEffect(() => {
    if (isAuthenticated) {
      searchProducts();
    }
  }, [isAuthenticated]);

  const searchProducts = async (query = '') => {
    try {
      setLoading(true);
      setError(null);
      setNoResults(false);
      
      console.log('Iniciando búsqueda de objetos...');
      const results = await productService.search(query);
      console.log('Resultados de la búsqueda:', results);
      
      if (!results || (results.success === false)) {
        console.error('Error en la respuesta:', results);
        setError(results?.message || 'Error desconocido en la respuesta');
        setProducts([]);
        return;
      } 
      
      const productsArray = Array.isArray(results) ? results : [];
      console.log('Productos validados:', productsArray);
      
      // Filtrar objetos intercambiados y mostrarlos en consola
      const exchangedProducts = productsArray.filter(product => product.estado === 3);
      if (exchangedProducts.length > 0) {
        console.log('Objetos intercambiados encontrados:', exchangedProducts);
      }
      
      // Filtrar solo objetos disponibles y reservados
      const filteredProducts = productsArray.filter(product => 
        product.estado === 1 || product.estado === 2
      );
      
      if (filteredProducts.length === 0) {
        console.log('No se encontraron productos disponibles o reservados');
        setNoResults(true);
        setProducts([]);
        return;
      }
      
      const validatedProducts = filteredProducts.map(product => ({
        ...product,
        name: product.title || 'Producto sin nombre',
        description: product.description || 'Sin descripción',
        credits: product.credits || 0,
        imageUrl: product.image || 'https://via.placeholder.com/150',
        state: product.estado || 1,
        seller: {
          id: product.seller?.id || 0,
          username: product.seller?.username || 'Vendedor desconocido'
        }
      }));
      
      console.log('Productos finales:', validatedProducts);
      setProducts(validatedProducts);
      
    } catch (err) {
      console.error('Error en la búsqueda:', err);
      setError(err.message || 'Error al realizar la búsqueda');
      setProducts([]);
    } finally {
      setLoading(false);
    }
  };

  const getStateText = (state) => {
    switch (state) {
      case 1: return 'Disponible';
      case 2: return 'Reservado';
      case 3: return 'Intercambiado';
      default: return 'Desconocido';
    }
  };

  const getStateClass = (state) => {
    switch (state) {
      case 1: return 'text-success';
      case 2: return 'text-warning';
      case 3: return 'text-danger';
      default: return 'text-muted';
    }
  };

  const handleSearch = (e) => {
    e.preventDefault();
    searchProducts(searchQuery);
  };

  const handleInputChange = (e) => {
    const query = e.target.value;
    setSearchQuery(query);
    
    if (!query.trim()) {
      searchProducts('');
    }
  };

  if (!isAuthenticated) {
    return (
      <div className="search-container">
        <AlertMessage 
          message="Debes iniciar sesión para buscar productos" 
          type="warning" 
        />
      </div>
    );
  }

  return (
    <div className="container py-4">
      <h2 className="text-center display-5 mb-4">OBJETOS</h2>
      
      <form onSubmit={handleSearch} className="mb-5">
        <div className="input-group shadow-sm mx-auto" style={{ maxWidth: '600px' }}>
          <input
            type="text"
            value={searchQuery}
            onChange={handleInputChange}
            placeholder="Buscar objetos..."
            className="form-control py-3 border-0"
          />
          <button type="submit" className="btn btn-primary px-4">
            <i className="bi bi-search"></i>
          </button>
        </div>
      </form>
      
      {loading && (
        <div className="text-center my-4">
          <div className="spinner-border text-primary" role="status">
            <span className="visually-hidden">Cargando...</span>
          </div>
          <p className="mt-2 text-muted">Cargando...</p>
        </div>
      )}
      
      {error && (
        <div className="alert alert-danger text-center my-4" role="alert">
          {error}
        </div>
      )}
      
      {noResults && !loading && !error && (
        <div className="text-center bg-light bg-opacity-25 p-4 rounded my-4">
          <h3 className="fw-bold text-secondary">No se encontraron objetos</h3>
          {searchQuery ? (
            <p className="text-muted">No hay resultados para "<strong>{searchQuery}</strong>". Intenta con otra búsqueda.</p>
          ) : (
            <p className="text-muted">No hay objetos disponibles o reservados en este momento.</p>
          )}
        </div>
      )}
      
      {!loading && !error && !noResults && products.length > 0 && (
        <div className="mt-4">
          <h3 className="text-center mb-4">
            {searchQuery ? `Resultados para "${searchQuery}"` : 'Objetos disponibles y reservados'}
          </h3>
          
          <div className="row g-4">
            {products.map((product) => (
              <div key={product.id} className="col-md-4">
                <div className="card h-100 shadow-sm">
                  <img 
                    src={product.imageUrl} 
                    className="card-img-top" 
                    alt={product.name}
                    style={{ height: '200px', objectFit: 'cover' }}
                    onError={(e) => {
                      e.target.onerror = null;
                      e.target.src = 'https://via.placeholder.com/150?text=Sin+Imagen';
                    }}
                  />
                  <div className="card-body">
                    <h5 className="card-title">{product.name}</h5>
                    <p className="card-text">{product.description}</p>
                    <p className="card-text">
                      <small className="text-muted">
                        {product.credits} créditos
                      </small>
                    </p>
                    <p className={`card-text ${getStateClass(product.state)}`}>
                      <small>
                        Estado: {getStateText(product.state)}
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
                      to={`/product/${product.id}`}
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
      )}
    </div>
  );
};

export default ProductSearch; 