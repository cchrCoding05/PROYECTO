import React, { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import { productService } from '../../services/api.jsx';
import './Search.css';

const ProductSearch = () => {
  const [searchQuery, setSearchQuery] = useState('');
  const [products, setProducts] = useState([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);

  useEffect(() => {
    // Cargar productos al iniciar
    searchProducts();
  }, []);

  const searchProducts = async (query = '') => {
    try {
      setLoading(true);
      setError(null);
      
      const results = await productService.search(query);
      setProducts(results);
    } catch (err) {
      setError('Error al buscar objetos');
      console.error(err);
    } finally {
      setLoading(false);
    }
  };

  const handleSearch = (e) => {
    e.preventDefault();
    searchProducts(searchQuery);
  };

  const handleInputChange = (e) => {
    setSearchQuery(e.target.value);
  };

  return (
    <div className="search-container">
      <h2 className="search-title">OBJETOS</h2>
      
      <form onSubmit={handleSearch} className="search-form">
        <div className="search-input-container">
          <input
            type="text"
            value={searchQuery}
            onChange={handleInputChange}
            placeholder="Bicicleta"
            className="search-input"
          />
          <button type="submit" className="search-button">
            <i className="search-icon">🔍</i>
          </button>
        </div>
      </form>

      {loading && <div className="search-loading">Cargando...</div>}
      
      {error && <div className="search-error">{error}</div>}

      <div className="results-container">
        <div className="products-layout">
          <div className="sellers-column">
            <h3 className="column-title">Vendedor</h3>
            
            {products.length > 0 ? (
              <div className="sellers-list">
                {products.map((product) => (
                  <div key={product.id} className="seller-card">
                    <div className="seller-name">{product.seller.name}</div>
                    <div className="seller-sales">{product.seller.sales} ventas.</div>
                  </div>
                ))}
              </div>
            ) : (
              <div className="no-results">No se encontraron vendedores</div>
            )}
          </div>
          
          <div className="products-column">
            <h3 className="column-title">Precio</h3>
            
            {products.length > 0 ? (
              <div className="products-list">
                {products.map((product) => (
                  <div key={product.id} className="product-card">
                    <div className="product-image-container">
                      <img 
                        src={product.imageUrl} 
                        alt={product.name} 
                        className="product-image" 
                      />
                    </div>
                    
                    <div className="product-details">
                      <div className="product-credits-icon">💰</div>
                      <div className="product-price">{product.price}pts</div>
                    </div>
                    
                    <Link to={`/chat/${product.seller.id}`} className="message-button">
                      Enviar mensaje
                      <span className="message-icon">💬</span>
                    </Link>
                  </div>
                ))}
              </div>
            ) : (
              <div className="no-results">No se encontraron objetos</div>
            )}
          </div>
        </div>
      </div>
    </div>
  );
};

export default ProductSearch; 