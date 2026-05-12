import axios from 'axios';

const api = axios.create({
  baseURL: 'http://localhost:8080/apis',
  withCredentials: true,
  headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
});

const toForm = (data: Record<string, any>) => {
  const params = new URLSearchParams();
  for (const [k, v] of Object.entries(data)) {
    if (v !== null && v !== undefined) params.append(k, String(v));
  }
  return params;
};

export const auth = {
  login: (email: string, password: string) =>
    api.post('/auth', toForm({ service: 'auth', email, password })),
  profile: () => api.get('/auth', { params: { service: 'auth' } }),
};

export const accounts = {
  list: () => api.get('/accounts', { params: { service: 'accounts' } }),
  create: (name: string, type: string, balance: number) =>
    api.post('/accounts', toForm({ service: 'accounts', name, type, balance })),
  remove: (id: number) => api.delete('/accounts', { params: { service: 'accounts', id } }),
};

export const categories = {
  list: () => api.get('/categories', { params: { service: 'categories' } }),
};

export const transactions = {
  list: (filters?: Record<string, any>) =>
    api.get('/transactions', { params: { service: 'transactions', ...filters } }),
  create: (data: Record<string, any>) =>
    api.post('/transactions', toForm({ service: 'transactions', ...data })),
};

export const analytics = {
  summary: () => api.get('/analytics', { params: { service: 'analytics', report: 'summary' } }),
  byCategory: () => api.get('/analytics', { params: { service: 'analytics', report: 'byCategory' } }),
  accountBalances: () => api.get('/analytics', { params: { service: 'analytics', report: 'accountBalances' } }),
};

export const budgets = {
  list: () => api.get('/budgets', { params: { service: 'budgets' } }),
  create: (data: Record<string, any>) => api.post('/budgets', toForm({ service: 'budgets', ...data })),
  remove: (id: number) => api.delete('/budgets', { params: { service: 'budgets', id } }),
};

export default api;
