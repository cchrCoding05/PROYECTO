import React, { useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { useNotifications } from '../../contexts/NotificationContext';
import { formatDistanceToNow } from 'date-fns';
import { es } from 'date-fns/locale';
import './NotificationList.css';

const NotificationList = ({ onClose }) => {
    const { notifications, loading, error, markAsRead, markAllAsRead, refreshNotifications } = useNotifications();
    const navigate = useNavigate();

    // Actualizar la lista cuando se abre el desplegable
    useEffect(() => {
        refreshNotifications();
    }, []);

    const handleNotificationClick = async (notification) => {
        try {
            if (!notification.leido) {
                await markAsRead(notification.id);
            }
            
            // Redirigir según el tipo de notificación
            switch (notification.tipo) {
                case 'mensaje':
                    // Para mensajes, usar el ID del emisor (profesional)
                    if (notification.emisor?.id) {
                        navigate(`/negotiate/professional/${notification.emisor.id}`);
                    }
                    break;
                    
                case 'propuesta_producto':
                    // Para propuestas de producto, usar el referenciaId (ID del producto), soportando ambos formatos
                    if (notification.referenciaId || notification.referencia_id) {
                        navigate(`/negotiate/product/${notification.referenciaId || notification.referencia_id}`);
                    }
                    break;
                    
                case 'propuesta_servicio':
                    // Para propuestas de servicio, usar el ID del emisor (profesional)
                    if (notification.emisor?.id) {
                        navigate(`/negotiate/professional/${notification.emisor.id}`);
                    }
                    break;
                    
                default:
                    console.log('Tipo de notificación no manejado:', notification.tipo);
            }
        } catch (error) {
            console.error('Error al manejar la notificación:', error);
        } finally {
            onClose?.();
        }
    };

    const getNotificationIcon = (type) => {
        switch (type) {
            case 'mensaje':
                return 'bi-chat-dots';
            case 'propuesta_producto':
                return 'bi-tag';
            case 'propuesta_profesional':
            case 'propuesta_servicio':
                return 'bi-person';
            default:
                return 'bi-bell';
        }
    };

    if (loading) {
        return (
            <div className="notification-dropdown">
                <div className="notification-loading">
                    <div className="spinner-border text-primary" role="status">
                        <span className="visually-hidden">Cargando...</span>
                    </div>
                </div>
            </div>
        );
    }

    if (error) {
        return (
            <div className="notification-dropdown">
                <div className="notification-error">
                    <i className="bi bi-exclamation-circle"></i>
                    <p>{error}</p>
                </div>
            </div>
        );
    }

    return (
        <div className="notification-dropdown">
            <div className="notification-header">
                <h6>Notificaciones</h6>
                {notifications.length > 0 && (
                    <button
                        className="btn btn-link btn-sm"
                        onClick={markAllAsRead}
                    >
                        Marcar todas como leídas
                    </button>
                )}
            </div>
            <div className="notification-list">
                {notifications.length === 0 ? (
                    <div className="notification-empty">
                        <i className="bi bi-bell-slash"></i>
                        <p>No hay notificaciones</p>
                    </div>
                ) : (
                    notifications.map((notification) => (
                        <div
                            key={notification.id}
                            className={`notification-item ${!notification.leido ? 'unread' : ''}`}
                            onClick={() => handleNotificationClick(notification)}
                        >
                            <div className="notification-icon">
                                <i className={`bi ${getNotificationIcon(notification.tipo)}`}></i>
                            </div>
                            <div className="notification-content">
                                <p className="notification-message">{notification.mensaje}</p>
                                <div className="notification-meta">
                                    <span className="notification-sender">
                                        {notification.emisor?.username || 'Usuario'}
                                    </span>
                                    <span className="notification-time">
                                        {formatDistanceToNow(new Date(notification.fecha_creacion), {
                                            addSuffix: true,
                                            locale: es,
                                            timeZone: 'Europe/Madrid'
                                        })}
                                    </span>
                                </div>
                            </div>
                        </div>
                    ))
                )}
            </div>
        </div>
    );
};

export default NotificationList; 