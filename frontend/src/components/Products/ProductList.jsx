import React, { useState } from "react";
import { useNavigate } from "react-router-dom";
import { useProducts } from "../../hooks/useProducts";
import { useAuth } from "../../hooks/useAuth";
import AlertMessage from "../Layout/AlertMessage";
import ProductItem from "./ProductItem";

const ProductList = () => {
  const { products, loading, error, deleteProduct, loadProducts } =
    useProducts();
  const { isAuthenticated } = useAuth();
  const [searchTerm, setSearchTerm] = useState("");
  const [alert, setAlert] = useState(error ? { message: error } : null);
  const navigate = useNavigate();

  const handleSearch = (e) => {
    setSearchTerm(e.target.value);
  };

  const handleDelete = async (id) => {
    if (
      window.confirm("¿Estás seguro de que quieres eliminar este producto?")
    ) {
      const result = await deleteProduct(id);

      if (result.success) {
        setAlert({
          message: "Producto eliminado correctamente",
          type: "success",
        });
      } else {
        setAlert({
          message: result.message || "Error al eliminar el producto",
        });
      }
    }
  };

  const handleEdit = (id) => {
    navigate(`/products/edit/${id}`);
  };

  // Filter products based on search term
  const filteredProducts = products.filter(
    (product) =>
      product.title.toLowerCase().includes(searchTerm.toLowerCase()) ||
      product.description.toLowerCase().includes(searchTerm.toLowerCase()) ||
      product.category.toLowerCase().includes(searchTerm.toLowerCase())
  );

  if (loading) {
    return (
      <div className="text-center my-5">
        <div className="spinner-border" role="status"></div>
      </div>
    );
  }

  return (
    <div className="row">
      <div className="col-12">
        <h2>Lista de Productos</h2>

        {alert && (
          <AlertMessage
            message={alert.message}
            type={alert.type || "danger"}
            onClose={() => setAlert(null)}
          />
        )}

        <div className="d-flex justify-content-between align-items-center mb-3">
          <input
            type="text"
            className="form-control w-50"
            placeholder="Buscar producto..."
            value={searchTerm}
            onChange={handleSearch}
          />
          <button className="btn btn-primary" onClick={() => loadProducts()}>
            <i className="bi bi-arrow-clockwise me-1"></i> Actualizar
          </button>
        </div>

        <div className="table-responsive">
          <table className="table table-striped">
            <thead>
              <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Descripción</th>
                <th>Categoría</th>
                <th>Precio</th>
                <th>Stock</th>
                <th>Imagen</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody>
              {filteredProducts.length > 0 ? (
                filteredProducts.map((product) => (
                  <ProductItem
                    key={product.id}
                    product={product}
                    onDelete={handleDelete}
                    onEdit={handleEdit}
                    isAuthenticated={isAuthenticated}
                  />
                ))
              ) : (
                <tr>
                  <td colSpan="8" className="text-center">
                    No hay productos disponibles
                  </td>
                </tr>
              )}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  );
};

export default ProductList;
