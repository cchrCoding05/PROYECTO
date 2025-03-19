import React, { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { productService, creditService } from '../../services/api';
import { useAuth } from '../../hooks/useAuth';
import AlertMessage from '../Layout/AlertMessage';
import './Negotiation.css';

const ProductNegotiation = () => {
  const { productId } = useParams();
  const { currentUser } = useAuth();
  const navigate = useNavigate();
  
  const [product, setProduct] = useState(null);
  const [chat, setChat] = useState([]);
  const [newPrice, setNewPrice] = useState('');
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [alert, setAlert] = useState(null);

  useEffect(() => {
    loadProductDetails();
  }, [productId]);

  const loadProductDetails = async () => {
    try {
      setLoading(true);
      setError(null);
      
      const productData = await productService.getById(productId);
      setProduct(productData);
      
      // Simulamos que ya hay mensajes de negociación
      setChat([
        {
          id: 1,
          sender: productData.seller.name,
          price: productData.price,
          timestamp: new Date().toISOString()
        },
        // Podríamos cargar las ofertas previas desde la API
      ]);
      
    } catch (err) {
      setError('Error al cargar los detalles del producto');
      console.error(err);
    } finally {
      setLoading(false);
    }
  };

  const handlePriceChange = (e) => {
    // Solo permitir números
    const value = e.target.value.replace(/[^0-9]/g, '');
    setNewPrice(value);
  };

  const handleSubmitPrice = async () => {
    if (!newPrice || parseInt(newPrice) <= 0) {
      setAlert({
        type: 'danger',
        message: 'Por favor, ingresa un precio válido'
      });
      return;
    }

    try {
      setLoading(true);
      
      // Enviar la propuesta al servidor
      const response = await creditService.proposePrice(productId, parseInt(newPrice));
      
      // Agregar el mensaje al chat
      setChat([
        ...chat,
        {
          id: Date.now(),
          sender: currentUser.username,
          price: parseInt(newPrice),
          timestamp: new Date().toISOString()
        }
      ]);
      
      setNewPrice('');
      
      setAlert({
        type: 'success',
        message: 'Propuesta enviada correctamente'
      });
      
    } catch (err) {
      setAlert({
        type: 'danger',
        message: 'Error al enviar la propuesta: ' + err.toString()
      });
    } finally {
      setLoading(false);
    }
  };

  if (loading && !product) {
    return <div className="loading-container">Cargando...</div>;
  }

  if (error) {
    return <div className="error-container">{error}</div>;
  }

  if (!product) {
    return <div className="error-container">Producto no encontrado</div>;
  }

  return (
    <div className="negotiation-container">
      <div className="product-section">
        <div className="product-image-wrapper">
          <img 
            src={product.imageUrl} 
            alt={product.name} 
            className="product-detail-image" 
          />
        </div>
        
        <div className="product-info">
          <h2 className="product-title">Producto</h2>
          <div className="product-name">{product.name}</div>
          <div className="current-price">
            <span className="price-label">Precio actual</span>
            <span className="price-value">{product.price}pts</span>
          </div>
        </div>
      </div>
      
      <div className="chat-section">
        <div className="chat-header">
          <div className="seller-info">@{product.seller.username}</div>
        </div>
        
        <div className="chat-messages">
          {chat.map((message) => (
            <div key={message.id} className={`message ${message.sender === currentUser?.username ? 'outgoing' : 'incoming'}`}>
              <div className="message-bubble">
                <div className="price-proposal">Nuevo Precio &gt; {message.price}pts</div>
              </div>
            </div>
          ))}
        </div>
        
        <div className="new-price-section">
          <h3>Nueva Solicitud</h3>
          
          {alert && (
            <AlertMessage 
              message={alert.message} 
              type={alert.type} 
              onClose={() => setAlert(null)} 
            />
          )}
          
          <div className="price-input-container">
            <button className="decrease-btn">-</button>
            <input
              type="text"
              value={newPrice}
              onChange={handlePriceChange}
              placeholder="850pts"
              className="price-input"
            />
            <button className="increase-btn">+</button>
            <button 
              className="send-price-btn"
              onClick={handleSubmitPrice}
              disabled={loading}
            >
              <span className="send-icon">➤</span>
            </button>
          </div>
        </div>
      </div>
    </div>
  );
};

export default ProductNegotiation; 