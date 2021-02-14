export interface Payment {
  cardNumber: string;
  cardHolder: string;
  expiration: string;
  cvv?: string;
  amount: number;
}

export interface States{
  paymentStatus: boolean;
  payment: Payment | null;
  error: string | null;
}
