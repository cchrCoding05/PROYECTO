import React, { useState, useEffect } from "react";
import { useParams, useNavigate } from "react-router-dom";
import { useProducts } from "../../hooks/useProducts";
import AlertMessage from "../Layout/AlertMessage";

const EditProduct = () => {
  const { id } = useParams();
  const navigate = useNavigate();
  const { getProduct, updateProduct } = useProducts();

  const [formData, setFormData] = useState({
    id: "",
    title: "",
    description: "",
    price: "",
    category: "",
    stockQuantity: "",
    image_url: "",
  });

  const [loading, setLoading] = useState(true);
  const [alert, setAlert] = useState(null);

  useEffect(() => {
    const loadProduct = async () => {
      try {
        const product = await getProduct(id);
        if (product) {
          setFormData({
            id: product.id,
            title: product.title || "",
            description: product.description || "",
            price: product.price || "",
            category: product.category || "",
            stockQuantity: product.stockQuantity || "",
            image_url: product.image_url || "",
          });
        } else {
          setAlert({ message: "Producto no encontrado" });
        }
      } catch (error) {
        setAlert({ message: "Error al cargar el producto" });
      } finally {
        setLoading(false);
      }
    };

    loadProduct();
  }, [id, getProduct]);

  const handleChange = (e) => {
    const { name, value } = e.target;
    setFormData({
      ...formData,
      [name]:
        name === "price" || name === "stockQuantity"
          ? value === ""
            ? ""
            : Number(value)
          : value,
    });
  };

  const handleSubmit = async (e) => {
    e.preventDefault();

    try {
      const result = await updateProduct(formData);
      if (result.success) {
        setAlert({
          message: "Producto actualizado correctamente",
          type: "success",
        });

        setTimeout(() => {
          navigate("/products");
        }, 2000);
      } else {
        setAlert({
          message: result.message || "Error al actualizar el producto",
        });
      }
    } catch (error) {
      setAlert({ message: "Error al conectar con el servidor" });
    }
  };

  if (loading) {
    return (
      <div className="text-center my-5">
        <div className="spinner-border" role="status"></div>
      </div>
    );
  }

  return (
    <div className="row justify-content-center">
      <div className="col-md-8">
        <h2 className="mb-4">Editar Producto</h2>

        {alert && (
          <AlertMessage
            message={alert.message}
            type={alert.type || "danger"}
            onClose={() => setAlert(null)}
          />
        )}

        <div className="card shadow-sm">
          <div className="card-body">
            <form onSubmit={handleSubmit}>
              <div className="mb-3">
                <label htmlFor="title" className="form-label">
                  Título
                </label>
                <input
                  type="text"
                  className="form-control"
                  id="title"
                  name="title"
                  value={formData.title}
                  onChange={handleChange}
                  required
                />
              </div>

              <div className="mb-3">
                <label htmlFor="description" className="form-label">
                  Descripción
                </label>
                <textarea
                  className="form-control"
                  id="description"
                  name="description"
                  value={formData.description}
                  onChange={handleChange}
                  rows="3"
                ></textarea>
              </div>

              <div className="mb-3">
                <label htmlFor="category" className="form-label">
                  Categoría
                </label>
                <input
                  type="text"
                  className="form-control"
                  id="category"
                  name="category"
                  value={formData.category}
                  onChange={handleChange}
                  required
                />
              </div>

              <div className="row">
                <div className="col-md-6 mb-3">
                  <label htmlFor="price" className="form-label">
                    Precio
                  </label>
                  <div className="input-group">
                    <span className="input-group-text">$</span>
                    <input
                      type="number"
                      step="0.01"
                      className="form-control"
                      id="price"
                      name="price"
                      value={formData.price}
                      onChange={handleChange}
                      required
                    />
                  </div>
                </div>

                <div className="col-md-6 mb-3">
                  <label htmlFor="stockQuantity" className="form-label">
                    Cantidad en Stock
                  </label>
                  <input
                    type="number"
                    className="form-control"
                    id="stockQuantity"
                    name="stockQuantity"
                    value={formData.stockQuantity}
                    onChange={handleChange}
                    required
                  />
                </div>
              </div>

              <div className="mb-3">
                <label htmlFor="image_url" className="form-label">
                  URL de la imagen
                </label>
                <input
                  type="url"
                  className="form-control"
                  id="image_url"
                  name="image_url"
                  value={formData.image_url}
                  onChange={handleChange}
                />
              </div>

              {formData.image_url && (
                <div className="mb-3 text-center">
                  <p>Vista previa:</p>
                  <img
                    src={formData.image_url}
                    alt={formData.title}
                    style={{ maxHeight: "200px", maxWidth: "100%" }}
                    className="border"
                  />
                </div>
              )}

              <div className="d-flex justify-content-between">
                <button
                  type="button"
                  className="btn btn-secondary"
                  onClick={() => navigate("/products")}
                >
                  Cancelar
                </button>
                <button type="submit" className="btn btn-primary">
                  Guardar Cambios
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  );
};

export default EditProduct;
