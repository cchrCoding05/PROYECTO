import React, { useState, useEffect, useCallback } from 'react';
import { adminService } from '../../services/adminService';
import './ProfessionalManagement.css';
import AlertMessage from '../Layout/AlertMessage';

const UserManagement = ({ itemsPerPage = 20 }) => {
    const [allUsers, setAllUsers] = useState([]); // Guarda todos los usuarios cargados inicialmente
    const [filteredUsers, setFilteredUsers] = useState([]); // Guarda la lista de usuarios después de aplicar el filtro y ordenación
    const [searchTerm, setSearchTerm] = useState('');
    const [selectedUser, setSelectedUser] = useState(null);
    const [showModal, setShowModal] = useState(false);
    const [sortConfig, setSortConfig] = useState({ key: null, direction: 'asc' });
    const [currentPage, setCurrentPage] = useState(1);
    const [alert, setAlert] = useState(null);

    // Cargar todos los usuarios una vez al inicio
    const fetchAllUsers = useCallback(async () => {
        try {
            const response = await adminService.getAllUsers();
            if (response.success && Array.isArray(response.data)) {
                setAllUsers(response.data); // Guarda la lista completa
                // setFilteredUsers(response.data); // Inicialmente, los filtrados son todos (esto se manejará en el useEffect de filtrado/ordenación)
                setAlert(null);
            } else {
                console.error('Formato de respuesta inválido al cargar usuarios:', response);
                setAlert({ message: 'Formato de datos inválido al cargar usuarios', type: 'danger' });
            }
        } catch (error) {
            console.error('Error al cargar usuarios:', error);
            setAlert({ message: error.message || 'No se pudieron cargar los usuarios', type: 'danger' });
        }
    }, []); // Dependencia vacía para ejecutar solo al montar

    useEffect(() => {
        fetchAllUsers();
    }, [fetchAllUsers]);

    // Efecto para filtrar y ordenar cuando cambia el término de búsqueda, el orden o la lista completa de usuarios
    useEffect(() => {
        let currentUsers = [...allUsers];

        // 1. Filtrar por término de búsqueda
        if (searchTerm) {
            const lowerCaseSearchTerm = searchTerm.toLowerCase();
            currentUsers = currentUsers.filter(user =>
                user.username.toLowerCase().includes(lowerCaseSearchTerm) ||
                user.email.toLowerCase().includes(lowerCaseSearchTerm)
            );
        }

        // 2. Ordenar la lista filtrada
        if (sortConfig.key) {
            currentUsers = currentUsers.sort((a, b) => {
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

        // 3. Actualizar la lista de usuarios filtrados y ordenar al inicio
        setFilteredUsers(currentUsers);
        setCurrentPage(1); // Resetear paginación al filtrar u ordenar

    }, [searchTerm, sortConfig, allUsers]); // Dependencias: término de búsqueda, configuración de orden y lista completa

    const handleDelete = async (userId) => {
        if (window.confirm('¿Estás seguro de que deseas eliminar este usuario?')) {
            try {
                const response = await adminService.deleteUser(userId);
                if (response.success) {
                    setAlert({ message: 'Usuario eliminado correctamente', type: 'success' });
                    fetchAllUsers(); // Volver a cargar todos los usuarios después de eliminar para actualizar la lista completa
                } else {
                    setAlert({ message: response.message || 'No se pudo eliminar el usuario', type: 'danger' });
                }
            } catch (error) {
                setAlert({ message: error.message || 'No se pudo eliminar el usuario', type: 'danger' });
            }
        }
    };

    const handleEdit = (user) => {
        setSelectedUser(user);
        setShowModal(true);
        setAlert(null); // Limpiar alerta al abrir modal
    };

    const handleSave = async (e) => {
        e.preventDefault();
        try {
            const response = await adminService.updateUser(selectedUser.id, {
                username: selectedUser.username,
                email: selectedUser.email,
                credits: selectedUser.credits,
                profession: selectedUser.profession,
                description: selectedUser.description,
                profilePhoto: selectedUser.foto_perfil
            });

            if (response.success) {
                setAlert({ message: 'Usuario actualizado correctamente', type: 'success' });
                setShowModal(false);
                fetchAllUsers(); // Volver a cargar todos los usuarios después de actualizar para actualizar la lista completa
            } else {
                setAlert({ message: response.message || 'No se pudo actualizar el usuario', type: 'danger' });
            }
        } catch (error) {
            setAlert({ message: error.message || 'No se pudo actualizar el usuario', type: 'danger' });
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

    const getPaginatedUsers = () => {
        const startIndex = (currentPage - 1) * itemsPerPage;
        return filteredUsers.slice(startIndex, startIndex + itemsPerPage);
    };

    const totalPages = Math.ceil(filteredUsers.length / itemsPerPage);

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

    // Determinar si mostrar el estado vacío general o el de búsqueda
    const showEmptyState = allUsers.length === 0 && !searchTerm;
    const showNoResultsState = allUsers.length > 0 && filteredUsers.length === 0 && searchTerm;

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
                    placeholder="Buscar por nombre de usuario o email..."
                    value={searchTerm}
                    onChange={(e) => setSearchTerm(e.target.value)}
                />
            </div>

            {showEmptyState ? (
                 <div className="empty-state">
                     <i className="bi bi-people"></i>
                     <h3>No hay usuarios</h3>
                     <p>La lista de usuarios está vacía.</p>
                 </div>
             ) : showNoResultsState ? (
                 <div className="empty-state">
                     <i className="bi bi-search"></i>
                     <h3>No se encontraron usuarios</h3>
                     <p>Intenta ajustar tu búsqueda.</p>
                 </div>
            ) : (
                <div className="table-responsive">
                    <table className="table table-hover user-table-responsive">
                        <thead>
                            <tr>
                                <th>Foto</th>
                                <th
                                    onClick={() => {
                                        handleSort('username');
                                        setCurrentPage(1); // Resetear paginación al ordenar
                                    }}
                                    style={{ cursor: 'pointer' }}
                                >
                                    Nombre{getSortIndicator('username')}
                                </th>
                                <th
                                    onClick={() => {
                                        handleSort('email');
                                        setCurrentPage(1); // Resetear paginación al ordenar
                                    }}
                                    style={{ cursor: 'pointer' }}
                                >
                                    Email{getSortIndicator('email')}
                                </th>
                                <th
                                    onClick={() => {
                                        handleSort('credits');
                                        setCurrentPage(1); // Resetear paginación al ordenar
                                    }}
                                    style={{ cursor: 'pointer' }}
                                >
                                    Créditos{getSortIndicator('credits')}
                                </th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            {getPaginatedUsers().map((user) => (
                                <tr key={user.id}>
                                    <td>
                                        <img
                                            src={user.foto_perfil || 'https://via.placeholder.com/50'}
                                            alt={user.username}
                                            className="professional-avatar"
                                        />
                                    </td>
                                    <td>{user.username}</td>
                                    <td>{user.email}</td>
                                    <td>{user.credits}</td>
                                    <td>
                                        <button
                                            className="btn btn-primary btn-sm me-2 btn-edit"
                                            onClick={() => handleEdit(user)}
                                        >
                                            Editar
                                        </button>
                                        <button
                                            className="btn btn-danger btn-sm btn-delete"
                                            onClick={() => handleDelete(user.id)}
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
                <nav aria-label="Navegación de usuarios" className="mt-4">
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
                                <h5 className="modal-title">Editar Usuario</h5>
                                <button
                                    type="button"
                                    className="btn-close"
                                    onClick={() => setShowModal(false)}
                                ></button>
                            </div>
                            <div className="modal-body">
                                <form onSubmit={handleSave}>
                                    <div className="mb-3">
                                        <label className="form-label">Nombre de Usuario</label>
                                        <input
                                            type="text"
                                            className="form-control"
                                            value={selectedUser?.username || ''}
                                            onChange={(e) =>
                                                setSelectedUser({
                                                    ...selectedUser,
                                                    username: e.target.value,
                                                })
                                            }
                                        />
                                    </div>
                                    <div className="mb-3">
                                        <label className="form-label">Email</label>
                                        <input
                                            type="email"
                                            className="form-control"
                                            value={selectedUser?.email || ''}
                                            onChange={(e) =>
                                                setSelectedUser({
                                                    ...selectedUser,
                                                    email: e.target.value,
                                                })
                                            }
                                        />
                                    </div>
                                    <div className="mb-3">
                                        <label className="form-label">Créditos</label>
                                        <input
                                            type="number"
                                            className="form-control"
                                            value={selectedUser?.credits || 0}
                                            onChange={(e) =>
                                                setSelectedUser({
                                                    ...selectedUser,
                                                    credits: parseInt(e.target.value),
                                                })
                                            }
                                        />
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

export default UserManagement;
