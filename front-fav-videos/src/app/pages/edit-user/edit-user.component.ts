import { User } from './../../models/user';
import { Component, OnInit } from '@angular/core';
import { AuthService } from 'src/app/services/auth.service';

@Component({
  selector: 'app-edit-user',
  templateUrl: './edit-user.component.html',
  styleUrls: ['./edit-user.component.scss']
})
export class EditUserComponent implements OnInit {

  public user: User;

  constructor(private _auth:AuthService) { }

  ngOnInit(): void {
    this.user = this._auth.getUser();
  }

}
