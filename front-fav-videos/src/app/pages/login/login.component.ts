import { ServerError } from './../../interfaces/ServerError';
import { Component, OnInit, Inject, Injectable } from '@angular/core';
import {
  FormControl,
  FormGroupDirective,
  NgForm,
  Validators,
} from '@angular/forms';
import { ErrorStateMatcher } from '@angular/material/core';
import { AuthService } from '../../services/auth.service';
import { HttpErrorResponse } from '@angular/common/http';
import { MatSnackBar } from '@angular/material/snack-bar';
import { Router } from '@angular/router';
/** Error when invalid control is dirty, touched, or submitted. */
export class LoginFormErrorMatcher implements ErrorStateMatcher {
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
  selector: 'app-login',
  templateUrl: './login.component.html',
  styleUrls: ['./login.component.scss'],
})
export class LoginComponent implements OnInit {
  constructor(private _auth: AuthService, private _snackBar: MatSnackBar, private _router: Router) {}

  public email = '';
  public password = '';
  public loginErrorMatcher: LoginFormErrorMatcher = new LoginFormErrorMatcher();

  public emailFormControl = new FormControl('', [
    Validators.required,
    Validators.email,
  ]);
  public passwordFormControl = new FormControl('', [Validators.required]);

  public errors: string[];

  ngOnInit(): void {}

  onLoginSubmit() {
    this._auth
      .login({
        email: "" + this.emailFormControl.value,
        password: "" + this.passwordFormControl.value,
      })
      .subscribe(
        (response) => {
          this._snackBar.open(`¡Bienvenido, ${response.message.decoded_data.user.name}!`, "¡Gracias!", {
            duration: 2000
          });
          this._auth.persist({decoded_data: response.message.decoded_data, token: response.message.token});
          this._router.navigate(["/home"]);
          console.log(response);
        },
        (httpErrorResponse: HttpErrorResponse) => {
          let error: ServerError = httpErrorResponse.error;
          this.errors = error.errors;
          this._snackBar.open(this.errors.join(","), "Close", {
            duration: 2000
          });
        }
      );
  }
}
