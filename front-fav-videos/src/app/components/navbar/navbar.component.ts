import { MatSnackBar } from '@angular/material/snack-bar';
import { CreateVideoInterface } from './../../interfaces/CreateVideoInterface';
import { Component, OnInit, EventEmitter, Output } from '@angular/core';
import { MatDialog } from '@angular/material/dialog';
import { AddVideoDialogComponent } from '../add-video-dialog/add-video-dialog.component';
import { VideoService } from 'src/app/services/video.service';
import { AuthService } from 'src/app/services/auth.service';

@Component({
  selector: 'app-navbar',
  templateUrl: './navbar.component.html',
  styleUrls: ['./navbar.component.scss'],
})
export class NavbarComponent implements OnInit {
  public url: string = '';
  public description: string = '';
  public title: string = '';

  @Output() public onVideoAdded = new EventEmitter<any>();

  constructor(
    private _dialog: MatDialog,
    private _videoService: VideoService,
    private _auth: AuthService,
    private _snackbar: MatSnackBar
  ) {}

  ngOnInit(): void {}

  openDialog() {
    const addVideoDialog = this._dialog.open(AddVideoDialogComponent, {
      width: '500px',
      data: { title: this.title, url: this.url, description: this.description },
    });

    addVideoDialog.afterClosed().subscribe((params: CreateVideoInterface) => {
      this._videoService
        .addVideo(params, this._auth.getToken())
        .subscribe((response) => {
          if (response.status == 1) {
            this._snackbar.open('¡Vídeo añadido!');
            this.onVideoAdded.emit(response.video_added);
          }
        });
    });
  }
}
