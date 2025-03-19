import React from "react";

const ProductItem = ({ product, onDelete, onEdit, isAuthenticated }) => {
  return (
    <tr>
      <td>{product.id}</td>
      <td>{product.title}</td>
      <td>
        {product.description && product.description.length > 50
          ? `${product.description.substring(0, 50)}...`
          : product.description}
      </td>
      <td>{product.category}</td>
      <td>${product.price?.toFixed(2)}</td>
      <td>{product.stockQuantity}</td>
      <td>
        {product.image_url && (
          <img
            src={product.image_url}
            alt={product.title}
            style={{ width: "50px", height: "50px", objectFit: "cover" }}
          />
        )}
      </td>
      <td>
        <div className="btn-group" role="group">
          <button
            type="button"
            className="btn btn-primary btn-sm"
            onClick={() => onEdit(product.id)}
            disabled={!isAuthenticated}
          >
            <i className="bi bi-pencil"></i>
          </button>
          <button
            type="button"
            className="btn btn-danger btn-sm"
            onClick={() => onDelete(product.id)}
            disabled={!isAuthenticated}
          >
            <i className="bi bi-trash"></i>
          </button>
        </div>
      </td>
    </tr>
  );
};

export default ProductItem;
