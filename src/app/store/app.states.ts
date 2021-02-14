import * as payment from './reducers/payment.reducer';
import {createFeatureSelector} from '@ngrx/store';
export interface AppStates {
  paymentState: payment.State;
}
export const reducers = {
  payment: payment.reducer
};
export const selectPaymentState = createFeatureSelector<AppStates>('payment');
