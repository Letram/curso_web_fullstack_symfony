import { Observable } from 'rxjs';
import { environment } from './../../environments/environment';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { User } from '../models/user';
@Injectable({
  providedIn: 'root',
})
export class UserService {
  constructor(private _http: HttpClient) {}
  updateUser(token: string, user: User): Observable<any> {
    let headers = new HttpHeaders().set("Authorization", token).set("content-type", "application/json");
    return this._http.put(`${environment.url}/user/edit`, user, {headers});
  }
}
