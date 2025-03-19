import React from 'react';

const Footer = () => {
  return (
    <footer className="bg-light py-4 mt-5">
      <div className="container text-center">
        <p>
          LaMacroEmpresa &copy; | Intermediaria de Compras | Todos los derechos reservados.
        </p>
        <div className="social-icons">
          <a href="https://www.facebook.com" target="_blank" rel="noopener noreferrer" className="me-3">
            <i className="bi bi-facebook" style={{ fontSize: '2rem' }}></i>
          </a>
          <a href="https://www.instagram.com" target="_blank" rel="noopener noreferrer" className="me-3">
            <i className="bi bi-instagram" style={{ fontSize: '2rem' }}></i>
          </a>
          <a href="https://twitter.com" target="_blank" rel="noopener noreferrer" className="me-3">
            <i className="bi bi-twitter" style={{ fontSize: '2rem' }}></i>
          </a>
          <a href="https://www.linkedin.com" target="_blank" rel="noopener noreferrer" className="me-3">
            <i className="bi bi-linkedin" style={{ fontSize: '2rem' }}></i>
          </a>
        </div>
      </div>
    </footer>
  );
};

export default Footer;