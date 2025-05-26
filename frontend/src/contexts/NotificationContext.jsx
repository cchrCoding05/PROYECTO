import React, { createContext, useContext, useState, useEffect } from 'react';
import { notificationService } from '../services/notificationService';

const NotificationContext = createContext();

export const useNotifications = () => {
    const context = useContext(NotificationContext);
    if (!context) {
        throw new Error('useNotifications debe ser usado dentro de un NotificationProvider');
    }
    return context;
};

export const NotificationProvider = ({ children }) => {
    const [notifications, setNotifications] = useState([]);
    const [unreadCount, setUnreadCount] = useState(0);
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState(null);

    const fetchNotifications = async () => {
        try {
            setLoading(true);
            setError(null);
            const response = await notificationService.getNotifications();
            if (response.success) {
                setNotifications(response.data);
            } else {
                setError(response.message || 'Error al cargar las notificaciones');
            }
        } catch (error) {
            setError(error.message || 'Error al cargar las notificaciones');
            console.error('Error en fetchNotifications:', error);
        } finally {
            setLoading(false);
        }
    };

    const fetchUnreadCount = async () => {
        try {
            const response = await notificationService.getUnreadCount();
            if (response.success) {
                setUnreadCount(response.data.count);
            }
        } catch (error) {
            console.error('Error en fetchUnreadCount:', error);
        }
    };

    const markAsRead = async (notificationId) => {
        try {
            const response = await notificationService.markAsRead(notificationId);
            if (response.success) {
                setNotifications(prevNotifications =>
                    prevNotifications.map(notification =>
                        notification.id === notificationId
                            ? { ...notification, leido: true }
                            : notification
                    )
                );
                setUnreadCount(prev => Math.max(0, prev - 1));
            }
        } catch (error) {
            console.error('Error en markAsRead:', error);
            setError(error.message || 'Error al marcar la notificación como leída');
        }
    };

    const markAllAsRead = async () => {
        try {
            const response = await notificationService.markAllAsRead();
            if (response.success) {
                setNotifications(prevNotifications =>
                    prevNotifications.map(notification => ({
                        ...notification,
                        leido: true
                    }))
                );
                setUnreadCount(0);
            }
        } catch (error) {
            console.error('Error en markAllAsRead:', error);
            setError(error.message || 'Error al marcar todas las notificaciones como leídas');
        }
    };

    const refreshNotifications = async () => {
        await Promise.all([
            fetchNotifications(),
            fetchUnreadCount()
        ]);
    };

    useEffect(() => {
        refreshNotifications();

        // Actualizar cada 30 segundos en lugar de cada minuto
        const interval = setInterval(refreshNotifications, 30000);

        return () => clearInterval(interval);
    }, []);

    const value = {
        notifications,
        unreadCount,
        loading,
        error,
        refreshNotifications,
        markAsRead,
        markAllAsRead
    };

    return (
        <NotificationContext.Provider value={value}>
            {children}
        </NotificationContext.Provider>
    );
}; 