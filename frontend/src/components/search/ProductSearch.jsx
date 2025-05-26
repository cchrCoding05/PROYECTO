import React, { useState, useEffect, useCallback } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { productService } from '../../services/productService';
import { useAuth } from '../../hooks/useAuth';
import AlertMessage from '../Layout/AlertMessage';
import './Search.css';

const ProductSearch = () => {
  const { isAuthenticated, user } = useAuth();
  const navigate = useNavigate();
  const [searchQuery, setSearchQuery] = useState('');
  const [products, setProducts] = useState([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);
  const [noResults, setNoResults] = useState(false);
  const [currentPage, setCurrentPage] = useState(1);
  const productsPerPage = 15;

  useEffect(() => {
    const checkAuth = () => {
      if (!isAuthenticated) {
        navigate('/login', { replace: true });
      }
    };
    checkAuth();
  }, [isAuthenticated, navigate]);

  const searchProducts = useCallback(async (query = '') => {
    if (!isAuthenticated || !user) {
      navigate('/login', { replace: true });
      return;
    }

    try {
      setLoading(true);
      setError(null);
      setNoResults(false);
      
      const results = await productService.search(query);
      
      if (results && results.success === false) {
        setError(results.message || 'Error al buscar productos');
        setProducts([]);
        return;
      }
      
      const productsData = results.data || [];
      
      if (productsData.length === 0) {
        setNoResults(true);
        setProducts([]);
        return;
      }
      
      const filteredProducts = productsData.filter(product => 
        (product.estado === 1 || product.state === 1) && 
        (product.seller?.id !== user.id && product.usuario?.id_usuario !== user.id)
      );
      
      const validatedProducts = filteredProducts.map(product => ({
        id: product.id || product.id_objeto || 0,
        name: product.titulo || product.name || product.title || 'Producto sin nombre',
        description: product.descripcion || product.description || 'Sin descripción',
        credits: product.creditos || product.credits || 0,
        imageUrl: product.imagen || product.image || 'https://via.placeholder.com/150',
        state: product.estado || product.state || 1,
        seller: {
          id: product.seller?.id || product.usuario?.id_usuario || 0,
          username: product.seller?.name || product.seller?.username || 
                   product.usuario?.nombreUsuario || 'Vendedor desconocido'
        },
        created_at: product.created_at || product.fechaCreacion || new Date().toISOString()
      }));
      
      setProducts(validatedProducts);
    } catch (err) {
      console.error('Error al buscar productos:', err);
      setError('Error al buscar productos');
      setProducts([]);
    } finally {
      setLoading(false);
    }
  }, [isAuthenticated, user, navigate]);

  // Cargar productos iniciales
  useEffect(() => {
    if (isAuthenticated) {
      searchProducts('');
    }
  }, [isAuthenticated, searchProducts]);

  // Calcular los productos a mostrar en la página actual
  const indexOfLast = currentPage * productsPerPage;
  const indexOfFirst = indexOfLast - productsPerPage;
  const currentProducts = products.slice(indexOfFirst, indexOfLast);
  const totalPages = Math.ceil(products.length / productsPerPage);

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
    setSearchQuery(e.target.value);
  };

  const handlePageChange = (page) => {
    setCurrentPage(page);
  };

  if (!isAuthenticated) {
    return null;
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
          <p className="mt-2 text-muted">Cargando productos...</p>
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
            <p className="text-muted">No hay objetos disponibles en este momento.</p>
          )}
        </div>
      )}
      
      {!loading && !error && !noResults && products.length > 0 && (
        <div className="mt-4">
          <h3 className="text-center mb-4">
            {searchQuery ? `Resultados para "${searchQuery}"` : 'Objetos disponibles'}
          </h3>
          
          <div className="row g-4">
            {currentProducts.map((product) => (
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
                    <p className="card-text small text-muted">
                      Publicado: {new Date(product.created_at).toLocaleDateString()}
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
          {/* Paginación */}
          {totalPages > 1 && (
            <nav className="d-flex justify-content-center mt-4">
              <ul className="pagination">
                {Array.from({ length: totalPages }, (_, i) => (
                  <li key={i} className={`page-item${currentPage === i + 1 ? ' active' : ''}`}>
                    <button className="page-link" onClick={() => handlePageChange(i + 1)}>{i + 1}</button>
                  </li>
                ))}
              </ul>
            </nav>
          )}
        </div>
      )}
    </div>
  );
};

export default ProductSearch;