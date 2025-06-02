import { fetchApi } from './apiConfig';

export const chatService = {
  // Obtener mensajes de un chat
  getMessages: async (chatId) => {
    try {
      const response = await fetchApi(`/chat/${chatId}`);
      console.log('Respuesta de getMessages:', response);
      return response;
    } catch (error) {
      console.error('Error en getMessages:', error);
      throw error;
    }
  },

  // Enviar un mensaje
  sendMessage: async (chatId, message) => {
    try {
      console.log('Enviando mensaje:', { chatId, message });
      const response = await fetchApi(`/chat/${chatId}/message`, {
        method: 'POST',
        body: JSON.stringify({
          contenido: message
        })
      });
      console.log('Respuesta de sendMessage:', response);
      
      if (response.success) {
        // Recargar mensajes después de enviar
        const updatedMessages = await chatService.getMessages(chatId);
        return updatedMessages;
      }
      
      return response;
    } catch (error) {
      console.error('Error en sendMessage:', error);
      throw error;
    }
  },

  // Obtener todos los chats del usuario
 getUserChats: async () => {
  try {
    const response = await fetchApi('/chat/my-chats');
    console.log('Respuesta de getUserChats:', response);
    return response;
  } catch (error) {
    console.error('Error en getUserChats:', error);
    throw error;
  }
},

  // Crear un nuevo chat
  createChat: async (professionalId) => {
    try {
      console.log('Creando chat con profesional:', professionalId);
      const response = await fetchApi('/chat', {
        method: 'POST',
        body: JSON.stringify({
          id_receptor: professionalId
        })
      });
      console.log('Respuesta de createChat:', response);
      return response;
    } catch (error) {
      console.error('Error en createChat:', error);
      throw error;
    }
  },

  // Marcar mensajes como leídos
  markAsRead: async (chatId) => {
    try {
      const response = await fetchApi(`/chat/${chatId}/read`, {
        method: 'POST'
      });
      console.log('Respuesta de markAsRead:', response);
      return response;
    } catch (error) {
      console.error('Error en markAsRead:', error);
      throw error;
    }
  }
}; 
