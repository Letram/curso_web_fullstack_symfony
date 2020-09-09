import { LoginFormValidator } from './../../validators/login-form-validator';
import { Component, OnInit, Inject, Injectable } from '@angular/core';
import { FormControl, FormGroupDirective, NgForm, Validators } from '@angular/forms';
import { ErrorStateMatcher } from '@angular/material/core'

/** Error when invalid control is dirty, touched, or submitted. */
export class LoginFormErrorMatcher implements ErrorStateMatcher {
  isErrorState(control: FormControl | null, form: FormGroupDirective | NgForm | null): boolean {
    const isSubmitted = form && form.submitted;
    return !!(control && control.invalid && (control.dirty || control.touched || isSubmitted));
  }
}

@Component({
  selector: 'app-login',
  templateUrl: './login.component.html',
  styleUrls: ['./login.component.scss']
})
export class LoginComponent implements OnInit {

  constructor() { }

  public email = "";
  public password = "";
  public loginErrorMatcher: LoginFormErrorMatcher = new LoginFormErrorMatcher();

   public emailFormControl = new FormControl("", [
    Validators.required,
    Validators.email
  ]);
   public passwordFormControl = new FormControl("", [
    Validators.required
  ]);

  ngOnInit(): void {}

  onLoginSubmit(){
    console.log({email: this.emailFormControl.value, password: this.passwordFormControl.value});
  }


}
