import { useState, useEffect } from 'react';
import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom';
import { AuthProvider, useAuth } from './hooks/useAuth';
import './App.css';

// Componentes de autenticación
import Login from './components/auth/Login';
import Register from './components/auth/Register';

// Componentes de perfil
import ProfileForm from './components/profile/ProfileForm';

// Componentes de búsqueda
import ProfessionalSearch from './components/search/ProfessionalSearch';
import ProductSearch from './components/search/ProductSearch';

// Componentes de negociación
import ProductNegotiation from './components/negotiation/ProductNegotiation';

// Componente protegido para rutas que requieren autenticación
const ProtectedRoute = ({ children }) => {
  const { isAuthenticated, loading } = useAuth();
  
  if (loading) {
    return <div className="loading-container">Cargando...</div>;
  }
  
  if (!isAuthenticated) {
    return <Navigate to="/login" />;
  }
  
  return children;
};

// Componente principal de la navegación
const AppNavigation = () => {
  const { currentUser, logout } = useAuth();

  const handleLogout = async () => {
    await logout();
  };

  return (
    <BrowserRouter>
      <div className="app-container">
        <header className="app-header">
          <div className="header-logo">
            <h1>HelpEx</h1>
          </div>
          <nav className="header-nav">
            {currentUser ? (
              <>
                <a href="/buscar/profesionales">Profesionales</a>
                <a href="/buscar/objetos">Objetos</a>
                <a href="/perfil">Perfil</a>
                <button onClick={handleLogout} className="logout-btn">Cerrar sesión</button>
                <div className="user-credits">{currentUser.credits || 0} pts</div>
              </>
            ) : (
              <>
                <a href="/login">Iniciar sesión</a>
                <a href="/registro">Registrarse</a>
              </>
            )}
          </nav>
        </header>

        <main className="app-content">
          <Routes>
            <Route path="/" element={<Navigate to="/buscar/profesionales" />} />
            <Route path="/login" element={<Login />} />
            <Route path="/registro" element={<Register />} />
            
            <Route 
              path="/perfil" 
              element={
                <ProtectedRoute>
                  <ProfileForm />
                </ProtectedRoute>
              } 
            />
            
            <Route 
              path="/buscar/profesionales" 
              element={<ProfessionalSearch />} 
            />
            
            <Route 
              path="/buscar/objetos" 
              element={<ProductSearch />} 
            />
            
            <Route 
              path="/productos/:productId/negociar" 
              element={
                <ProtectedRoute>
                  <ProductNegotiation />
                </ProtectedRoute>
              } 
            />
          </Routes>
        </main>

        <footer className="app-footer">
          <p>&copy; 2023 HelpEx - Plataforma de Intercambio de Servicios sin Dinero</p>
        </footer>
      </div>
    </BrowserRouter>
  );
};

function App() {
  return (
    <AuthProvider>
      <AppNavigation />
    </AuthProvider>
  );
}

export default App;
