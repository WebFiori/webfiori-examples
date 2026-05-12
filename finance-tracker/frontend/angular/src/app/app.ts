import { Component, OnInit } from '@angular/core';
import { RouterOutlet, RouterLink } from '@angular/router';
import { MatToolbarModule } from '@angular/material/toolbar';
import { MatButtonModule } from '@angular/material/button';
import { auth } from './api';

@Component({
  selector: 'app-root',
  standalone: true,
  imports: [RouterOutlet, RouterLink, MatToolbarModule, MatButtonModule],
  template: `
    <mat-toolbar color="primary">
      <span>Finance Tracker</span>
      <span style="flex:1"></span>
      @if (loggedIn) {
        <a mat-button routerLink="/">Dashboard</a>
        <a mat-button routerLink="/transactions">Transactions</a>
        <a mat-button routerLink="/accounts">Accounts</a>
        <button mat-button (click)="logout()">Logout</button>
      }
    </mat-toolbar>
    <div style="padding:24px">
      <router-outlet />
    </div>
  `
})
export class AppComponent implements OnInit {
  loggedIn = false;

  async ngOnInit() {
    try {
      await auth.profile();
      this.loggedIn = true;
    } catch {
      this.loggedIn = false;
    }
  }

  logout() {
    document.cookie = 'wf-session=; Max-Age=0; path=/';
    window.location.href = '/login';
  }
}
