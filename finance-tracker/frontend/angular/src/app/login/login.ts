import { Component } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { MatCardModule } from '@angular/material/card';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatButtonModule } from '@angular/material/button';
import { auth } from '../api';

@Component({
  selector: 'app-login',
  standalone: true,
  imports: [FormsModule, MatCardModule, MatFormFieldModule, MatInputModule, MatButtonModule],
  template: `
    <mat-card style="max-width:400px;margin:40px auto">
      <mat-card-header><mat-card-title>Login</mat-card-title></mat-card-header>
      <mat-card-content>
        @if (error) { <p style="color:red">{{ error }}</p> }
        <mat-form-field style="width:100%"><input matInput placeholder="Email" [(ngModel)]="email"></mat-form-field>
        <mat-form-field style="width:100%"><input matInput type="password" placeholder="Password" [(ngModel)]="password"></mat-form-field>
      </mat-card-content>
      <mat-card-actions><button mat-raised-button color="primary" (click)="login()" style="width:100%">Login</button></mat-card-actions>
    </mat-card>
  `
})
export class LoginComponent {
  email = 'demo@example.com';
  password = 'demo123';
  error = '';

  async login() {
    try {
      await auth.login(this.email, this.password);
      window.location.href = '/';
    } catch (e: any) {
      this.error = e.response?.data?.message || 'Login failed';
    }
  }
}
