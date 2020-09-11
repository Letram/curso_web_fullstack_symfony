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
  getVideos(token: string): Observable<any> {
    let headers = new HttpHeaders().set('Authorization', token);
    return this._http.get(`${environment.url}/videos`, { headers: headers });
  }
}
