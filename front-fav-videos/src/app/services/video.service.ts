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

  public addVideo(params: CreateVideoInterface, token: string): Observable<any> {
    let headers = new HttpHeaders()
      .set('Content-type', 'application/json')
      .set('Authorization', token);
    return this._http.post(`${environment.url}/video/create`, params, {
      headers: headers,
    });
  }
  public getVideos(token: string, page: number = 1): Observable<any> {
    let headers = new HttpHeaders().set('Authorization', token);
    return this._http.get(`${environment.url}/videos?page=${page}`, {
      headers: headers,
    });
  }
  public updateVideo(token: string, updatedVideo: {id: number, params: any}) {
    let headers = new HttpHeaders()
      .set('Authorization', token)
      .set('content-type', 'application/json');
    return this._http.put(
      `${environment.url}/videos/${updatedVideo.id}`,
      {...updatedVideo.params},
      { headers }
    );
  }
  public removeVideo(token: string, id: number): Observable<any> {
    let headers = new HttpHeaders()
    .set("Authorization", token);
    return this._http.delete(`${environment.url}/videos/${id}`, {headers});
  }
}
