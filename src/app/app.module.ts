import { BrowserModule } from '@angular/platform-browser';
import { NgModule } from '@angular/core';

import { AppRoutingModule } from './app-routing.module';
import { AppComponent } from './app.component';
import {PanelModule} from 'primeng/panel';
import {BrowserAnimationsModule} from '@angular/platform-browser/animations';
import {ToastModule} from 'primeng/toast';
import {IConfig, NgxMaskModule} from 'ngx-mask';
import {InputTextModule} from 'primeng/inputtext';
import { PaymentComponent } from './payment/payment.component';
import {ButtonModule} from 'primeng/button';
import { DashboardComponent } from './dashboard/dashboard.component';
import {ReactiveFormsModule} from '@angular/forms';
import {InputNumberModule} from 'primeng/inputnumber';
import {KeyFilterModule} from 'primeng/keyfilter';
import {RippleModule} from 'primeng/ripple';
import {InputMaskModule} from 'primeng/inputmask';
import {MessageService} from 'primeng/api';
import { EffectsModule } from '@ngrx/effects';
import {StoreModule} from '@ngrx/store';
import {PaymentEffects} from './store/effects/payment.effects';
import {reducers} from './store/app.states';
export const options: Partial<IConfig> | (() => Partial<IConfig>) = null;
@NgModule({
  declarations: [
    AppComponent,
    PaymentComponent,
    DashboardComponent
  ],
  imports: [
    BrowserModule,
    AppRoutingModule,
    PanelModule,
    BrowserAnimationsModule,
    ToastModule,
    NgxMaskModule.forRoot(),
    InputTextModule,
    ButtonModule,
    ReactiveFormsModule,
    InputNumberModule,
    KeyFilterModule,
    RippleModule,
    InputMaskModule,
    EffectsModule.forRoot([PaymentEffects]),
    StoreModule.forRoot(reducers, {}),
  ],
  providers: [MessageService],
  bootstrap: [AppComponent]
})
export class AppModule {

}
