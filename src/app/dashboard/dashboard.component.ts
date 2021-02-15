import { Component, OnInit } from '@angular/core';
import {Payment, States} from '../interfaces/payment';
import {Observable} from 'rxjs';
import {State, Store} from '@ngrx/store';
import {AppStates, selectPaymentState} from '../store/app.states';

@Component({
  selector: 'app-dashboard',
  templateUrl: './dashboard.component.html',
  styleUrls: ['./dashboard.component.scss']
})
export class DashboardComponent implements OnInit {
  getState: Observable<any>;
  paymentComplete = false;
  savedPayment: Payment = null;
  public error: string = null;
  constructor(
    private store: Store<AppStates>,
) {
    this.getState = this.store.select(selectPaymentState);
  }

  ngOnInit(): void {
    this.getState.subscribe((state: States) => {
      this.paymentComplete = state.paymentStatus;
      this.savedPayment = state.payment;
      this.error = state.error;
    });
  }
}
