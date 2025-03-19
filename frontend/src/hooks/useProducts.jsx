import { useState, useEffect, useCallback } from "react";
import * as productService from "../services/productService";

export const useProducts = () => {
  const [products, setProducts] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  const loadProducts = useCallback(async () => {
    setLoading(true);
    setError(null);

    try {
      console.log("Fetching products...");
      const fetchedProducts = await productService.getAllProducts();
      console.log("Products loaded:", fetchedProducts);

      if (Array.isArray(fetchedProducts)) {
        setProducts(fetchedProducts);
      } else {
        console.error(
          "Unexpected response format for products:",
          fetchedProducts
        );
        setProducts([]);
        setError("Formato de respuesta inesperado");
      }
    } catch (err) {
      console.error("Error in loadProducts:", err);
      setError("Error al cargar los productos");
      setProducts([]);
    } finally {
      setLoading(false);
    }
  }, []);

  useEffect(() => {
    loadProducts();
  }, [loadProducts]);

  const getProduct = useCallback(async (id) => {
    try {
      return await productService.getProductById(id);
    } catch (err) {
      console.error(`Error fetching product ${id}:`, err);
      return null;
    }
  }, []);

  const updateProduct = useCallback(
    async (product) => {
      try {
        const result = await productService.updateProduct(product);
        if (result.success) {
          await loadProducts(); // Refresh the list
        }
        return result;
      } catch (err) {
        console.error("Error updating product:", err);
        return { success: false, message: "Error al actualizar el producto" };
      }
    },
    [loadProducts]
  );

  const deleteProduct = useCallback(
    async (id) => {
      try {
        const result = await productService.deleteProduct(id);
        if (result.success) {
          await loadProducts(); // Refresh the list
        }
        return result;
      } catch (err) {
        console.error("Error deleting product:", err);
        return { success: false, message: "Error al eliminar el producto" };
      }
    },
    [loadProducts]
  );

  return {
    products,
    loading,
    error,
    loadProducts,
    getProduct,
    updateProduct,
    deleteProduct,
  };
};
