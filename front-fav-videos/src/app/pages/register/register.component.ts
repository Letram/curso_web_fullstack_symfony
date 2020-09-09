import { Component, OnInit } from '@angular/core';
import { FormControl, FormGroupDirective, NgForm, Validators } from '@angular/forms';
import { ErrorStateMatcher } from '@angular/material/core'

/** Error when invalid control is dirty, touched, or submitted. */
export class RegisterErrorFormMatcher implements ErrorStateMatcher {
  isErrorState(control: FormControl | null, form: FormGroupDirective | NgForm | null): boolean {
    const isSubmitted = form && form.submitted;
    return !!(control && control.invalid && (control.dirty || control.touched || isSubmitted));
  }
}

@Component({
  selector: 'app-register',
  templateUrl: './register.component.html',
  styleUrls: ['./register.component.scss']
})
export class RegisterComponent implements OnInit {

  public name: string;
  public surname: string;
  public email: string;
  public password: string;

  public emailFormControl = new FormControl("", [
    Validators.required,
    Validators.email
  ]);
   public passwordFormControl = new FormControl("", [
    Validators.required
  ]);
  public nameFormControl = new FormControl("", [
    Validators.required
  ]);
  public surnameFormControl = new FormControl("", [
    Validators.required
  ]);

  public registerErrorFormMatcher: RegisterErrorFormMatcher = new RegisterErrorFormMatcher();
  constructor() { }

  ngOnInit(): void {
  }

  public onRegisterSubmit(){
    console.log({
      name: this.emailFormControl.value,
      surname: this.surnameFormControl.value,
      email: this.emailFormControl.value,
      password: this.passwordFormControl.value
    });
  }
}
