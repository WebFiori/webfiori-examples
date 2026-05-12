import { Component, OnInit } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { MatTableModule } from '@angular/material/table';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatSelectModule } from '@angular/material/select';
import { MatButtonModule } from '@angular/material/button';
import { MatChipsModule } from '@angular/material/chips';
import { transactions, accounts } from '../api';

@Component({
  selector: 'app-transactions',
  standalone: true,
  imports: [FormsModule, MatTableModule, MatFormFieldModule, MatInputModule, MatSelectModule, MatButtonModule, MatChipsModule],
  template: `
    <h1>Transactions</h1>
    <div style="display:flex;gap:8px;margin-bottom:16px;flex-wrap:wrap">
      <mat-form-field><mat-select [(ngModel)]="form.accountId" placeholder="Account">@for (a of accs; track a.id) { <mat-option [value]="a.id">{{ a.name }}</mat-option> }</mat-select></mat-form-field>
      <mat-form-field><mat-select [(ngModel)]="form.type"><mat-option value="income">Income</mat-option><mat-option value="expense">Expense</mat-option></mat-select></mat-form-field>
      <mat-form-field><input matInput type="number" placeholder="Amount" [(ngModel)]="form.amount"></mat-form-field>
      <mat-form-field><input matInput type="date" [(ngModel)]="form.date"></mat-form-field>
      <mat-form-field><input matInput placeholder="Description" [(ngModel)]="form.description"></mat-form-field>
      <button mat-raised-button color="primary" (click)="add()">Add</button>
    </div>
    <table mat-table [dataSource]="items" style="width:100%">
      <ng-container matColumnDef="date"><th mat-header-cell *matHeaderCellDef>Date</th><td mat-cell *matCellDef="let t">{{ t.date }}</td></ng-container>
      <ng-container matColumnDef="account"><th mat-header-cell *matHeaderCellDef>Account</th><td mat-cell *matCellDef="let t">{{ t.accountName }}</td></ng-container>
      <ng-container matColumnDef="type"><th mat-header-cell *matHeaderCellDef>Type</th><td mat-cell *matCellDef="let t"><mat-chip [highlighted]="t.type==='income'">{{ t.type }}</mat-chip></td></ng-container>
      <ng-container matColumnDef="amount"><th mat-header-cell *matHeaderCellDef>Amount</th><td mat-cell *matCellDef="let t">\${{ t.amount }}</td></ng-container>
      <ng-container matColumnDef="description"><th mat-header-cell *matHeaderCellDef>Description</th><td mat-cell *matCellDef="let t">{{ t.description }}</td></ng-container>
      <tr mat-header-row *matHeaderRowDef="cols"></tr>
      <tr mat-row *matRowDef="let row; columns: cols"></tr>
    </table>
  `
})
export class TransactionsComponent implements OnInit {
  items: any[] = [];
  accs: any[] = [];
  cols = ['date', 'account', 'type', 'amount', 'description'];
  form = { accountId: null as number | null, type: 'expense', amount: '', date: new Date().toISOString().slice(0, 10), description: '' };

  async ngOnInit() { await this.load(); }

  async load() {
    const [t, a] = await Promise.all([transactions.list(), accounts.list()]);
    this.items = t.data.data || [];
    this.accs = a.data.data || [];
  }

  async add() {
    if (!this.form.accountId || !this.form.amount) return;
    await transactions.create(this.form);
    this.form = { ...this.form, amount: '', description: '' };
    await this.load();
  }
}
