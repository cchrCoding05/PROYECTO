import { fetchApi } from './apiConfig';

// Servicios de créditos
export const creditService = {
  getBalance: async () => {
    return fetchApi('/credits/balance');
  },
  
  getHistory: async () => {
    return fetchApi('/credits/history');
  },
  
  transfer: async (data) => {
    return fetchApi('/credits/transfer', {
      method: 'POST',
      body: JSON.stringify(data)
    });
  },
};