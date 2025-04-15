import React, { useState, useEffect } from 'react';
import { professionalService } from '../../services/api.jsx';

const ProfessionalSearch = () => {
  const [searchQuery, setSearchQuery] = useState('');
  const [professionals, setProfessionals] = useState([]);
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
    // Cargar profesionales al iniciar
    searchProfessionals();
  }, []);

  const searchProfessionals = async (query = '') => {
    try {
      setLoading(true);
      setError(null);
      setNoResults(false);
      
      // Normalizar la consulta para eliminar tildes y acentos
      const normalizedQuery = normalizeText(query);
      
      // Enviar la consulta original al servidor
      console.log('Buscando profesionales con query:', query);
      const results = await professionalService.search(query);
      console.log('Resultados de búsqueda:', results);

      // Validar que results es un array
      const professionalsArray = Array.isArray(results) ? results : [];

      // Validar y preparar los datos de profesionales
      const validatedProfessionals = professionalsArray.map(professional => ({
        ...professional,
        name: professional.name || 'Profesional sin nombre',
        profession: professional.profession || 'Profesión no especificada',
        description: professional.description || 'Sin descripción',
        rating: professional.rating || 0,
        ratingCount: professional.ratingCount || 0,
        // Pre-normalizar los campos de búsqueda para facilitar la comparación
        _normalizedName: normalizeText(professional.name || ''),
        _normalizedProfession: normalizeText(professional.profession || ''),
        _normalizedDescription: normalizeText(professional.description || '')
      }));

      // Si hay un término de búsqueda, hacemos un filtrado adicional
      if (normalizedQuery) {
        // Filtramos los resultados en el cliente para mejorar la búsqueda
        const filteredResults = validatedProfessionals.filter(professional => 
          professional._normalizedName.includes(normalizedQuery) || 
          professional._normalizedProfession.includes(normalizedQuery) ||
          professional._normalizedDescription.includes(normalizedQuery)
        );
        
        console.log('Resultados después de filtrar tildes:', filteredResults.length);
        setProfessionals(filteredResults);
        
        // Si no hay resultados después del filtrado, mostrar un mensaje
        if (filteredResults.length === 0) {
          setNoResults(true);
        }
      } else {
        setProfessionals(validatedProfessionals);
        
        // Si no hay resultados, mostrar un mensaje
        if (validatedProfessionals.length === 0) {
          setNoResults(true);
        }
      }
    } catch (err) {
      setError('Error al buscar profesionales: ' + (err.message || 'Error desconocido'));
      console.error('Error en la búsqueda:', err);
      setProfessionals([]);
    } finally {
      setLoading(false);
    }
  };

  const handleSearch = (e) => {
    e.preventDefault();
    searchProfessionals(searchQuery);
  };

  const handleInputChange = (e) => {
    const query = e.target.value;
    setSearchQuery(query);
    
    // Si el campo de búsqueda está vacío, mostrar todos los profesionales
    if (!query.trim()) {
      searchProfessionals('');
    }
  };

  // Renderiza un mensaje cuando no hay resultados
  const renderNoResults = () => (
    <div className="text-center bg-light bg-opacity-25 p-4 rounded my-4">
      <div className="display-4 text-muted mb-3"></div>
      <h3 className="fw-bold text-secondary">No se encontraron profesionales</h3>
      {searchQuery ? (
        <p className="text-muted">No hay resultados para "<strong>{searchQuery}</strong>". Intenta con otra búsqueda.</p>
      ) : (
        <p className="text-muted">No hay profesionales disponibles en este momento.</p>
      )}
    </div>
  );

  return (
    <div className="container py-4">
      <h2 className="text-center display-5 mb-4">PROFESIONALES</h2>
      
      <form onSubmit={handleSearch} className="mb-5">
        <div className="input-group shadow-sm mx-auto" style={{ maxWidth: '600px' }}>
          <input
            type="text"
            value={searchQuery}
            onChange={handleInputChange}
            placeholder="Buscar por profesión o nombre..."
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

      <div className="mt-4">
        <h3 className="text-center mb-4">
          {searchQuery ? `Resultados para "${searchQuery}"` : 'Todos los profesionales'}
        </h3>
        
        {!loading && !error && !noResults && professionals.length > 0 ? (
          <div className="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            {professionals.map((professional) => (
              <div key={professional.id} className="col">
                <div className="card h-100 border-0 shadow-sm transition">
                  <div className="card-body text-center p-4">
                    <div className="mb-3">
                      {professional.avatarUrl ? (
                        <img 
                          src={professional.avatarUrl} 
                          alt={professional.name} 
                          className="rounded-circle mx-auto border"
                          style={{ 
                            width: '80px', 
                            height: '80px', 
                            objectFit: 'cover',
                            borderColor: 'var(--bs-border-color)'
                          }}
                          onError={(e) => {
                            e.target.onerror = null;
                            e.target.src = `https://via.placeholder.com/80/e6f3ff/007bff?text=${professional.name.charAt(0).toUpperCase()}`;
                          }} 
                        />
                      ) : (
                        <div 
                          className="rounded-circle d-flex align-items-center justify-content-center mx-auto bg-primary bg-opacity-10 text-primary fw-bold border"
                          style={{ 
                            width: '80px', 
                            height: '80px',
                            borderColor: 'var(--bs-border-color)'
                          }}
                        >
                          {professional.name.charAt(0).toUpperCase()}
                        </div>
                      )}
                    </div>
                    
                    <h4 className="card-title mb-1 fw-bold">{professional.name}</h4>
                    <div className="text-primary fw-medium mb-2">{professional.profession}</div>
                    <p className="card-text text-body-secondary small mb-3" style={{ 
                      display: '-webkit-box', 
                      WebkitBoxOrient: 'vertical', 
                      WebkitLineClamp: 3, 
                      overflow: 'hidden'
                    }}>
                      {professional.description}
                    </p>
                    
                    <div className="d-flex flex-column align-items-center mb-3">
                      <div className="mb-1">
                        {Array.from({ length: 5 }).map((_, index) => (
                          <span 
                            key={index}
                            className={index < professional.rating ? "text-warning" : "text-body-tertiary"}
                            style={{ fontSize: '1.1rem' }}
                          >
                            ★
                          </span>
                        ))}
                      </div>
                      <div className="text-body-secondary small">{professional.ratingCount} valoraciones</div>
                    </div>
                    
                    <div className="d-flex justify-content-center gap-3">
                      <button className="btn btn-primary">Contactar</button>
                      <button className="btn btn-outline-primary rounded-circle d-flex align-items-center justify-content-center" style={{ width: '40px', height: '40px', padding: 0 }}>
                        💬
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            ))}
          </div>
        ) : null}
      </div>
    </div>
  );
};

export default ProfessionalSearch;