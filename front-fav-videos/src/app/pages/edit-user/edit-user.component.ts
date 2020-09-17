import { UserService } from './../../services/user.service';
import { User } from './../../models/user';
import { Component, OnInit } from '@angular/core';
import { AuthService } from 'src/app/services/auth.service';
import {
  FormControl,
  FormGroupDirective,
  NgForm,
  Validators,
} from '@angular/forms';
import { ErrorStateMatcher } from '@angular/material/core';

export class EditUserErrorStateMatcher implements ErrorStateMatcher {
  isErrorState(
    control: FormControl | null,
    form: FormGroupDirective | NgForm | null
  ): boolean {
    const isSubmitted = form && form.submitted;
    return !!(
      control &&
      control.invalid &&
      (control.dirty || control.touched || isSubmitted)
    );
  }
}

@Component({
  selector: 'app-edit-user',
  templateUrl: './edit-user.component.html',
  styleUrls: ['./edit-user.component.scss'],
})
export class EditUserComponent implements OnInit {
  public user: User;
  public surname: string = "";
  public editUserErrorMatcher: EditUserErrorStateMatcher = new EditUserErrorStateMatcher();

  public emailFormControl = new FormControl('', [
    Validators.required,
    Validators.email,
  ]);
  public passwordFormControl = new FormControl('', [Validators.required]);
  public nameFormControl = new FormControl('', [Validators.required]);

  constructor(private _auth: AuthService, private _userService: UserService) {}

  ngOnInit(): void {
    this.user = this._auth.getUser();
    this.initForm();
  }

  onEditSubmit(){
    let userAux: any = {};
    Object.assign(userAux, this.user);
    userAux.email = this.emailFormControl.value;
    userAux.name = this.nameFormControl.value;
    userAux.surname = this.surname;
    this._userService.updateUser(this._auth.getToken(), userAux).subscribe(
      response => {
        console.log(response);
        if(response.status == 1){
          this.user = response.user;
          this.initForm();
          this._auth.updateUserStored(this.user);
        }
      },
      error => console.error(error)
    );
  }

  initForm(){
    this.surname = this.user.surname;
    this.nameFormControl.setValue(this.user.name);
    this.emailFormControl.setValue(this.user.email);
  }
}
