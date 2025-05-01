// components/home/HomePage.jsx
import React, { useEffect } from "react";

const Home = () => {
  return (
    <div id="home-view" className="view-container active">
      <div className="row">
        {/* Main Content */}
        <div className="col-12 col-md-9">
          {/* Header Section */}
          <header className="text-center mb-5">
            <div className="container">
              <h1 className="display-4">
                Encuentra lo que necesitas, donde lo necesites
              </h1>
              <p className="lead">
                Buscamos productos en tiendas cercanas para ti.
              </p>
            </div>
          </header>


        </div>
      </div>
    </div>
  );
};

export default Home;
