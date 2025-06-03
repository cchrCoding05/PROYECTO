import React, { useState, useEffect } from 'react';
import UserManagement from './UserManagement';
import ProductManagement from './ProductManagement';
import { adminService } from '../../services/adminService';
import './AdminPanel.css';

const AdminPanel = () => {
    const [activeTab, setActiveTab] = useState('estadisticas');
    const [stats, setStats] = useState(null);
    const [recentActivity, setRecentActivity] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const [currentPage, setCurrentPage] = useState(1);
    const itemsPerPage = 10;

    useEffect(() => {
        const fetchData = async () => {
            try {
                setLoading(true);
                const [statsResponse, activityResponse] = await Promise.all([
                    adminService.getSystemStats(),
                    adminService.getRecentActivity(50)
                ]);
                setStats(statsResponse.data);
                setRecentActivity(activityResponse.data);
                setError(null);
            } catch (err) {
                setError('Error al cargar los datos: ' + err.message);
            } finally {
                setLoading(false);
            }
        };

        if (activeTab === 'estadisticas') {
            fetchData();
        }
    }, [activeTab]);

    const getPaginatedActivity = () => {
        const startIndex = (currentPage - 1) * itemsPerPage;
        return recentActivity.slice(startIndex, startIndex + itemsPerPage);
    };

    const totalPages = Math.ceil(recentActivity.length / itemsPerPage);

    const handlePageChange = (pageNumber) => {
        setCurrentPage(pageNumber);
    };

    const renderStats = () => {
        if (loading) return <div className="text-center"><div className="spinner-border" role="status"></div></div>;
        if (error) return <div className="alert alert-danger">{error}</div>;
        if (!stats) return null;

        return (
            <div className="row">
                <div className="col-md-6 mb-4">
                    <div className="card">
                        <div className="card-header">
                            <h5 className="card-title mb-0">Estadísticas de Usuarios</h5>
                        </div>
                        <div className="card-body">
                            <p>Total de usuarios: {stats.users.total}</p>
                        </div>
                    </div>
                </div>
                <div className="col-md-6 mb-4">
                    <div className="card">
                        <div className="card-header">
                            <h5 className="card-title mb-0">Estadísticas de Productos</h5>
                        </div>
                        <div className="card-body">
                            <p>Total de productos: {stats.products.total}</p>
                            <p>Disponibles: {stats.products.available}</p>
                            <p>Reservados: {stats.products.reserved}</p>
                            <p>Intercambiados: {stats.products.exchanged}</p>
                        </div>
                    </div>
                </div>
                <div className="col-md-6 mb-4">
                    <div className="card">
                        <div className="card-header">
                            <h5 className="card-title mb-0">Negociaciones</h5>
                        </div>
                        <div className="card-body">
                            <p>Total de negociaciones: {stats.negotiations.total}</p>
                            <p>Aceptadas: {stats.negotiations.accepted}</p>
                            <p>Pendientes: {stats.negotiations.pending}</p>
                        </div>
                    </div>
                </div>
                <div className="col-md-6 mb-4">
                    <div className="card">
                        <div className="card-header">
                            <h5 className="card-title mb-0">Top Vendedores</h5>
                        </div>
                        <div className="card-body">
                            <ul className="list-group">
                                {stats.top_sellers.map((seller, index) => (
                                    <li key={index} className="list-group-item d-flex justify-content-between align-items-center">
                                        {seller.nombre_usuario}
                                        <span className="badge bg-primary rounded-pill">{seller.product_count} productos</span>
                                    </li>
                                ))}
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        );
    };

    const renderRecentActivity = () => {
        if (loading) return <div className="text-center"><div className="spinner-border" role="status"></div></div>;
        if (error) return <div className="alert alert-danger">{error}</div>;
        if (!recentActivity.length) return <p>No hay actividad reciente</p>;

        return (
            <div className="card">
                <div className="card-header">
                    <h5 className="card-title mb-0">Actividad Reciente</h5>
                </div>
                <div className="card-body">
                    <div className="list-group">
                        {getPaginatedActivity().map((activity, index) => (
                            <div key={index} className="list-group-item">
                                <div className="d-flex w-100 justify-content-between">
                                    <h6 className="mb-1">{activity.description}</h6>
                                    <small>{new Date(activity.date).toLocaleString()}</small>
                                </div>
                                <p className="mb-1">Usuario: {activity.user}</p>
                                {activity.details && (
                                    <small className="text-muted">
                                        {Object.entries(activity.details).map(([key, value]) => (
                                            <span key={key} className="me-3">
                                                {key}: {value}
                                            </span>
                                        ))}
                                    </small>
                                )}
                            </div>
                        ))}
                    </div>

                    {totalPages > 1 && (
                        <nav aria-label="Navegación de actividad" className="mt-4">
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
                </div>
            </div>
        );
    };

    return (
        <div className="container py-4">
            <div className="row">
                <div className="col-md-3">
                    <div className="nav flex-column nav-pills">
                        <button
                            className={`nav-link ${activeTab === 'estadisticas' ? 'active' : ''}`}
                            onClick={() => setActiveTab('estadisticas')}
                        >
                            <i className="bi bi-graph-up me-2"></i>
                            Estadísticas
                        </button>
                        <button
                            className={`nav-link ${activeTab === 'usuarios' ? 'active' : ''}`}
                            onClick={() => setActiveTab('usuarios')}
                        >
                            <i className="bi bi-people me-2"></i>
                            Usuarios
                        </button>
                        <button
                            className={`nav-link ${activeTab === 'productos' ? 'active' : ''}`}
                            onClick={() => setActiveTab('productos')}
                        >
                            <i className="bi bi-box me-2"></i>
                            Productos
                        </button>
                    </div>
                </div>
                <div className="col-md-9">
                    <div className="tab-content">
                        {activeTab === 'estadisticas' && (
                            <>
                                {renderStats()}
                                {renderRecentActivity()}
                            </>
                        )}
                        {activeTab === 'usuarios' && <UserManagement itemsPerPage={20} />}
                        {activeTab === 'productos' && <ProductManagement itemsPerPage={20} />}
                    </div>
                </div>
            </div>
        </div>
    );
};

export default AdminPanel;
