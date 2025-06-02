import React, { useState } from 'react';
import UserManagement from './UserManagement';
import ProductManagement from './ProductManagement';
import './AdminPanel.css';

const AdminPanel = () => {
    const [activeTab, setActiveTab] = useState('usuarios');

    return (
        <div className="container py-4">
            <div className="row">
                <div className="col-md-3">
                    <div className="nav flex-column nav-pills">
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
                        {activeTab === 'usuarios' && <UserManagement />}
                        {activeTab === 'productos' && <ProductManagement />}
                    </div>
                </div>
            </div>
        </div>
    );
};

export default AdminPanel; 