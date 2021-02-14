import { Injectable } from '@angular/core';
// import {Store} from '@ngrx/store';
import {Observable, Subject} from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class StorageService {
  public paymentData = new Subject<any>();
  paymentData$ = this.paymentData.asObservable();
  constructor(
  ) { }
  // using angular RXJS state management
  async savePaymentData(data){
     return this.paymentData.next(data);
  }
  getPaymentData(): Observable<any>{
    return this.paymentData$;
  }
  get paymentDat(){
    return this.paymentData$;
  }
}
