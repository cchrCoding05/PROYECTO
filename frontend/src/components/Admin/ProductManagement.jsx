import React, { useState, useEffect, useCallback } from 'react';
import { adminService } from '../../services/adminService';
import './ProductManagement.css';
import AlertMessage from '../Layout/AlertMessage';

const ProductManagement = ({ itemsPerPage = 20 }) => {
    const [allProducts, setAllProducts] = useState([]);
    const [filteredProducts, setFilteredProducts] = useState([]);
    const [searchTerm, setSearchTerm] = useState('');
    const [selectedProduct, setSelectedProduct] = useState(null);
    const [showModal, setShowModal] = useState(false);
    const [sortConfig, setSortConfig] = useState({ key: null, direction: 'asc' });
    const [currentPage, setCurrentPage] = useState(1);
    const [alert, setAlert] = useState(null);

    const fetchAllProducts = useCallback(async () => {
        try {
            const response = await adminService.getAllProducts();
            if (response.success && Array.isArray(response.data)) {
                setAllProducts(response.data);
                setAlert(null);
            } else {
                console.error('Formato de respuesta inválido al cargar productos:', response);
                setAlert({ message: 'Formato de datos inválido al cargar productos', type: 'danger' });
            }
        } catch (error) {
            console.error('Error al cargar productos:', error);
            setAlert({ message: error.message || 'No se pudieron cargar los productos', type: 'danger' });
        }
    }, []);

    useEffect(() => {
        fetchAllProducts();
    }, [fetchAllProducts]);

    useEffect(() => {
        let currentProducts = [...allProducts];

        if (searchTerm) {
            const normalizedSearchTerm = normalizeText(searchTerm.toLowerCase());
            currentProducts = currentProducts.filter(product => {
                const normalizedProductName = normalizeText(product.name.toLowerCase());
                const normalizedSellerName = normalizeText((product.seller?.username || '').toLowerCase());
                
                return normalizedProductName.includes(normalizedSearchTerm) ||
                       normalizedSellerName.includes(normalizedSearchTerm);
            });
        }

        if (sortConfig.key) {
            currentProducts = currentProducts.sort((a, b) => {
                const aValue = a[sortConfig.key];
                const bValue = b[sortConfig.key];

                if (aValue < bValue) {
                    return sortConfig.direction === 'asc' ? -1 : 1;
                }
                if (aValue > bValue) {
                    return sortConfig.direction === 'asc' ? 1 : -1;
                }
                return 0;
            });
        }

        setFilteredProducts(currentProducts);
        setCurrentPage(1);
    }, [searchTerm, sortConfig, allProducts]);

    const handleEdit = (product) => {
        setSelectedProduct(product);
        setShowModal(true);
        setAlert(null);
    };

    const handleDelete = async (productId) => {
        if (window.confirm('¿Estás seguro de que deseas eliminar este producto?')) {
            try {
                const response = await adminService.deleteProduct(productId);
                if (response.success) {
                    setAlert({ message: 'Producto eliminado correctamente', type: 'success' });
                    fetchAllProducts();
                } else {
                    setAlert({ message: response.message || 'No se pudo eliminar el producto', type: 'danger' });
                }
            } catch (error) {
                setAlert({ message: error.message || 'No se pudo eliminar el producto', type: 'danger' });
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
                setAlert({ message: 'Producto actualizado correctamente', type: 'success' });
                setShowModal(false);
                fetchAllProducts();
            } else {
                setAlert({ message: response.message || 'No se pudo actualizar el producto', type: 'danger' });
            }
        } catch (error) {
            setAlert({ message: error.message || 'No se pudo actualizar el producto', type: 'danger' });
        }
    };

    const handleSort = (key) => {
        let direction = 'asc';
        if (sortConfig.key === key && sortConfig.direction === 'asc') {
            direction = 'desc';
        }
        setSortConfig({ key, direction });
        setCurrentPage(1);
    };

    const getPaginatedProducts = () => {
        const startIndex = (currentPage - 1) * itemsPerPage;
        return filteredProducts.slice(startIndex, startIndex + itemsPerPage);
    };

    const totalPages = Math.ceil(filteredProducts.length / itemsPerPage);

    const handlePageChange = (pageNumber) => {
        setCurrentPage(pageNumber);
    };

    const normalizeText = (text) => {
        return text.normalize('NFD').replace(/[\u0300-\u036f]/g, '');
    };

    const getSortIndicator = (columnKey) => {
        if (sortConfig.key !== columnKey) return '';
        return sortConfig.direction === 'asc' ? ' ↑' : ' ↓';
    };

    const showEmptyState = allProducts.length === 0 && !searchTerm;
    const showNoResultsState = allProducts.length > 0 && filteredProducts.length === 0 && searchTerm;

    return (
        <div className="container py-4">
            {alert && (
                <div className="alert-container">
                    <AlertMessage
                        message={alert.message}
                        type={alert.type}
                        onClose={() => setAlert(null)}
                    />
                </div>
            )}

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

            {showEmptyState ? (
                <div className="empty-state">
                    <i className="bi bi-box"></i>
                    <h3>No hay productos</h3>
                    <p>Añade un nuevo producto para comenzar a gestionar tu catálogo.</p>
                </div>
            ) : showNoResultsState ? (
                <div className="empty-state">
                    <i className="bi bi-search"></i>
                    <h3>No se encontraron productos</h3>
                    <p>Intenta ajustar tu búsqueda.</p>
                </div>
            ) : (
                <div className="table-responsive">
                    <table className="table table-hover product-table-responsive">
                        <thead>
                            <tr>
                                <th>Imagen</th>
                                <th
                                    onClick={() => {
                                        handleSort('name');
                                        setCurrentPage(1);
                                    }}
                                    style={{ cursor: 'pointer' }}
                                >
                                    Nombre{getSortIndicator('name')}
                                </th>
                                <th
                                    onClick={() => {
                                        handleSort('credits');
                                        setCurrentPage(1);
                                    }}
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
                            {getPaginatedProducts().map((product) => (
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

            {totalPages > 1 && (
                <nav aria-label="Navegación de productos" className="mt-4">
                    <ul className="pagination justify-content-center">
                        <li className={`page-item ${currentPage === 1 ? 'disabled' : ''}`}>
                            <button
                                className="page-link"
                                onClick={() => handlePageChange(currentPage - 1)}
                                disabled={currentPage === 1}
                            >
                                Anterior
                            </button>
                        </li>
                        {[...Array(totalPages)].map((_, index) => (
                            <li key={index + 1} className={`page-item ${currentPage === index + 1 ? 'active' : ''}`}>
                                <button
                                    className="page-link"
                                    onClick={() => handlePageChange(index + 1)}
                                >
                                    {index + 1}
                                </button>
                            </li>
                        ))}
                        <li className={`page-item ${currentPage === totalPages ? 'disabled' : ''}`}>
                            <button
                                className="page-link"
                                onClick={() => handlePageChange(currentPage + 1)}
                                disabled={currentPage === totalPages}
                            >
                                Siguiente
                            </button>
                        </li>
                    </ul>
                </nav>
            )}

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
