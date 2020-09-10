import { RouterModule, Router } from '@angular/router';
import { Component, OnInit } from '@angular/core';
import { FormControl, FormGroupDirective, NgForm, Validators } from '@angular/forms';
import { ErrorStateMatcher } from '@angular/material/core'
import { AuthService } from 'src/app/services/auth.service';
import { MatSnackBar } from '@angular/material/snack-bar';
import { ServerError } from 'src/app/interfaces/ServerError';
import { HttpErrorResponse } from '@angular/common/http';
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

  public registerErrorFormMatcher: RegisterErrorFormMatcher = new RegisterErrorFormMatcher();

  public errors: any[];


  constructor(private _auth: AuthService, private _snackBar: MatSnackBar, private _router: Router) { }

  ngOnInit(): void {
  }

  public onRegisterSubmit(){
    this._auth.register({
      name: this.emailFormControl.value,
      surname: this.surname,
      email: this.emailFormControl.value,
      password: this.passwordFormControl.value
    }).subscribe(
      response => {
          console.log(response);
          this._snackBar
          .open("Usuario creado correctamente", "Login", {duration: 2000})
          .afterDismissed().subscribe(
            snackBarDismissed => this._router.navigate(["/login"])
          );
      },
      (httpErrorResponse: HttpErrorResponse) => {
        let error: ServerError = httpErrorResponse.error;
        this.errors = error.errors;
        this._snackBar.open(error.errors.violations.map(vio => vio.title).join(", "), "Close", {
          duration: 2000
        })
      }
    );
  }
}
