import { Component, OnInit } from '@angular/core';
import {FormBuilder, FormGroup, Validators} from '@angular/forms';
import {formatDate} from '@angular/common';
import {MessageService} from 'primeng/api';
import {PaymentService} from '../services/payment.service';
import {Router} from '@angular/router';
import {Store} from '@ngrx/store';
import {AppStates} from '../store/app.states';
import {PaymentAction} from '../store/actions/payment.action';

@Component({
  selector: 'app-payment',
  templateUrl: './payment.component.html',
  styleUrls: ['./payment.component.scss'],
})
export class PaymentComponent implements OnInit {
  paymentForm: FormGroup;
  validName: RegExp = /^[^<>*!]+$/;
  currentDate;
  constructor(
    private formBuilder: FormBuilder,
    private messageService: MessageService,
    private paymentService: PaymentService,
    public router: Router,
    private store: Store<AppStates>
  ) { }

  ngOnInit(): void {
    this.paymentForm = this.formBuilder.group({
      cardNumber: ['', Validators.required],
      cardHolder: ['', Validators.required],
      amount: [0, [Validators.required, Validators.minLength(4)]],
      cvv: [''],
      expiration: ['', [Validators.required]]
    });
  }
  validate(){
    const today = new Date();
    // tslint:disable-next-line:radix
    const month = parseInt(formatDate(today, 'MM', 'en-us') );
    // tslint:disable-next-line:radix
    const year = parseInt(formatDate(today, 'yy', 'en-us'));
    const expireDateControl = this.paymentForm.get('expiration');
    if (expireDateControl.value.length === 5 ){
      // get the year out
      const expiration = expireDateControl.value;
      const rawDate = expiration.split('/');
      const selectYear = parseInt(rawDate[1]);
      const selectMonth = parseInt(rawDate[0]);
      // tslint:disable-next-line:radix
      if (selectYear < year || selectYear > (year + 5)){
        expireDateControl.setErrors(['invalid date']);
      } else if (selectYear === year && selectMonth < month){
        expireDateControl.setErrors(['invalid date month']);
      }
    } else{
      expireDateControl.markAsDirty();
    }
  }

  makePayment() {
    const payload = this.paymentForm.value;
    this.messageService.add({severity: 'success', summary: 'Complete', detail: 'payment completed'});
    this.store.dispatch(new PaymentAction(payload));
  }
}
