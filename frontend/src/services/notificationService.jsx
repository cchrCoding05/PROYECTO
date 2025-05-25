import { fetchApi } from './apiConfig';

export const notificationService = {
    getUnreadCount: async () => {
        try {
            const response = await fetchApi('/notifications/unread/count');
            return response;
        } catch (error) {
            console.error('Error al obtener el contador de notificaciones:', error);
            return { count: 0 };
        }
    },

    getNotifications: async () => {
        try {
            const response = await fetchApi('/notifications');
            return response;
        } catch (error) {
            console.error('Error al obtener las notificaciones:', error);
            return [];
        }
    },

    markAsRead: async (notificationId) => {
        try {
            const response = await fetchApi(`/notifications/${notificationId}/read`, {
                method: 'PUT'
            });
            return response;
        } catch (error) {
            console.error('Error al marcar notificación como leída:', error);
            throw error;
        }
    },

    markAllAsRead: async () => {
        try {
            const response = await fetchApi('/notifications/read-all', {
                method: 'PUT'
            });
            return response;
        } catch (error) {
            console.error('Error al marcar todas las notificaciones como leídas:', error);
            throw error;
        }
    }
}; 