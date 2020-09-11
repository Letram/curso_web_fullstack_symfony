import { Video } from './../../models/video';
import { VideoService } from 'src/app/services/video.service';
import { MatSnackBar } from '@angular/material/snack-bar';
import { Component, OnInit } from '@angular/core';
import { AuthService } from 'src/app/services/auth.service';
import { Router } from '@angular/router';

@Component({
  selector: 'app-home',
  templateUrl: './home.component.html',
  styleUrls: ['./home.component.scss'],
})
export class HomeComponent implements OnInit {
  public userVideos: Video[] = [];
  public loading: boolean = false;
  constructor(
    private _auth: AuthService,
    private _videoService: VideoService,
    private _router: Router,
    private _snackbar: MatSnackBar
  ) {}

  ngOnInit(): void {
    if (!this._auth.isLoggedIn()){
      this._router.navigate(['/login']).then(() => {
        this._snackbar.open(
          'Lo sentimos, el inicio de sesión automático ha expirado.'
        );
      });
      return;
    }
    this.getVideos();
  }

  onVideoAdded(event){
    console.log(event);
    this.userVideos.push(event);
  }

  getVideos(){
    this.loading = true;
    this._videoService.getVideos(this._auth.getToken()).subscribe(
      response => {
        console.log(response);
        this.userVideos = response.videos;
        this.loading = false;
      }
    );
  }
}
