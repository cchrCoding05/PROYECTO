import { useState, useEffect } from 'react';
import './AlertMessage.css';

const AlertMessage = ({ message, type = 'danger', duration = 5000, onClose }) => {
  const [visible, setVisible] = useState(true);

  useEffect(() => {
    if (duration > 0) {
      const timer = setTimeout(() => {
        setVisible(false);
        if (onClose) onClose();
      }, duration);
      
      return () => clearTimeout(timer);
    }
  }, [duration, onClose]);

  const handleClose = () => {
    setVisible(false);
    if (onClose) onClose();
  };

  if (!visible) return null;

  return (
    <div className="alert-container">
      <div className={`alert alert-${type}`}>
        <div className="alert-content">
          {message}
        </div>
        <button 
          className="alert-close" 
          onClick={handleClose}
          aria-label="Cerrar"
        >
          &times;
        </button>
      </div>
    </div>
  );
};

export default AlertMessage;