import { Routes } from '@angular/router';

export const routes: Routes = [
  { path: 'login', loadComponent: () => import('./login/login').then(m => m.LoginComponent) },
  { path: '', loadComponent: () => import('./dashboard/dashboard').then(m => m.DashboardComponent) },
  { path: 'transactions', loadComponent: () => import('./transactions/transactions').then(m => m.TransactionsComponent) },
  { path: 'accounts', loadComponent: () => import('./accounts/accounts').then(m => m.AccountsComponent) },
];
