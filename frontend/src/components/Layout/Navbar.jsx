import React from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { useAuth } from '../../hooks/useAuth';
import ThemeToggle from '../UI/ThemeToggle';

const Navbar = () => {
  const { isAuthenticated, currentUser, logout } = useAuth();
  const navigate = useNavigate();

  const handleLogout = async () => {
    try {
      const result = await logout();
      if (result.success) {
        console.log('Cierre de sesión exitoso');
        navigate('/');
      } else {
        console.error('Error al cerrar sesión:', result.message);
      }
    } catch (error) {
      console.error('Error inesperado al cerrar sesión:', error);
    }
  };

  return (
    <nav className="navbar navbar-expand-lg mb-4 border-bottom">
      <div className="container">
        <Link className="navbar-brand fw-bold" to="/">
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
              <Link className="nav-link" to="/">Inicio</Link>
            </li>
            <li className="nav-item">
              <Link className="nav-link" to="/search/products">Buscar Objetos</Link>
            </li>
            <li className="nav-item">
              <Link className="nav-link" to="/search/professionals">Buscar Profesionales</Link>
            </li>
            {isAuthenticated && currentUser?.username === 'admin' && (
              <li className="nav-item">
                <Link className="nav-link" to="/admin/users">Gestión de Usuarios</Link>
              </li>
            )}
          </ul>
          
          {/* Toggle de tema solo visible en escritorio */}
          <div className="d-none d-lg-flex align-items-center pe-3 border-end me-3">
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
                <div className="text-nowrap d-none d-md-block">
                  Hola, {currentUser?.username || currentUser?.userName || 'Usuario'}
                </div>
                
                {currentUser?.credits !== undefined && (
                  <div className="badge bg-warning text-dark d-flex align-items-center p-2">
                    <span className="me-1">💰</span>
                    <span>{currentUser.credits} créditos</span>
                  </div>
                )}
                
                <div className="dropdown">
                  <button 
                    className="btn btn-outline-secondary dropdown-toggle" 
                    type="button" 
                    id="userMenuDropdown" 
                    data-bs-toggle="dropdown" 
                    aria-expanded="false"
                  >
                    Mi cuenta
                  </button>
                  <ul className="dropdown-menu dropdown-menu-end" aria-labelledby="userMenuDropdown">
                    <li>
                      <Link className="dropdown-item" to="/profile">
                        Mi perfil
                      </Link>
                    </li>
                    <li>
                      <Link className="dropdown-item" to="/my-products">
                        Mis productos
                      </Link>
                    </li>
                    <li><hr className="dropdown-divider" /></li>
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