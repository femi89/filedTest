import {Component, OnInit} from '@angular/core';
import {MessageService} from 'primeng/api';

@Component({
  selector: 'app-root',
  templateUrl: './app.component.html',
  styleUrls: ['./app.component.scss'],
  providers: [MessageService],
})
export class AppComponent implements OnInit{
  title = 'filedTest';
  constructor(
    private messageService: MessageService
  ) {
  }

  ngOnInit(): void {
  }
}
