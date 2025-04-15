import { useState, useEffect } from 'react';
import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom';
import { AuthProvider, useAuth } from './hooks/useAuth';
import './App.css';

// Componentes de layout
import Navbar from './components/Layout/Navbar';

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
    return (
      <div className="d-flex justify-content-center align-items-center" style={{ minHeight: '200px' }}>
        <div className="spinner-border text-primary" role="status">
          <span className="visually-hidden">Cargando...</span>
        </div>
      </div>
    );
  }
  
  if (!isAuthenticated) {
    return <Navigate to="/login" />;
  }
  
  return children;
};

// Componente principal de la navegación
const AppNavigation = () => {
  return (
    <BrowserRouter>
      <div className="d-flex flex-column min-vh-100">
        <Navbar />

        <main className="container flex-grow-1 py-3">
          <Routes>
            <Route path="/" element={<Navigate to="/search/professionals" />} />
            <Route path="/login" element={<Login />} />
            <Route path="/register" element={<Register />} />
            
            <Route 
              path="/profile" 
              element={
                <ProtectedRoute>
                  <ProfileForm />
                </ProtectedRoute>
              } 
            />
            
            <Route 
              path="/search/professionals" 
              element={<ProfessionalSearch />} 
            />
            
            <Route 
              path="/search/products" 
              element={<ProductSearch />} 
            />
            
            <Route 
              path="/products/:productId/negotiate" 
              element={
                <ProtectedRoute>
                  <ProductNegotiation />
                </ProtectedRoute>
              } 
            />
          </Routes>
        </main>

        <footer className="py-3 mt-auto bg-body-tertiary border-top">
          <div className="container text-center">
            <p className="mb-0 text-body-secondary">
              &copy; 2023 HelpEx - Plataforma de Intercambio de Servicios sin Dinero
            </p>
          </div>
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
