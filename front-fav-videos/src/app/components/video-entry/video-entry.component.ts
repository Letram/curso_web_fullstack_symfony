import { Component, OnInit, Input, Output, EventEmitter } from '@angular/core';
import { MatDialog } from '@angular/material/dialog';
import { MatSnackBar } from '@angular/material/snack-bar';
import { Video } from 'src/app/models/video';
import { AuthService } from 'src/app/services/auth.service';
import { VideoService } from 'src/app/services/video.service';
import { VideoEntryDialogComponent } from '../video-entry-dialog/video-entry-dialog.component';

@Component({
  selector: 'app-video-entry',
  templateUrl: './video-entry.component.html',
  styleUrls: ['./video-entry.component.scss'],
})
export class VideoEntryComponent implements OnInit {
  constructor(
    private _dialog: MatDialog,
    private _snackbar: MatSnackBar,
    private _videoService: VideoService,
    private _auth: AuthService
  ) {}
  @Input() video: Video;
  @Output() onVideoRemoved: EventEmitter<number> = new EventEmitter();
  ngOnInit(): void {}

  openVideoDetails() {
    let videoDialog = this._dialog.open(VideoEntryDialogComponent, {
      width: '900px',
      height: '600px',
      data: {
        title: this.video.title,
        url: this.video.url,
        description: this.video.description,
      },
    });
    videoDialog
      .afterClosed()
      .subscribe(
        (params: { title: string; url: string; description: string }) => {
          let updatedVideo = Object.assign(this.video, params);
          this._videoService
            .updateVideo(this._auth.getToken(), {id: this.video.id, params})
            .subscribe((response: any) => {
              if (response.status == 1)
                this._snackbar.open('Vídeo actualizado!');
            });
        }
      );
  }
  public removeVideo(){
    this._videoService.removeVideo(this._auth.getToken(), this.video.id).subscribe(
      response => {
        if(response.status == 1)
          this.onVideoRemoved.emit(this.video.id);
      },
      error => console.error(error)
    );
  }
}
