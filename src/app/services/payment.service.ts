import {Injectable} from '@angular/core';
import {Payment} from '../interfaces/payment';
import {Observable} from 'rxjs';
import {StorageService} from './storage.service';
import {HttpClient} from '@angular/common/http';

@Injectable({
  providedIn: 'root'
})
export class PaymentService {

  constructor(public storageService: StorageService, private httpClient: HttpClient) {
  }

  const;
  baseUrl = 'https://123.0.0.1/payment/';

  allPayment() {
    return this.httpClient.get<Payment>(this.baseUrl);
  }

  getPaymentById(id): Observable<Payment> {
    return this.httpClient.get<Payment>(this.baseUrl + id);
  }

  createPayment(data): Observable<Payment> {
    return this.httpClient.post<Payment>(this.baseUrl, data);
  }

  deletePayment(id): Observable<any> {
    return this.httpClient.delete(this.baseUrl + id);
  }

  updatePayment(id, data): Observable<Payment> {
    return this.httpClient.put<Payment>(this.baseUrl + id, data);
  }

  postPayment(data): Promise<any> {// this is used for creating payment because the api is npt available
    return this.storageService.savePaymentData(data);
  }
}
