import { Video } from './../models/video';
import { environment } from 'src/environments/environment';
import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Observable, Observer } from 'rxjs';
import { CreateVideoInterface } from '../interfaces/CreateVideoInterface';

@Injectable({
  providedIn: 'root',
})
export class VideoService {
  constructor(private _http: HttpClient) {}

  addVideo(params: CreateVideoInterface, token: string): Observable<any> {
    let headers = new HttpHeaders()
      .set('Content-type', 'application/json')
      .set('Authorization', token);
    return this._http.post(`${environment.url}/video/create`, params, {
      headers: headers,
    });
  }
  getVideos(token: string, page: number = 1): Observable<any> {
    let headers = new HttpHeaders().set('Authorization', token);
    return this._http.get(`${environment.url}/videos?page=${page}`, {
      headers: headers,
    });
  }
  updateVideo(token: string, updatedVideo: {id: number, params: any}) {
    let headers = new HttpHeaders()
      .set('authorization', token)
      .set('content-type', 'application/json');
    return this._http.put(
      `${environment.url}/videos/${updatedVideo.id}`,
      {...updatedVideo.params},
      { headers }
    );
  }
}
