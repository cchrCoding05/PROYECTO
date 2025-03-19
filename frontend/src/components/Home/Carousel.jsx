// Carousel.jsx
import React from "react";

const Carousel = () => {
  return (
    <div
      id="mainCarousel"
      className="carousel slide mb-5"
      data-bs-ride="carousel"
    >
      <div className="carousel-indicators">
        <button
          type="button"
          data-bs-target="#mainCarousel"
          data-bs-slide-to="0"
          className="active"
          aria-current="true"
          aria-label="Slide 1"
        ></button>
        <button
          type="button"
          data-bs-target="#mainCarousel"
          data-bs-slide-to="1"
          aria-label="Slide 2"
        ></button>
        <button
          type="button"
          data-bs-target="#mainCarousel"
          data-bs-slide-to="2"
          aria-label="Slide 3"
        ></button>
      </div>
      <div className="carousel-inner rounded shadow">
        <div className="carousel-item active" style={{ height: "400px" }}>
          <img
            src="https://images.unsplash.com/photo-1607082350899-7e105aa886ae?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1740&q=80"
            className="d-block w-100"
            alt="Productos"
            style={{ objectFit: "cover", height: "100%" }}
          />
          <div className="carousel-caption d-none d-md-block bg-dark bg-opacity-50 rounded p-3">
            <h3>Los mejores productos</h3>
            <p>Encuentra todo lo que necesitas en LaMacroEmpresa</p>
          </div>
        </div>
        <div className="carousel-item" style={{ height: "400px" }}>
          <img
            src="https://images.unsplash.com/photo-1556742502-ec7c0e9f34b1?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1287&q=80"
            className="d-block w-100"
            alt="Servicios"
            style={{ objectFit: "cover", height: "100%" }}
          />
          <div className="carousel-caption d-none d-md-block bg-dark bg-opacity-50 rounded p-3">
            <h3>Servicio de calidad</h3>
            <p>Atención personalizada para todos nuestros clientes</p>
          </div>
        </div>
        <div className="carousel-item" style={{ height: "400px" }}>
          <img
            src="https://images.unsplash.com/photo-1577401239170-897942555fb3?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1769&q=80"
            className="d-block w-100"
            alt="Equipo"
            style={{ objectFit: "cover", height: "100%" }}
          />
          <div className="carousel-caption d-none d-md-block bg-dark bg-opacity-50 rounded p-3">
            <h3>Nuestro equipo</h3>
            <p>Profesionales capacitados para brindarte la mejor experiencia</p>
          </div>
        </div>
      </div>
      <button
        className="carousel-control-prev"
        type="button"
        data-bs-target="#mainCarousel"
        data-bs-slide="prev"
      >
        <span className="carousel-control-prev-icon" aria-hidden="true"></span>
        <span className="visually-hidden">Anterior</span>
      </button>
      <button
        className="carousel-control-next"
        type="button"
        data-bs-target="#mainCarousel"
        data-bs-slide="next"
      >
        <span className="carousel-control-next-icon" aria-hidden="true"></span>
        <span className="visually-hidden">Siguiente</span>
      </button>
    </div>
  );
};

export default Carousel;
