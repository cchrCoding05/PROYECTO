// components/home/HomePage.jsx
import React, { useState, useEffect } from "react";
import { professionalService, productService } from "../../services/api";
import { Link } from "react-router-dom";
import { Carousel, Card, Row, Col } from 'react-bootstrap';
import 'bootstrap/dist/css/bootstrap.min.css';

const Home = () => {
  const [topUsers, setTopUsers] = useState([]);
  const [topProducts, setTopProducts] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    const fetchData = async () => {
      try {
        setLoading(true);
        setError(null);
        
        // Realizar las peticiones sin token
        const [usersResponse, productsResponse] = await Promise.all([
          professionalService.getTopRated(),
          productService.getFromTopRatedUsers()
        ]);
        
        console.log('Respuesta de usuarios:', usersResponse);
        console.log('Respuesta de productos:', productsResponse);
        
        if (usersResponse?.data) {
          setTopUsers(usersResponse.data);
        }
        
        if (productsResponse?.data) {
          setTopProducts(productsResponse.data);
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
  }, []);

  // Función para dividir el array en grupos de 3
  const chunkArray = (array, size) => {
    if (!array || array.length === 0) return [];
    const chunked = [];
    for (let i = 0; i < array.length; i += size) {
      chunked.push(array.slice(i, i + size));
    }
    return chunked;
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
                  {chunk.map((user) => (
                    <Col key={user.id} xs={12} md={4} className="mb-3">
                      <Card className="h-100">
                        <Card.Img 
                          variant="top" 
                          src={user.profilePhoto || '/default-profile.png'} 
                          alt={user.username}
                          style={{ height: '200px', objectFit: 'cover' }}
                          onError={(e) => {
                            e.target.onerror = null;
                            e.target.src = '/default-profile.png';
                          }}
                        />
                        <Card.Body className="text-center">
                          <Card.Title>{user.username}</Card.Title>
                          <Card.Text>{user.profession}</Card.Text>
                          <div className="d-flex justify-content-center align-items-center mb-2">
                            <i className="bi bi-star-fill text-warning me-1"></i>
                            <span>{user.rating ? user.rating.toFixed(1) : '0.0'}</span>
                          </div>
                          <Link to={`/search/professionals/${user.id}`} className="btn btn-primary">
                            Ver Perfil
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
                            <small>Vendedor: {product.user?.username || 'Anónimo'} ⭐ {product.user?.rating ? product.user.rating.toFixed(1) : '0.0'}</small>
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
