import React, { useState, useEffect } from 'react';

const ThemeToggle = () => {
  // Estado para el tema actual (claro u oscuro)
  const [theme, setTheme] = useState('light');

  // Cargar el tema actual al montar el componente
  useEffect(() => {
    const savedTheme = localStorage.getItem('theme') || 'light';
    setTheme(savedTheme);
  }, []);

  // Función para cambiar el tema
  const toggleTheme = () => {
    const newTheme = theme === 'light' ? 'dark' : 'light';
    
    // Actualizar el DOM
    document.documentElement.setAttribute('data-bs-theme', newTheme);
    
    // Guardar en localStorage
    localStorage.setItem('theme', newTheme);
    
    // Actualizar el estado
    setTheme(newTheme);
  };

  return (
    <div className="d-flex align-items-center p-2 rounded" style={{ background: 'rgba(var(--bs-primary-rgb), 0.1)' }}>
      <button 
        onClick={toggleTheme}
        className="btn position-relative p-0 border-0" 
        style={{ 
          width: '60px', 
          height: '30px',
          backgroundColor: theme === 'light' ? '#f8f9fa' : '#343a40',
          boxShadow: 'inset 0 0 5px rgba(0,0,0,0.2)',
          transition: 'all 0.3s ease',
          borderRadius: '15px'
        }}
        aria-label={theme === 'light' ? 'Cambiar a modo oscuro' : 'Cambiar a modo claro'}
      >
        <div 
          className="position-absolute rounded-circle shadow-sm"
          style={{ 
            width: '26px', 
            height: '26px', 
            top: '2px',
            left: theme === 'light' ? '2px' : '32px',
            backgroundColor: theme === 'light' ? '#ffc107' : '#6f42c1',
            transition: 'all 0.3s ease',
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'center',
            color: '#fff',
            fontSize: '14px'
          }}
        >
          {theme === 'light' ? '☀️' : '🌙'}
        </div>
      </button>
      <span className="ms-2 fw-medium" style={{ color: 'var(--bs-primary)' }}>
        {theme === 'light' ? 'Modo claro' : 'Modo oscuro'}
      </span>
    </div>
  );
};

export default ThemeToggle; 