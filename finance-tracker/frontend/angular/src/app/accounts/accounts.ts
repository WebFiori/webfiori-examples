import { Component, OnInit } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { MatTableModule } from '@angular/material/table';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatSelectModule } from '@angular/material/select';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { accounts } from '../api';

@Component({
  selector: 'app-accounts',
  standalone: true,
  imports: [FormsModule, MatTableModule, MatFormFieldModule, MatInputModule, MatSelectModule, MatButtonModule, MatIconModule],
  template: `
    <h1>Accounts</h1>
    <div style="display:flex;gap:8px;margin-bottom:16px">
      <mat-form-field><input matInput placeholder="Name" [(ngModel)]="form.name"></mat-form-field>
      <mat-form-field><mat-select [(ngModel)]="form.type"><mat-option value="checking">Checking</mat-option><mat-option value="savings">Savings</mat-option><mat-option value="credit">Credit</mat-option><mat-option value="cash">Cash</mat-option></mat-select></mat-form-field>
      <mat-form-field><input matInput type="number" placeholder="Balance" [(ngModel)]="form.balance"></mat-form-field>
      <button mat-raised-button color="primary" (click)="add()">Add</button>
    </div>
    <table mat-table [dataSource]="items" style="width:100%">
      <ng-container matColumnDef="name"><th mat-header-cell *matHeaderCellDef>Name</th><td mat-cell *matCellDef="let a">{{ a.name }}</td></ng-container>
      <ng-container matColumnDef="type"><th mat-header-cell *matHeaderCellDef>Type</th><td mat-cell *matCellDef="let a">{{ a.type }}</td></ng-container>
      <ng-container matColumnDef="balance"><th mat-header-cell *matHeaderCellDef>Balance</th><td mat-cell *matCellDef="let a">\${{ a.balance }}</td></ng-container>
      <ng-container matColumnDef="actions"><th mat-header-cell *matHeaderCellDef></th><td mat-cell *matCellDef="let a"><button mat-icon-button color="warn" (click)="remove(a.id)"><mat-icon>delete</mat-icon></button></td></ng-container>
      <tr mat-header-row *matHeaderRowDef="['name','type','balance','actions']"></tr>
      <tr mat-row *matRowDef="let row; columns: ['name','type','balance','actions']"></tr>
    </table>
  `
})
export class AccountsComponent implements OnInit {
  items: any[] = [];
  form = { name: '', type: 'checking', balance: '0' };

  async ngOnInit() { await this.load(); }

  async load() { this.items = (await accounts.list()).data.data || []; }

  async add() {
    if (!this.form.name) return;
    await accounts.create(this.form.name, this.form.type, parseFloat(this.form.balance));
    this.form = { name: '', type: 'checking', balance: '0' };
    await this.load();
  }

  async remove(id: number) { await accounts.remove(id); await this.load(); }
}
