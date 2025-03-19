import { fetchApi } from "./api";

export const getAllProducts = async () => {
  try {
    const products = await fetchApi("/api/products");
    console.log("Products fetched:", products);
    // Make sure we always return an array, even if response is unexpected
    return Array.isArray(products) ? products : [];
  } catch (error) {
    console.error("Error fetching products:", error);
    return [];
  }
};

export const getProductById = async (id) => {
  try {
    const product = await fetchApi(`/api/products/${id}`);
    console.log("Product details fetched:", product);
    return product;
  } catch (error) {
    console.error(`Error fetching product ${id}:`, error);
    return null;
  }
};

export const updateProduct = async (product) => {
  try {
    await fetchApi(`/api/products/${product.id}`, {
      method: "PUT",
      body: JSON.stringify(product),
    });
    return { success: true };
  } catch (error) {
    console.error("Error updating product:", error);
    return {
      success: false,
      message:
        typeof error === "string" ? error : "Error al actualizar el producto",
    };
  }
};

export const deleteProduct = async (id) => {
  try {
    await fetchApi(`/api/products/${id}`, {
      method: "DELETE",
    });
    return { success: true };
  } catch (error) {
    console.error(`Error deleting product ${id}:`, error);
    return {
      success: false,
      message:
        typeof error === "string" ? error : "Error al eliminar el producto",
    };
  }
};
