import React, { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import { productService } from '../../services/api.jsx';

const ProductSearch = () => {
  const [searchQuery, setSearchQuery] = useState('');
  const [products, setProducts] = useState([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);
  const [noResults, setNoResults] = useState(false);

  // Función para normalizar texto (quitar tildes y convertir a minúsculas)
  const normalizeText = (text) => {
    if (!text) return '';
    // Convertir a minúsculas y eliminar acentos/tildes
    return text
      .toLowerCase()
      .normalize("NFD")
      .replace(/[\u0300-\u036f]/g, "")
      .trim();
  };

  useEffect(() => {
    // Cargar productos al iniciar
    searchProducts();
  }, []);

  const searchProducts = async (query = '') => {
    try {
      setLoading(true);
      setError(null);
      setNoResults(false);
      
      // Normalizar la consulta para eliminar tildes y acentos
      const normalizedQuery = normalizeText(query);
      
      // Enviar la consulta original al servidor (sin normalizar)
      const results = await productService.search(query);
      
      // Comprobar si la respuesta indica un error
      if (results && results.success === false) {
        setError(results.message || 'Error desconocido en la respuesta');
        setProducts([]);
        return;
      } 
      
      // Asegurarnos de que results es un array
      const productsArray = Array.isArray(results) ? results : [];
      
      // Validar que cada producto tenga la estructura esperada
      const validatedProducts = productsArray.map(product => ({
        ...product,
        name: product.name || 'Producto sin nombre',
        description: product.description || 'Sin descripción',
        category: product.category || 'Sin categoría',
        price: product.price || 0,
        imageUrl: product.imageUrl || 'https://via.placeholder.com/150',
        seller: {
          id: product.seller?.id || 0,
          username: product.seller?.username || 'Vendedor desconocido',
          sales: product.seller?.sales || 0
        },
        // Pre-normalizar los campos de búsqueda para facilitar la comparación
        _normalizedName: normalizeText(product.name || ''),
        _normalizedDescription: normalizeText(product.description || ''),
        _normalizedCategory: normalizeText(product.category || '')
      }));
        
      // Si hay un término de búsqueda, hacemos un filtrado adicional en el cliente
      // usando los campos normalizados pre-calculados
      if (normalizedQuery) {
        const filteredResults = validatedProducts.filter(product => 
          product._normalizedName.includes(normalizedQuery) || 
          product._normalizedDescription.includes(normalizedQuery) ||
          product._normalizedCategory.includes(normalizedQuery)
        );
        
        setProducts(filteredResults);
        
        // Si no hay resultados después del filtrado, mostrar un mensaje
        if (filteredResults.length === 0) {
          setNoResults(true);
        }
      } else {
        setProducts(validatedProducts);
        
        // Si no hay resultados, mostrar un mensaje
        if (validatedProducts.length === 0) {
          setNoResults(true);
        }
      }
    } catch (err) {
      setError('Error al buscar objetos: ' + (err.message || 'Error desconocido'));
      console.error('Error en la búsqueda:', err);
      setProducts([]);
    } finally {
      setLoading(false);
    }
  };

  const handleSearch = (e) => {
    e.preventDefault();
    searchProducts(searchQuery);
  };

  const handleInputChange = (e) => {
    const query = e.target.value;
    setSearchQuery(query);
    
    // Si el campo de búsqueda está vacío, mostrar todos los productos
    if (!query.trim()) {
      searchProducts('');
    }
  };

  // Renderiza un mensaje cuando no hay resultados
  const renderNoResults = () => (
    <div className="text-center bg-light bg-opacity-25 p-4 rounded my-4">
      <div className="display-4 text-muted mb-3"></div>
      <h3 className="fw-bold text-secondary">No se encontraron objetos</h3>
      {searchQuery ? (
        <p className="text-muted">No hay resultados para "<strong>{searchQuery}</strong>". Intenta con otra búsqueda.</p>
      ) : (
        <p className="text-muted">No hay objetos disponibles en este momento.</p>
      )}
    </div>
  );

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
      
      {noResults && !loading && !error && renderNoResults()}

      {!loading && !error && !noResults && products.length > 0 && (
        <div className="mt-4">
          <h3 className="text-center mb-4">
            {searchQuery ? `Resultados para "${searchQuery}"` : 'Todos los objetos'}
          </h3>
          
          <div className="row g-4">
            <div className="col-md-4">
              <div className="card border-0 shadow-sm h-100">
                <div className="card-header bg-transparent border-bottom">
                  <h3 className="text-center mb-0 fs-5">Vendedor</h3>
                </div>
                
                <div className="list-group list-group-flush">
                  {products.map((product) => (
                    <div key={`seller-${product.id}`} className="list-group-item bg-transparent">
                      <div className="fw-bold">{product.seller.username}</div>
                      <div className="text-success small">{product.seller.sales} ventas</div>
                    </div>
                  ))}
                </div>
              </div>
            </div>
            
            <div className="col-md-8">
              <div className="card border-0 shadow-sm h-100">
                <div className="card-header bg-transparent border-bottom">
                  <h3 className="text-center mb-0 fs-5">Producto</h3>
                </div>
                
                <div className="list-group list-group-flush">
                  {products.map((product) => (
                    <div key={`product-${product.id}`} className="list-group-item p-0 bg-transparent">
                      <div className="row g-0">
                        <div className="col-md-4">
                          <img 
                            src={product.imageUrl} 
                            alt={product.name} 
                            className="img-fluid w-100 h-100 object-fit-cover rounded-start"
                            style={{ maxHeight: '180px' }}
                            onError={(e) => {
                              e.target.onerror = null;
                              e.target.src = 'https://via.placeholder.com/150?text=Sin+Imagen';
                            }}
                          />
                        </div>
                        <div className="col-md-8">
                          <div className="p-3">
                            <h4 className="fs-5 mb-1">{product.name}</h4>
                            <div className="small mb-2 text-body-secondary">{product.category}</div>
                            <div className="d-flex align-items-center mb-3">
                              <div className="me-2">💰</div>
                              <div className="text-primary fw-bold fs-5">{product.price}pts</div>
                            </div>
                            <Link 
                              to={`/chat/${product.seller.id}`} 
                              className="btn btn-primary d-flex align-items-center justify-content-center gap-2 w-100"
                            >
                              Enviar mensaje
                              <span>💬</span>
                            </Link>
                          </div>
                        </div>
                      </div>
                    </div>
                  ))}
                </div>
              </div>
            </div>
          </div>
        </div>
      )}
    </div>
  );
};

export default ProductSearch; 