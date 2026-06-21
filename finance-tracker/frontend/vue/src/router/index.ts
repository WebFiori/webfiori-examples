import { createRouter, createWebHistory } from 'vue-router'

const router = createRouter({
  history: createWebHistory(),
  routes: [
    { path: '/login', component: () => import('../views/LoginView.vue') },
    { path: '/', component: () => import('../views/DashboardView.vue') },
    { path: '/transactions', component: () => import('../views/TransactionsView.vue') },
    { path: '/accounts', component: () => import('../views/AccountsView.vue') },
    { path: '/budgets', component: () => import('../views/BudgetsView.vue') },
  ]
})

export default router
