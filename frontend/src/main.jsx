import React from 'react'
import ReactDOM from 'react-dom/client'
import App from './App.jsx'
// Importamos Bootstrap CSS y JS
import 'bootstrap/dist/css/bootstrap.min.css'
import 'bootstrap/dist/js/bootstrap.bundle.min.js'
// Importamos Bootstrap Icons
import 'bootstrap-icons/font/bootstrap-icons.css'
import './index.css'

// Función para establecer el tema inicial según preferencias del usuario
const setInitialTheme = () => {
  // Verificar si hay una preferencia guardada en localStorage
  const savedTheme = localStorage.getItem('theme');
  
  if (savedTheme) {
    // Aplicar el tema guardado
    document.documentElement.setAttribute('data-bs-theme', savedTheme);
  } else {
    // Verificar la preferencia del sistema
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    document.documentElement.setAttribute('data-bs-theme', prefersDark ? 'dark' : 'light');
    // Guardar preferencia
    localStorage.setItem('theme', prefersDark ? 'dark' : 'light');
  }
}

// Establecer el tema inicial antes de renderizar
setInitialTheme();

ReactDOM.createRoot(document.getElementById('root')).render(
  <React.StrictMode>
    <App />
  </React.StrictMode>,
)
