import { fetchApi } from './apiConfig';

// Servicios de notificaciones
export const notificationService = {
    async getNotifications() {
        try {
            const response = await fetchApi('/notifications');
            return response;
        } catch (error) {
            console.error('Error al obtener notificaciones:', error);
            throw error;
        }
    },

    async getUnreadCount() {
        try {
            const response = await fetchApi('/notifications/unread/count');
            return response;
        } catch (error) {
            console.error('Error al obtener contador de notificaciones no leídas:', error);
            throw error;
        }
    },

    async markAsRead(notificationId) {
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

    async markAllAsRead() {
        try {
            const response = await fetchApi('/notifications/read-all', {
                method: 'PUT'
            });
            return response;
        } catch (error) {
            console.error('Error al marcar todas las notificaciones como leídas:', error);
            throw error;
        }
    },

    async createNotification(data) {
        try {
            const response = await fetchApi('/notifications/create', {
                method: 'POST',
                body: JSON.stringify(data)
            });
            return response;
        } catch (error) {
            console.error('Error al crear notificación:', error);
            throw error;
        }
    }
}; 