import {Injectable} from '@angular/core';
import {Router} from '@angular/router';
import {Actions, Effect, ofType} from '@ngrx/effects';
import {exhaustMap, tap} from 'rxjs/operators';
import {PaymentService} from '../../services/payment.service';
import {PaymentAction, PaymentActionTypes, PaymentComplete, PaymentFailed, All} from '../actions/payment.action';
import {Observable} from 'rxjs';
import {MessageService} from 'primeng/api';


@Injectable()
export class PaymentEffects {

  constructor(
    private actions: Actions,
    private paymentService: PaymentService,
    private router: Router,
    private messageService: MessageService,
  ) {
  }

  @Effect()
  MakePayment: Observable<any> = this.actions.pipe(
    ofType(PaymentActionTypes.PAYMENT),
    exhaustMap((action$: PaymentAction) =>
       this.paymentService.postPayment(action$.payload)
         .then((res: any) => {
           return new PaymentComplete(action$.payload);
         }
      )
    )
  );
  @Effect({ dispatch: false })
  LogInSuccess: Observable<any> = this.actions.pipe(
    ofType(PaymentActionTypes.PAYMENT_COMPLETE),
    tap(() => {
      this.router.navigate(['home']).then(
        () => this.messageService.add({severity: 'success', detail: 'payment Complete', summary: 'Complete'})
      );
      this.router.navigateByUrl('/home');
    })
  );

  @Effect({dispatch: false})
  LogInFailure: Observable<any> = this.actions.pipe(
    ofType(PaymentActionTypes.PAYMENT_FAILED)
  );
}
