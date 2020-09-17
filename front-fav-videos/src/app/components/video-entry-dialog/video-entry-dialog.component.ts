import { Component, Inject, OnInit } from '@angular/core';
import {
  FormControl,
  FormGroupDirective,
  NgForm,
  Validators,
} from '@angular/forms';
import { ErrorStateMatcher } from '@angular/material/core';
import { MatDialogRef, MAT_DIALOG_DATA } from '@angular/material/dialog';
import { DomSanitizer, SafeResourceUrl } from '@angular/platform-browser';

export class VideoEntryErrorMatcher implements ErrorStateMatcher {
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
  selector: 'app-video-entry-dialog',
  templateUrl: './video-entry-dialog.component.html',
  styleUrls: ['./video-entry-dialog.component.scss'],
})
export class VideoEntryDialogComponent implements OnInit {
  public videoEntryErrorMatcher: VideoEntryErrorMatcher = new VideoEntryErrorMatcher();

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
    private _sanitizer: DomSanitizer,
    private _dialogRef: MatDialogRef<VideoEntryDialogComponent>,
    @Inject(MAT_DIALOG_DATA)
    public data: { title: string; url: string; description: string }
  ) {}

  ngOnInit(): void {
    console.log(this.data);
  }
  onNoClick() {
    this._dialogRef.close();
  }

  getVideoIframe(url) {
    var video, results;

    if (url === null) {
      return '';
    }
    results = url.match('[\\?&]v=([^&#]*)');
    video = results === null ? url : results[1];

    return this._sanitizer.bypassSecurityTrustResourceUrl(
      'https://www.youtube.com/embed/' + video
    );
  }
}
