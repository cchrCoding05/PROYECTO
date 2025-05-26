import React, { useState, useEffect } from 'react';
import { professionalService } from "../../services/professionalService";
import { useAuth } from '../../hooks/useAuth';
import AlertMessage from '../Layout/AlertMessage.jsx';
import { Cloudinary } from '@cloudinary/url-gen';
import { AdvancedImage } from '@cloudinary/react';
import { Link, useNavigate } from 'react-router-dom';
import './ProfessionalSearch.css';

const ProfessionalSearch = () => {
  const { user, isAuthenticated } = useAuth();
  const navigate = useNavigate();
  const [searchQuery, setSearchQuery] = useState('');
  const [professionals, setProfessionals] = useState([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);
  const [noResults, setNoResults] = useState(false);
  const [currentPage, setCurrentPage] = useState(1);
  const professionalsPerPage = 15;

  // Inicializar Cloudinary
  const cld = new Cloudinary({
    cloud: {
      cloudName: import.meta.env.VITE_CLOUDINARY_CLOUD_NAME
    }
  });

  useEffect(() => {
    if (!isAuthenticated) {
      navigate('/login');
    }
  }, [isAuthenticated, navigate]);

  // Función para normalizar texto (quitar tildes y convertir a minúsculas)
  const normalizeText = (text) => {
    if (!text) return '';
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
      
      console.log('Buscando profesionales con query:', query);
      const results = await professionalService.search(query);
      console.log('Resultados de búsqueda completos:', results);

      if (results && results.success === false) {
        setError(results.message || 'Error en la búsqueda de profesionales');
        setProfessionals([]);
        setNoResults(true);
        return;
      }

      // Validar que results.data es un array
      const professionalsArray = Array.isArray(results.data) ? results.data : [];
      console.log('Número total de profesionales antes de filtrar:', professionalsArray.length);
      
      // Filtrar el usuario actual solo si está autenticado
      const filteredProfessionals = user ? professionalsArray.filter(prof => prof.id !== user.id) : professionalsArray;
      console.log('Número de profesionales después de filtrar usuario actual:', filteredProfessionals.length);
      
      // Procesar las valoraciones y normalizar los datos
      const processedProfessionals = filteredProfessionals.map(prof => {
        // Asegurarse de que rating y reviews_count sean números
        const rating = parseFloat(prof.rating) || 0;
        const reviewsCount = parseInt(prof.reviews_count) || 0;
        
        // Normalizar los textos
        const normalizedName = prof.name ? prof.name.trim() : '';
        const normalizedProfession = prof.profession ? prof.profession.trim() : '';
        const normalizedDescription = prof.description ? prof.description.trim() : '';
        
        return {
          ...prof,
          name: normalizedName,
          profession: normalizedProfession,
          description: normalizedDescription,
          rating,
          reviews_count: reviewsCount
        };
      });

      console.log('Profesionales procesados:', processedProfessionals);

      // Si hay una consulta de búsqueda, filtrar los resultados
      if (query.trim()) {
        const normalizedQuery = normalizeText(query);
        const filteredResults = processedProfessionals.filter(prof => {
          const normalizedName = normalizeText(prof.name);
          const normalizedProfession = normalizeText(prof.profession);
          const normalizedDescription = normalizeText(prof.description);
          
          return normalizedName.includes(normalizedQuery) ||
                 normalizedProfession.includes(normalizedQuery) ||
                 normalizedDescription.includes(normalizedQuery);
        });
        
        console.log('Resultados filtrados por búsqueda:', filteredResults.length);
        setProfessionals(filteredResults);
        setNoResults(filteredResults.length === 0);
      } else {
        console.log('Mostrando todos los profesionales:', processedProfessionals.length);
        setProfessionals(processedProfessionals);
        setNoResults(processedProfessionals.length === 0);
      }
    } catch (error) {
      console.error('Error en la búsqueda:', error);
      setError('Error al buscar profesionales. Por favor, inténtalo de nuevo.');
      setProfessionals([]);
      setNoResults(true);
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

  const renderStars = (rating) => {
    // Convertir el rating a número y redondear al decimal más cercano
    const numericRating = parseFloat(rating) || 0;
    
    return [1, 2, 3, 4, 5].map((star) => {
      // Calcular la diferencia entre el rating y la estrella actual
      const difference = numericRating - (star - 1);
      
      // Determinar el tipo de estrella a mostrar
      let starClass = 'star';
      if (difference >= 1) {
        starClass += ' filled'; // Estrella completa
      } else if (difference > 0) {
        starClass += ' half-filled'; // Media estrella
      }
      
      return (
        <span key={star} className={starClass}>
        ★
      </span>
      );
    });
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

  // Manejar el cierre del mensaje de error
  const handleCloseError = () => {
    setError(null);
  };

  // Calcular los profesionales a mostrar en la página actual
  const indexOfLast = currentPage * professionalsPerPage;
  const indexOfFirst = indexOfLast - professionalsPerPage;
  const currentProfessionals = professionals.slice(indexOfFirst, indexOfLast);
  const totalPages = Math.ceil(professionals.length / professionalsPerPage);

  const handlePageChange = (page) => {
    setCurrentPage(page);
  };

  return (
    <div className="container py-4">
      <h2 className="text-center display-5 mb-4">PROFESIONALES</h2>
      
      {error && (
        <AlertMessage
          message={error}
          type="danger"
          onClose={handleCloseError}
        />
      )}
      
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
            {loading ? 'Buscando...' : 'Buscar'}
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
      
      {noResults && !loading && !error && renderNoResults()}

      <div className="mt-4">
        <h3 className="text-center mb-4">
          {searchQuery ? `Resultados para "${searchQuery}"` : 'Todos los profesionales'}
        </h3>
        
        {!loading && !error && !noResults && professionals.length > 0 ? (
          <>
            <div className="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
              {currentProfessionals.map((professional) => (
                <div key={professional.id} className="col">
                  <div className="card h-100 border-0 shadow-sm transition">
                    <div className="card-body text-center p-4">
                      <div className="mb-3">
                        {professional.foto_perfil ? (
                          <img 
                            src={professional.foto_perfil}
                            alt={professional.name}
                            className="rounded-circle mx-auto border"
                            style={{ 
                              width: '80px', 
                              height: '80px', 
                              objectFit: 'cover',
                              borderColor: 'var(--bs-border-color)'
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
                          {renderStars(professional.rating)}
                        </div>
                        <small className="text-muted">
                          {professional.reviews_count} valoraciones
                          {professional.rating > 0 && (
                            <span className="ms-1">
                              ({professional.rating.toFixed(1)})
                            </span>
                          )}
                        </small>
                      </div>
                      
                      <div className="d-flex justify-content-center gap-3">
                      <Link 
                          to={`/negotiate/professional/${professional.id}`}
                          className="btn btn-primary"
                        >
                          Contactar
                        </Link>
                      </div>
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
          </>
        ) : null}
      </div>
    </div>
  );
};

export default ProfessionalSearch;