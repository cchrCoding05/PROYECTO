import React, { useState, useEffect } from 'react';
import { adminService } from '../../services/adminService';
import './ProfessionalManagement.css';

const UserManagement = () => {
    const [users, setUsers] = useState([]);
    const [searchTerm, setSearchTerm] = useState('');
    const [selectedUser, setSelectedUser] = useState(null);
    const [showModal, setShowModal] = useState(false);
    const [sortConfig, setSortConfig] = useState({ key: null, direction: 'asc' });

    const fetchUsers = async () => {
        try {
            const response = await adminService.getAllUsers();
            if (response.success && Array.isArray(response.data)) {
                setUsers(response.data);
            } else {
                console.error('Formato de respuesta inválido:', response);
                showAlert('Error', 'Formato de datos inválido', 'danger');
            }
        } catch (error) {
            console.error('Error al cargar usuarios:', error);
            showAlert('Error', 'No se pudieron cargar los usuarios', 'danger');
        }
    };

    useEffect(() => {
        fetchUsers();
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

    const handleDelete = async (userId) => {
        if (window.confirm('¿Estás seguro de que deseas eliminar este usuario?')) {
            try {
                const response = await adminService.deleteUser(userId);
                if (response.success) {
                    showAlert('Éxito', 'Usuario eliminado correctamente', 'success');
                    fetchUsers();
                }
            } catch (error) {
                showAlert('Error', error.message || 'No se pudo eliminar el usuario', 'danger');
            }
        }
    };

    const handleEdit = (user) => {
        setSelectedUser(user);
        setShowModal(true);
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
                showAlert('Éxito', 'Usuario actualizado correctamente', 'success');
                setShowModal(false);
                fetchUsers();
            }
        } catch (error) {
            showAlert('Error', error.message || 'No se pudo actualizar el usuario', 'danger');
        }
    };

    const handleSort = (key) => {
        let direction = 'asc';
        if (sortConfig.key === key && sortConfig.direction === 'asc') {
            direction = 'desc';
        }
        setSortConfig({ key, direction });
    };

    const getSortedUsers = () => {
        let filteredUsers = users;
        
        // Filtrar por término de búsqueda
        if (searchTerm) {
            const searchLower = normalizeText(searchTerm.toLowerCase());
            filteredUsers = users.filter(user => 
                normalizeText(user.username.toLowerCase()).includes(searchLower) ||
                normalizeText(user.email.toLowerCase()).includes(searchLower)
            );
        }

        // Ordenar usuarios
        if (!sortConfig.key) return filteredUsers;

        return [...filteredUsers].sort((a, b) => {
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
                    placeholder="Buscar por nombre de usuario o email..."
                    value={searchTerm}
                    onChange={(e) => setSearchTerm(e.target.value)}
                />
            </div>

            <div className="table-responsive">
                <table className="table table-hover user-table-responsive">
                    <thead>
                        <tr>
                            <th>Foto</th>
                            <th 
                                onClick={() => handleSort('username')}
                                style={{ cursor: 'pointer' }}
                            >
                                Nombre{getSortIndicator('username')}
                            </th>
                            <th 
                                onClick={() => handleSort('email')}
                                style={{ cursor: 'pointer' }}
                            >
                                Email{getSortIndicator('email')}
                            </th>
                            <th 
                                onClick={() => handleSort('credits')}
                                style={{ cursor: 'pointer' }}
                            >
                                Créditos{getSortIndicator('credits')}
                            </th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        {getSortedUsers().map((user) => (
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

            {/* Modal de edición */}
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