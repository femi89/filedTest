import {Action} from '@ngrx/store';

export enum PaymentActionTypes {
  PAYMENT = '[MakePayment] Payments',
  PAYMENT_COMPLETE = '[Payment] Payments Complete',
  PAYMENT_FAILED = '[MakePayment] Payments Failed',
}
export class PaymentAction implements Action{
  readonly type = PaymentActionTypes.PAYMENT;
  constructor(public payload: any) {
  }
}
export class PaymentComplete implements Action {
  readonly type = PaymentActionTypes.PAYMENT_COMPLETE;
  constructor(public payload: any) {
  }
}
export class PaymentFailed implements Action {
  readonly type = PaymentActionTypes.PAYMENT_FAILED;
  constructor(public payload: any) {}
}
export type All =
  | PaymentAction
  |PaymentComplete
  |PaymentFailed;
