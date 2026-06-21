import { Component, OnInit } from '@angular/core';
import { MatCardModule } from '@angular/material/card';
import { MatTableModule } from '@angular/material/table';
import { analytics } from '../api';

@Component({
  selector: 'app-dashboard',
  standalone: true,
  imports: [MatCardModule, MatTableModule],
  template: `
    <h1>Dashboard</h1>
    <div style="display:flex;gap:16px;margin-bottom:24px">
      <mat-card style="flex:1;background:#e8f5e9"><mat-card-content><h3>Income</h3><h2>\${{ summary.income?.toFixed(2) || '0.00' }}</h2></mat-card-content></mat-card>
      <mat-card style="flex:1;background:#ffebee"><mat-card-content><h3>Expenses</h3><h2>\${{ summary.expense?.toFixed(2) || '0.00' }}</h2></mat-card-content></mat-card>
      <mat-card style="flex:1;background:#e3f2fd"><mat-card-content><h3>Net</h3><h2>\${{ summary.net?.toFixed(2) || '0.00' }}</h2></mat-card-content></mat-card>
    </div>
    <h2>Account Balances</h2>
    <table mat-table [dataSource]="balances" style="width:100%">
      <ng-container matColumnDef="name"><th mat-header-cell *matHeaderCellDef>Account</th><td mat-cell *matCellDef="let a">{{ a.name }}</td></ng-container>
      <ng-container matColumnDef="type"><th mat-header-cell *matHeaderCellDef>Type</th><td mat-cell *matCellDef="let a">{{ a.type }}</td></ng-container>
      <ng-container matColumnDef="balance"><th mat-header-cell *matHeaderCellDef>Balance</th><td mat-cell *matCellDef="let a">\${{ a.balance }}</td></ng-container>
      <tr mat-header-row *matHeaderRowDef="['name','type','balance']"></tr>
      <tr mat-row *matRowDef="let row; columns: ['name','type','balance']"></tr>
    </table>
  `
})
export class DashboardComponent implements OnInit {
  summary: any = {};
  balances: any[] = [];

  async ngOnInit() {
    const [s, b] = await Promise.all([analytics.summary(), analytics.accountBalances()]);
    this.summary = s.data.data || {};
    this.balances = b.data.data || [];
  }
}
