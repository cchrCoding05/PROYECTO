import React from 'react';

const Footer = () => {
  return (
    <footer className="py-4 mt-5" style={{ 
      backgroundColor: 'var(--bs-tertiary-bg)',
      borderTop: '1px solid var(--bs-border-color)'
    }}>
      <div className="container text-center">
        <p style={{ color: 'var(--bs-body-color)' }}>
          LaMacroEmpresa &copy; | Intermediaria de Compras | Todos los derechos reservados.
        </p>
        <div className="social-icons">
          <a href="https://www.facebook.com" target="_blank" rel="noopener noreferrer" className="me-3" style={{ color: 'var(--bs-body-color)' }}>
            <i className="bi bi-facebook" style={{ fontSize: '2rem' }}></i>
          </a>
          <a href="https://www.instagram.com" target="_blank" rel="noopener noreferrer" className="me-3" style={{ color: 'var(--bs-body-color)' }}>
            <i className="bi bi-instagram" style={{ fontSize: '2rem' }}></i>
          </a>
          <a href="https://twitter.com" target="_blank" rel="noopener noreferrer" className="me-3" style={{ color: 'var(--bs-body-color)' }}>
            <i className="bi bi-twitter" style={{ fontSize: '2rem' }}></i>
          </a>
          <a href="https://www.linkedin.com" target="_blank" rel="noopener noreferrer" className="me-3" style={{ color: 'var(--bs-body-color)' }}>
            <i className="bi bi-linkedin" style={{ fontSize: '2rem' }}></i>
          </a>
        </div>
      </div>
    </footer>
  );
};

export default Footer;