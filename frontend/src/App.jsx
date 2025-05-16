import { useState, useEffect } from 'react';
import { BrowserRouter, Routes, Route, useNavigate } from 'react-router-dom';
import { AuthProvider } from './hooks/useAuth';
import './App.css';
import CookieConsent from './components/CookieConsent';
import Footer from './components/Layout/Footer';

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

// Componentes de productos
import ProductUpload from './components/Product/ProductUpload';
import ProductDetail from './components/Products/ProductDetail';
import MyProducts from './components/Products/MyProducts';
import EditProduct from './components/Product/EditProduct';
import ProductList from './components/Products/ProductList';

// Componente principal de la navegación
const AppNavigation = () => {
  const navigate = useNavigate();
  
  return (
    <div className="d-flex flex-column min-vh-100">
      <Navbar />

      <main className="container flex-grow-1 py-3">
        <Routes>
          {/* Rutas públicas */}
          <Route path="/" element={<Home />} />
          <Route path="/login" element={<Login />} />
          <Route path="/register" element={<Register />} />
          <Route path="/products" element={<ProductList />} />
          
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
            path="/product/:id" 
            element={
              <ProtectedRoute>
                <ProductDetail />
              </ProtectedRoute>
            } 
          />
          
          <Route 
            path="/negotiation/:id" 
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

          <Route 
            path="/my-products" 
            element={
              <ProtectedRoute>
                <MyProducts />
              </ProtectedRoute>
            } 
          />

          <Route 
            path="/edit-product/:id" 
            element={
              <ProtectedRoute>
                <EditProduct />
              </ProtectedRoute>
            } 
          />
        </Routes>
      </main>

      <Footer />
      <CookieConsent />
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
