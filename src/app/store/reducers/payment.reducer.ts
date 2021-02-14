import {Payment} from '../../interfaces/payment';
import {All, PaymentAction, PaymentActionTypes} from '../actions/payment.action';

export interface State{
  paymentStatus: boolean;
  payment: Payment | null;
  error: string | null;
}

export const initialState: State = {
  payment: null,
  paymentStatus: false,
  error: null,
};
export function reducer(state = initialState, action: All): State {
  switch (action.type) {
    case PaymentActionTypes.PAYMENT_COMPLETE: {
      return {
        ...state,
        paymentStatus: true,
        payment: {
          cardHolder: action.payload.cardHolder,
          cardNumber: action.payload.cardNumber,
          cvv: action.payload.cvv,
          expiration: action.payload.expiration,
          amount: action.payload.amount,
        },
        error: null
      };
    }
    case PaymentActionTypes.PAYMENT_FAILED: {
      return {
        ...state,
        error: 'no payment yet'
      };
    }
    default: {
      return state;
    }
  }
}

