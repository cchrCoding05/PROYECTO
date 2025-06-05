import React, { createContext, useContext, useState, useEffect } from 'react';
import { useAuth } from '../hooks/useAuth';
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
    const { isAuthenticated } = useAuth();

    const fetchNotifications = async () => {
        if (!isAuthenticated) return;
        
        try {
            setLoading(true);
            setError(null);
            const response = await notificationService.getNotifications();
            if (response.success) {
                setNotifications(response.data);
            }
        } catch (error) {
            console.error('Error al obtener notificaciones:', error);
            setError(error.message);
        } finally {
            setLoading(false);
        }
    };

    const fetchUnreadCount = async () => {
        if (!isAuthenticated) return;
        
        try {
            const response = await notificationService.getUnreadCount();
            if (response.success) {
                setUnreadCount(response.data.count);
            }
        } catch (error) {
            console.error('Error al obtener contador de notificaciones no leídas:', error);
        }
    };

    const markAsRead = async (notificationId) => {
        if (!isAuthenticated) return;
        
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
            console.error('Error al marcar notificación como leída:', error);
        }
    };

    const markAllAsRead = async () => {
        if (!isAuthenticated) return;
        
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
            console.error('Error al marcar todas las notificaciones como leídas:', error);
        }
    };

    const refreshNotifications = async () => {
        if (!isAuthenticated) return;
        
        try {
            await Promise.all([
                fetchNotifications(),
                fetchUnreadCount()
            ]);
        } catch (error) {
            console.error('Error al refrescar notificaciones:', error);
        }
    };

    useEffect(() => {
        if (isAuthenticated) {
            refreshNotifications();
            const interval = setInterval(refreshNotifications, 10000);
            return () => clearInterval(interval);
        } else {
            setNotifications([]);
            setUnreadCount(0);
            setError(null);
        }
    }, [isAuthenticated]);

    return (
        <NotificationContext.Provider
            value={{
                notifications,
                unreadCount,
                loading,
                error,
                markAsRead,
                markAllAsRead,
                refreshNotifications
            }}
        >
            {children}
        </NotificationContext.Provider>
    );
}; 