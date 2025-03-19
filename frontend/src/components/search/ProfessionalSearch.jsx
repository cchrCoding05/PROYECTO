import React, { useState, useEffect } from 'react';
import { professionalService } from '../../services/api.jsx';
import './Search.css';

const ProfessionalSearch = () => {
  const [searchQuery, setSearchQuery] = useState('');
  const [professionals, setProfessionals] = useState([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);

  useEffect(() => {
    // Cargar profesionales al iniciar
    searchProfessionals();
  }, []);

  const searchProfessionals = async (query = '') => {
    try {
      setLoading(true);
      setError(null);
      
      const results = await professionalService.search(query);
      setProfessionals(results);
    } catch (err) {
      setError('Error al buscar profesionales');
      console.error(err);
    } finally {
      setLoading(false);
    }
  };

  const handleSearch = (e) => {
    e.preventDefault();
    searchProfessionals(searchQuery);
  };

  const handleInputChange = (e) => {
    setSearchQuery(e.target.value);
  };

  return (
    <div className="search-container">
      <h2 className="search-title">PROFESIONALES</h2>
      
      <form onSubmit={handleSearch} className="search-form">
        <div className="search-input-container">
          <input
            type="text"
            value={searchQuery}
            onChange={handleInputChange}
            placeholder="Fontanero"
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
        <h3 className="results-title">Resultados</h3>
        
        {professionals.length > 0 ? (
          <div className="professionals-grid">
            {professionals.map((professional) => (
              <div key={professional.id} className="professional-card">
                <div className="professional-info">
                  <h4 className="professional-name">{professional.name}</h4>
                  <div className="professional-chat-icon">💬</div>
                </div>
                
                <div className="professional-rating">
                  <div className="rating-stars">
                    {Array.from({ length: 5 }).map((_, index) => (
                      <span 
                        key={index}
                        className={index < professional.rating ? "star-filled" : "star-empty"}
                      >
                        ★
                      </span>
                    ))}
                  </div>
                  <div className="rating-count">{professional.ratingCount} val.</div>
                </div>
              </div>
            ))}
          </div>
        ) : (
          <div className="no-results">No se encontraron profesionales</div>
        )}
      </div>
    </div>
  );
};

export default ProfessionalSearch; 