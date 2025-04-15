import React from 'react';
import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom';
import Home from '../components/Home/Home';
import Login from '../components/Auth/Login';
import Register from '../components/Auth/Register';
import ProductList from '../components/Products/ProductList';
import EditProduct from '../components/Products/EditProduct';
import Navbar from '../components/Layout/Navbar';
import Footer from '../components/Layout/Footer';
import { useAuth } from '../hooks/useAuth';

// Protected route component
const ProtectedRoute = ({ children }) => {
  const { isAuthenticated } = useAuth();
  
  if (!isAuthenticated) {
    return <Navigate to="/login" />;
  }
  
  return children;
};

const AppRouter = () => {
  return (
    <BrowserRouter>
      <Navbar />
      <main className="container mt-4 mb-5">
        <Routes>
          <Route path="/" element={<Home />} />
          <Route path="/login" element={<Login />} />
          <Route path="/register" element={<Register />} />
          <Route path="/products" element={<ProductList />} />
          <Route path="/profile" element={
            <ProtectedRoute>
              <div className="profile-container">
                <h2>Mi Perfil</h2>
                <p>Aquí estará el contenido de tu perfil</p>
              </div>
            </ProtectedRoute>
          } />
          <Route 
            path="/products/edit/:id" 
            element={
              <ProtectedRoute>
                <EditProduct />
              </ProtectedRoute>
            } 
          />
          <Route path="*" element={<Navigate to="/" />} />
        </Routes>
      </main>
      <Footer />
    </BrowserRouter>
  );
};

export default AppRouter;