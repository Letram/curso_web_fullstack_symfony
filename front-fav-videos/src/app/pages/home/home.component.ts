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

  public currentPage;
  public totalPages;
  public totalVideos;

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
    this.getVideos(1);
  }

  onVideoAdded(event){
    this.userVideos.unshift(event);
  }

  onVideoRemoved(video_id){
    this.userVideos = this.userVideos.filter(video => video.id != video_id);
    this._snackbar.open("Video quitado de la lista correctamente!");
  }

  getVideos(page: number){
    this.loading = true;
    this._videoService.getVideos(this._auth.getToken(), page).subscribe(
      response => {
        console.log(response);
        this.userVideos = response.videos;
        
        this.currentPage = response.current_page;
        this.totalPages = response.total_pages;
        this.totalVideos = response.total_videos;

        this.loading = false;
      }
    );
  }
}
