import { useState, useEffect } from 'react';
import { BrowserRouter, Routes, Route, useNavigate } from 'react-router-dom';
import { AuthProvider } from './hooks/useAuth';
import './App.css';

// Componentes de layout
import Navbar from './components/Layout/Navbar';
import Home from './components/Home/Home';

// Componentes de autenticación
import Login from './components/Auth/Login';
import Register from './components/Auth/Register';
import ProtectedRoute from './components/Auth/ProtectedRoute';

// Componentes de perfil
import ProfileForm from './components/Profile/ProfileForm';

// Componentes de búsqueda
import ProfessionalSearch from './components/Search/ProfessionalSearch';
import ProductSearch from './components/Search/ProductSearch';

// Componentes de negociación
import ProductNegotiation from './components/Negotiation/ProductNegotiation';

// Componentes de subida de productos
import ProductUpload from './components/Product/ProductUpload';
import ProductDetails from './components/Product/ProductDetails';

// Componente principal de la navegación
const AppNavigation = () => {
  const navigate = useNavigate();
  
  return (
    <div className="d-flex flex-column min-vh-100">
      <Navbar />

      <main className="container flex-grow-1 py-3">
        <Routes>
          <Route path="/" element={<Home />} />
          <Route path="/login" element={<Login />} />
          <Route path="/register" element={<Register />} />
          
          {/* Rutas protegidas */}
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
            element={
              <ProtectedRoute>
                <ProfessionalSearch />
              </ProtectedRoute>
            } 
          />
          
          <Route 
            path="/search/products" 
            element={
              <ProtectedRoute>
                <ProductSearch />
              </ProtectedRoute>
            } 
          />

          <Route 
            path="/products/:id" 
            element={
              <ProtectedRoute>
                <ProductDetails />
              </ProtectedRoute>
            } 
          />
          
          <Route 
            path="/products/:productId/negotiate" 
            element={
              <ProtectedRoute>
                <ProductNegotiation />
              </ProtectedRoute>
            } 
          />
          
          <Route 
            path="/upload-product" 
            element={
              <ProtectedRoute>
                <ProductUpload />
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
  );
};

function App() {
  return (
    <BrowserRouter>
      <AuthProvider>
        <AppNavigation />
      </AuthProvider>
    </BrowserRouter>
  );
}

export default App;
