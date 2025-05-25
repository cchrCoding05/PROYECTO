// components/home/HomePage.jsx
import React, { useState, useEffect } from "react";
import { productService } from '../../services/productService';
import { professionalService } from '../../services/professionalService';
import { useAuth } from "../../hooks/useAuth";
import { Link } from "react-router-dom";
import { Carousel, Card, Row, Col } from 'react-bootstrap';
import 'bootstrap/dist/css/bootstrap.min.css';
import './Home.css';

const Home = () => {
  const { user: currentUser } = useAuth();
  const [topUsers, setTopUsers] = useState([]);
  const [topProducts, setTopProducts] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    const fetchData = async () => {
      try {
        setLoading(true);
        setError(null);
        
        // Obtener usuarios mejor valorados
        const usersResponse = await professionalService.getTopRated();
        
        // Obtener productos de los usuarios mejor valorados
        const productsResponse = await productService.getFromTopRatedUsers();
        
        if (usersResponse?.data) {
          // Filtrar el usuario actual y el usuario admin
          const filteredUsers = usersResponse.data.filter(prof => 
            prof.id !== currentUser?.id && 
            prof.username?.toLowerCase() !== 'admin'
          );
          
          // Procesar los datos para que coincidan con la estructura de ProfessionalSearch
          const processedProfessionals = filteredUsers.map(prof => ({
            id: prof.id,
            name: prof.name,
            profession: prof.profession,
            description: prof.description,
            foto_perfil: prof.photo,
            photo: prof.photo,
            rating: parseFloat(prof.rating) || 0,
            reviews_count: parseInt(prof.reviews_count) || 0
          }));

          // Ordenar usuarios por rating y limitar a 9
          const sortedUsers = processedProfessionals
            .sort((a, b) => b.rating - a.rating)
            .slice(0, 9);
          setTopUsers(sortedUsers);
        }
        
        if (productsResponse?.data) {
          // Filtrar productos del usuario actual y procesar las valoraciones
          const filteredProducts = productsResponse.data
            .filter(product => product.user?.id !== currentUser?.id)
            .map(product => ({
              ...product,
              user: {
                id: product.user?.id,
                name: product.user?.name || product.user?.username || 'Anónimo'
              }
            }))
            .slice(0, 9); // Limitar a 9 productos
          setTopProducts(filteredProducts);
        }
      } catch (err) {
        console.error('Error al cargar datos:', err);
        setError('No se pudieron cargar algunos datos. Por favor, intenta más tarde.');
        setTopUsers([]);
        setTopProducts([]);
      } finally {
        setLoading(false);
      }
    };

    fetchData();
  }, [currentUser]);

  // Función para dividir el array en grupos de 3
  const chunkArray = (array, size) => {
    if (!array || array.length === 0) return [];
    const chunked = [];
    for (let i = 0; i < array.length; i += size) {
      chunked.push(array.slice(i, i + size));
    }
    return chunked;
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

  if (loading) {
    return (
      <div className="text-center py-5">
        <div className="spinner-border text-primary" role="status">
          <span className="visually-hidden">Cargando...</span>
        </div>
        <p className="mt-2">Cargando contenido...</p>
      </div>
    );
  }

  // Dividir los arrays en grupos de 3
  const userChunks = chunkArray(topUsers, 3);
  const productChunks = chunkArray(topProducts, 3);

  return (
    <div className="container py-4">
      <header className="text-center mb-5">
        <h1 className="display-4">Encuentra lo que necesitas</h1>
        <p className="lead">Descubre productos y profesionales destacados</p>
      </header>

      {error && (
        <div className="alert alert-warning text-center mb-4" role="alert">
          {error}
        </div>
      )}

      {/* Top Rated Users Carousel */}
      {userChunks.length > 0 && (
        <section className="mb-5">
          <h2 className="mb-4">Profesionales Mejor Valorados</h2>
          <Carousel interval={5000} wrap={true}>
            {userChunks.map((chunk, index) => (
              <Carousel.Item key={index}>
                <Row className="justify-content-center">
                  {chunk.map((professional) => (
                    <Col key={professional.id} xs={12} md={4} className="mb-3">
                      <Card className="h-100">
                        <Card.Img 
                          variant="top" 
                          src={professional.photo || '/default-profile.png'} 
                          alt={professional.name}
                          style={{ height: '200px', objectFit: 'cover' }}
                          onError={(e) => {
                            e.target.onerror = null;
                            e.target.src = '/default-profile.png';
                          }}
                        />
                        <Card.Body className="text-center">
                          <Card.Title>{professional.name}</Card.Title>
                          <Card.Text>{professional.profession}</Card.Text>
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
                          <Link 
                        to={`/negotiate/professional/${professional.id}`}
                        className="btn btn-primary"
                      >
                        Contactar
                      </Link>
                        </Card.Body>
                      </Card>
                    </Col>
                  ))}
                </Row>
              </Carousel.Item>
            ))}
          </Carousel>
        </section>
      )}

      {/* Top Products Carousel */}
      {productChunks.length > 0 && (
        <section className="mb-5">
          <h2 className="mb-4">Productos Destacados</h2>
          <Carousel interval={5000} wrap={true}>
            {productChunks.map((chunk, index) => (
              <Carousel.Item key={index}>
                <Row className="justify-content-center">
                  {chunk.map((product) => (
                    <Col key={product.id} xs={12} md={4} className="mb-3">
                      <Card className="h-100">
                        <Card.Img 
                          variant="top" 
                          src={product.image || '/default-product.png'} 
                          alt={product.name}
                          style={{ height: '200px', objectFit: 'cover' }}
                          onError={(e) => {
                            e.target.onerror = null;
                            e.target.src = '/default-product.png';
                          }}
                        />
                        <Card.Body>
                          <Card.Title>{product.name}</Card.Title>
                          <Card.Text>{product.description}</Card.Text>
                          <div className="d-flex justify-content-between align-items-center">
                            <span className="h5 mb-0">{product.price} créditos</span>
                            <Link to={`/product/${product.id}`} className="btn btn-primary">
                              Ver Detalles
                            </Link>
                          </div>
                          <div className="mt-2 text-muted">
                            <small>
                              Vendedor: {product.user?.name}
                            </small>
                          </div>
                        </Card.Body>
                      </Card>
                    </Col>
                  ))}
                </Row>
              </Carousel.Item>
            ))}
          </Carousel>
        </section>
      )}

      {!error && userChunks.length === 0 && productChunks.length === 0 && (
        <div className="text-center py-5">
          <h3>No hay contenido disponible en este momento</h3>
          <p className="text-muted">Vuelve más tarde para ver los productos y profesionales destacados.</p>
        </div>
      )}
    </div>
  );
};

export default Home;
