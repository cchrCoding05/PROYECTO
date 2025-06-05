import React from 'react';
import { Link } from 'react-router-dom';
import { Container } from 'react-bootstrap';
import './NotFound.css';

const NotFound = () => {
    return (
        <Container fluid className="not-found-container">
            <div className="error-container">
                <div className="helpex-logo">
                    <i className="bi bi-heart-pulse-fill"></i> Helpex
                </div>
                <h1 className="error-code floating">404</h1>
                <p className="error-message">¡Ups! Página no encontrada</p>
                <p className="error-description">
                    Lo sentimos, la página que buscas no existe o ha sido movida.
                    ¿Por qué no vuelves a la página principal y exploras nuestros servicios?
                </p>
                <Link to="/" className="home-button">
                    <i className="bi bi-house-fill me-2"></i>Volver al inicio
                </Link>
            </div>
        </Container>
    );
};

export default NotFound; 