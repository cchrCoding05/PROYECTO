import React from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { useAuth } from '../../hooks/useAuth';
import ThemeToggle from '../UI/ThemeToggle';

const Navbar = () => {
  const navigate = useNavigate();
  const { isAuthenticated, currentUser, logout } = useAuth();

  const handleLogout = async () => {
    try {
      await logout();
      navigate('/login');
    } catch (error) {
      console.error('Error durante el logout:', error);
    }
  };

  return (
    <nav className="navbar navbar-expand-lg mb-4" style={{ 
      borderBottom: '1px solid var(--bs-border-color)',
      backgroundColor: 'var(--surface-color)'
    }}>
      <div className="container">
        <Link className="navbar-brand fw-bold" to="/" style={{ color: 'var(--bs-body-color)' }}>
          LaMacroEmpresa
        </Link>
        <button 
          className="navbar-toggler" 
          type="button" 
          data-bs-toggle="collapse" 
          data-bs-target="#navbarNav"
          aria-controls="navbarNav" 
          aria-expanded="false" 
          aria-label="Toggle navigation"
          style={{ borderColor: 'var(--bs-border-color)' }}
        >
          <span className="navbar-toggler-icon"></span>
        </button>
        
        {/* Toggle de tema visible en versión móvil */}
        <div className="d-lg-none ms-auto me-2">
          <ThemeToggle />
        </div>
        
        <div className="collapse navbar-collapse" id="navbarNav">
          <ul className="navbar-nav me-auto mb-2 mb-lg-0">
            <li className="nav-item">
              <Link className="nav-link" to="/" style={{ color: 'var(--bs-body-color)' }}>Inicio</Link>
            </li>
            <li className="nav-item">
              <Link className="nav-link" to="/search/products" style={{ color: 'var(--bs-body-color)' }}>Buscar Objetos</Link>
            </li>
            <li className="nav-item">
              <Link className="nav-link" to="/search/professionals" style={{ color: 'var(--bs-body-color)' }}>Buscar Profesionales</Link>
            </li>
            {isAuthenticated && (
              <li className="nav-item">
                <Link className="nav-link" to="/upload-product" style={{ color: 'var(--bs-body-color)' }}>Subir Producto</Link>
              </li>
            )}

            {isAuthenticated && currentUser?.data?.username === 'ADMIN' && (
              <li className="nav-item">
                <Link className="nav-link" to="/admin" style={{ color: 'var(--bs-body-color)' }}>Gestión</Link>
              </li>
            )}
          </ul>
          
          {/* Toggle de tema solo visible en escritorio */}
          <div className="d-none d-lg-flex align-items-center pe-3 border-end me-3" style={{ borderColor: 'var(--bs-border-color)' }}>
            <ThemeToggle />
          </div>
          
          <div className="d-flex align-items-center">
            {!isAuthenticated ? (
              <div className="d-flex gap-2">
                <Link className="btn btn-outline-primary" to="/login">
                  Iniciar Sesión
                </Link>
                <Link className="btn btn-primary" to="/register">
                  Registrarse
                </Link>
              </div>
            ) : (
              <div className="d-flex align-items-center gap-3">
                <div className="text-nowrap d-none d-md-block" style={{ color: 'var(--bs-body-color)' }}>
                  ¡Bienvenido, {currentUser?.data?.username || 'Usuario'}! 👋
                  {currentUser?.data?.credits !== undefined && (
                    <span className="ms-2 badge bg-warning text-dark">
                      💰 {currentUser.data.credits} créditos
                    </span>
                  )}
                </div>
                
                <div className="dropdown">
                  <button 
                    className="btn btn-outline-secondary dropdown-toggle" 
                    type="button" 
                    id="userMenuDropdown" 
                    data-bs-toggle="dropdown" 
                    aria-expanded="false"
                    style={{ borderColor: 'var(--bs-border-color)', color: 'var(--bs-body-color)' }}
                  >
                    Mi cuenta
                  </button>
                  <ul className="dropdown-menu dropdown-menu-end" aria-labelledby="userMenuDropdown" style={{ 
                    backgroundColor: 'var(--bs-body-bg)',
                    borderColor: 'var(--bs-border-color)'
                  }}>
                    <li>
                      <Link className="dropdown-item" to="/profile" style={{ color: 'var(--bs-body-color)' }}>
                        Mi perfil
                      </Link>
                    </li>
                    <li>
                      <Link className="dropdown-item" to="/profile?tab=products" style={{ color: 'var(--bs-body-color)' }}>
                        Mis productos
                      </Link>
                    </li>
                    <li>
                      <Link className="dropdown-item" to="/profile?tab=negotiations" style={{ color: 'var(--bs-body-color)' }}>
                        Mis negociaciones
                      </Link>
                    </li>
                    <li><hr className="dropdown-divider" style={{ borderColor: 'var(--bs-border-color)' }} /></li>
                    <li>
                      <button className="dropdown-item text-danger" onClick={handleLogout}>
                        Cerrar sesión
                      </button>
                    </li>
                  </ul>
                </div>
              </div>
            )}
          </div>
        </div>
      </div>
    </nav>
  );
};

export default Navbar;