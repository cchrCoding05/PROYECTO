import { api } from './api';

export const notificationService = {
    getUnreadCount: async () => {
        try {
            const response = await api.get('/notifications/unread/count');
            return response.data;
        } catch (error) {
            console.error('Error al obtener el contador de notificaciones:', error);
            return { count: 0 };
        }
    },

    getNotifications: async () => {
        try {
            const response = await api.get('/notifications');
            return response.data;
        } catch (error) {
            console.error('Error al obtener las notificaciones:', error);
            return [];
        }
    },

    markAsRead: async (notificationId) => {
        try {
            const response = await api.put(`/notifications/${notificationId}/read`);
            return response.data;
        } catch (error) {
            console.error('Error al marcar notificación como leída:', error);
            throw error;
        }
    },

    markAllAsRead: async () => {
        try {
            const response = await api.put('/notifications/read-all');
            return response.data;
        } catch (error) {
            console.error('Error al marcar todas las notificaciones como leídas:', error);
            throw error;
        }
    }
}; 