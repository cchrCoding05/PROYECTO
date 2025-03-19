// components/home/TeamSection.jsx
import React from "react";

const TeamSection = () => {
  return (
    <aside
      id="aside"
      className="col-lg-3 mb-4 p-3 mt-5"
      style={{
        background: "linear-gradient(135deg, #d1c4e9, #b39ddb)",
        borderRadius: "10px",
      }}
    >
      <div
        className="card shadow-lg p-4"
        style={{
          backgroundColor: "#f3e5f5",
          border: "none",
          borderRadius: "15px",
        }}
      >
        <div className="personal-section">
          <h3 className="text-center">Conoce al Equipo</h3>
          <hr className="mb-4" style={{ borderTop: "2px solid #7e57c2" }} />
          <ol className="list-unstyled">
            <li className="mb-5">
              <p
                className="fw-bold text-center text-secondary"
                style={{ fontSize: "1.2rem" }}
              >
                Chahine
              </p>
              <figure
                className="text-center"
                style={{
                  maxWidth: "80%",
                  margin: "0 auto",
                  overflow: "hidden",
                  border: "4px solid #7e57c2",
                  borderRadius: "10px",
                }}
              >
                <img
                  src="chahine.jpg"
                  className="img-fluid mb-2"
                  alt="Fotografía de Chahine"
                  style={{ borderRadius: "5px" }}
                />
                <figcaption
                  className="descripcion text-white py-2"
                  style={{
                    backgroundColor: "#5e35b1",
                    fontSize: "0.9rem",
                    borderRadius: "0 0 10px 10px",
                  }}
                >
                  Desarrollador del sitio
                </figcaption>
              </figure>
            </li>
          </ol>
          <p
            className="text-center mt-4 text-dark"
            style={{ fontSize: "1rem", lineHeight: "1.5" }}
          >
            Nos especializamos en la creación de experiencias web únicas,
            enfocadas en la funcionalidad y el diseño responsivo.
          </p>
        </div>
      </div>
    </aside>
  );
};

export default TeamSection;
