import React, { useState, useEffect } from 'react';
import { adminService } from '../../services/adminService';
import './ProductManagement.css';

const ProductManagement = () => {
    const [products, setProducts] = useState([]);
    const [searchTerm, setSearchTerm] = useState('');
    const [selectedProduct, setSelectedProduct] = useState(null);
    const [showModal, setShowModal] = useState(false);
    const [sortConfig, setSortConfig] = useState({ key: null, direction: 'asc' });

    const fetchProducts = async () => {
        try {
            const response = await adminService.getAllProducts();
            if (response.success && Array.isArray(response.data)) {
                setProducts(response.data);
            } else {
                console.error('Formato de respuesta inválido:', response);
                showAlert('Error', 'Formato de datos inválido', 'danger');
            }
        } catch (error) {
            console.error('Error al cargar productos:', error);
            showAlert('Error', 'No se pudieron cargar los productos', 'danger');
        }
    };

    useEffect(() => {
        fetchProducts();
    }, [searchTerm]);

    const showAlert = (title, message, type) => {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
        alertDiv.innerHTML = `
            <strong>${title}</strong> ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        document.body.appendChild(alertDiv);
        setTimeout(() => alertDiv.remove(), 5000);
    };

    const handleEdit = (product) => {
        setSelectedProduct(product);
        setShowModal(true);
    };

    const handleDelete = async (productId) => {
        if (window.confirm('¿Estás seguro de que deseas eliminar este producto?')) {
            try {
                const response = await adminService.deleteProduct(productId);
                if (response.success) {
                    showAlert('Éxito', 'Producto eliminado correctamente', 'success');
                    fetchProducts();
                }
            } catch (error) {
                showAlert('Error', error.message || 'No se pudo eliminar el producto', 'danger');
            }
        }
    };

    const handleSave = async (e) => {
        e.preventDefault();
        try {
            const response = await adminService.updateProduct(selectedProduct.id, {
                name: selectedProduct.name,
                description: selectedProduct.description,
                credits: selectedProduct.credits,
                state: selectedProduct.state,
                image: selectedProduct.image
            });
            
            if (response.success) {
                showAlert('Éxito', 'Producto actualizado correctamente', 'success');
                setShowModal(false);
                fetchProducts();
            }
        } catch (error) {
            showAlert('Error', error.message || 'No se pudo actualizar el producto', 'danger');
        }
    };

    const handleSort = (key) => {
        let direction = 'asc';
        if (sortConfig.key === key && sortConfig.direction === 'asc') {
            direction = 'desc';
        }
        setSortConfig({ key, direction });
    };

    const getSortedProducts = () => {
        let filteredProducts = products;
        
        // Filtrar por término de búsqueda
        if (searchTerm) {
            const searchLower = normalizeText(searchTerm.toLowerCase());
            filteredProducts = products.filter(product => 
                normalizeText(product.name.toLowerCase()).includes(searchLower) ||
                normalizeText(product.description?.toLowerCase() || '').includes(searchLower) ||
                normalizeText(product.seller?.username.toLowerCase() || '').includes(searchLower)
            );
        }

        // Ordenar productos
        if (!sortConfig.key) return filteredProducts;

        return [...filteredProducts].sort((a, b) => {
            let aValue = a[sortConfig.key];
            let bValue = b[sortConfig.key];

            if (aValue < bValue) {
                return sortConfig.direction === 'asc' ? -1 : 1;
            }
            if (aValue > bValue) {
                return sortConfig.direction === 'asc' ? 1 : -1;
            }
            return 0;
        });
    };

    // Función para normalizar texto (eliminar tildes)
    const normalizeText = (text) => {
        return text.normalize('NFD').replace(/[\u0300-\u036f]/g, '');
    };

    const getSortIndicator = (columnKey) => {
        if (sortConfig.key !== columnKey) return '';
        return sortConfig.direction === 'asc' ? ' ↑' : ' ↓';
    };

    return (
        <div className="container py-4">
            <div className="input-group mb-4">
                <span className="input-group-text">
                    <i className="bi bi-search"></i>
                </span>
                <input
                    type="text"
                    className="form-control"
                    placeholder="Buscar productos..."
                    value={searchTerm}
                    onChange={(e) => setSearchTerm(e.target.value)}
                />
            </div>

            {products.length === 0 ? (
                <div className="empty-state">
                    <i className="bi bi-box"></i>
                    <h3>No hay productos</h3>
                    <p>Añade un nuevo producto para comenzar a gestionar tu catálogo.</p>
                </div>
            ) : (
                <div className="table-responsive">
                    <table className="table table-hover product-table-responsive">
                        <thead>
                            <tr>
                                <th>Imagen</th>
                                <th 
                                    onClick={() => handleSort('name')}
                                    style={{ cursor: 'pointer' }}
                                >
                                    Nombre{getSortIndicator('name')}
                                </th>
                                <th 
                                    onClick={() => handleSort('credits')}
                                    style={{ cursor: 'pointer' }}
                                >
                                    Créditos{getSortIndicator('credits')}
                                </th>
                                <th>Estado</th>
                                <th>Vendedor</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            {getSortedProducts().map((product) => (
                                <tr key={product.id}>
                                    <td>
                                        <img
                                            src={product.image || 'https://via.placeholder.com/50'}
                                            alt={product.name}
                                            className="product-thumbnail"
                                        />
                                    </td>
                                    <td>{product.name}</td>
                                    <td>{product.credits}</td>
                                    <td>
                                        <span className={`badge bg-${getStateColor(product.state)}`}>
                                            {getStateText(product.state)}
                                        </span>
                                    </td>
                                    <td>{product.seller.username}</td>
                                    <td>
                                        <button
                                            className="btn btn-primary btn-sm me-2 btn-edit"
                                            onClick={() => handleEdit(product)}
                                        >
                                            Editar
                                        </button>
                                        <button
                                            className="btn btn-danger btn-sm btn-delete"
                                            onClick={() => handleDelete(product.id)}
                                        >
                                            Eliminar
                                        </button>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            )}

            {/* Modal de edición */}
            {showModal && (
                <div className="modal fade show" style={{ display: 'block' }}>
                    <div className="modal-dialog">
                        <div className="modal-content">
                            <div className="modal-header">
                                <h5 className="modal-title">Editar Producto</h5>
                                <button
                                    type="button"
                                    className="btn-close"
                                    onClick={() => setShowModal(false)}
                                ></button>
                            </div>
                            <div className="modal-body">
                                <form onSubmit={handleSave}>
                                    <div className="mb-3">
                                        <label className="form-label">Nombre</label>
                                        <input
                                            type="text"
                                            className="form-control"
                                            value={selectedProduct?.name || ''}
                                            onChange={(e) =>
                                                setSelectedProduct({
                                                    ...selectedProduct,
                                                    name: e.target.value,
                                                })
                                            }
                                        />
                                    </div>
                                    <div className="mb-3">
                                        <label className="form-label">Descripción</label>
                                        <textarea
                                            className="form-control"
                                            value={selectedProduct?.description || ''}
                                            onChange={(e) =>
                                                setSelectedProduct({
                                                    ...selectedProduct,
                                                    description: e.target.value,
                                                })
                                            }
                                        />
                                    </div>
                                    <div className="mb-3">
                                        <label className="form-label">Créditos</label>
                                        <input
                                            type="number"
                                            className="form-control"
                                            value={selectedProduct?.credits || 0}
                                            onChange={(e) =>
                                                setSelectedProduct({
                                                    ...selectedProduct,
                                                    credits: parseInt(e.target.value),
                                                })
                                            }
                                        />
                                    </div>
                                    <div className="mb-3">
                                        <label className="form-label">Estado</label>
                                        <select
                                            className="form-select"
                                            value={selectedProduct?.state || 1}
                                            onChange={(e) =>
                                                setSelectedProduct({
                                                    ...selectedProduct,
                                                    state: parseInt(e.target.value),
                                                })
                                            }
                                        >
                                            <option value={1}>Disponible</option>
                                            <option value={2}>Reservado</option>
                                            <option value={3}>Intercambiado</option>
                                        </select>
                                    </div>
                                    <button type="submit" className="btn btn-primary w-100">
                                        Guardar Cambios
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            )}
        </div>
    );
};

const getStateColor = (state) => {
    switch (state) {
        case 1: return 'success';
        case 2: return 'warning';
        case 3: return 'secondary';
        default: return 'primary';
    }
};

const getStateText = (state) => {
    switch (state) {
        case 1: return 'Disponible';
        case 2: return 'Reservado';
        case 3: return 'Intercambiado';
        default: return 'Desconocido';
    }
};

export default ProductManagement; 