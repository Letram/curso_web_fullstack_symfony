import { Component, OnInit, Inject } from '@angular/core';
import { ErrorStateMatcher } from '@angular/material/core';
import {
  FormControl,
  FormGroupDirective,
  NgForm,
  Validators,
} from '@angular/forms';
import { MatDialogRef, MAT_DIALOG_DATA } from '@angular/material/dialog';

/** Error when invalid control is dirty, touched, or submitted. */
export class AddVideoErrorMatcher implements ErrorStateMatcher {
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
  selector: 'app-add-video-dialog',
  templateUrl: './add-video-dialog.component.html',
  styleUrls: ['./add-video-dialog.component.scss'],
})
export class AddVideoDialogComponent implements OnInit {
  public addVideoErrorMatcher: AddVideoErrorMatcher = new AddVideoErrorMatcher();

  public titleFormControl = new FormControl('', [
    Validators.required,
    Validators.minLength(5),
  ]);
  private _regex = /^[A-Za-z][A-Za-z\d.+-]*:\/*(?:\w+(?::\w+)?@)?[^\s/]+(?::\d+)?(?:\/[\w#!:.?+=&%@\-/]*)?$/;
  public urlFormControl = new FormControl('', [
    Validators.required,
    Validators.pattern(this._regex),
  ]);
  constructor(
    private _dialogRef: MatDialogRef<AddVideoDialogComponent>,
    @Inject(MAT_DIALOG_DATA)
    public data: { title: string; url: string; description: string }
  ) {}

  ngOnInit(): void {}

  onNoClick() {
    this._dialogRef.close();
  }
}
