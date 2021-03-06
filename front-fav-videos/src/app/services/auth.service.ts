import { User } from '../models/user';
import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Observable, throwError } from 'rxjs';
import { catchError, retry } from 'rxjs/operators';
import { environment } from '../../environments/environment';
@Injectable({
  providedIn: 'root',
})
export class AuthService {
  constructor(private _http: HttpClient) {}

  private _loggedUser: User = null;

  public login(loginInput: {
    email: string;
    password: string;
  }): Observable<any> {
    let headers = new HttpHeaders().set('Content-type', 'application/json');
    return this._http.post(
      `${environment.url}/auth/login`,
      {
        retrieve_token: true,
        ...loginInput,
      },
      { headers: headers }
    );
  }

  public persist(data: { decoded_data: any; token: string }) {
    localStorage.setItem('token', data.token);
    localStorage.setItem('decoded_token', JSON.stringify(data.decoded_data));
    this._loggedUser = data.decoded_data.user;
  }

  public updateUserStored(user: User) {
    let data = localStorage.getItem('decoded_token');
    let parsed_data = JSON.parse(data);
    parsed_data.user = user;
    localStorage.setItem('decoded_token', JSON.stringify(parsed_data));
  }

  public isLoggedIn(): boolean {
    let decoded_data = localStorage.getItem('decoded_token');
    if (!decoded_data) return false;
    let parsed_data = JSON.parse(decoded_data);
    return parsed_data.exp >= new Date().getTime() / 1000;
  }

  public getToken(): string {
    return localStorage.getItem('token');
  }

  public getUser(): User {
    if (!this.isLoggedIn()) return null;
    let decoded_data = JSON.parse(localStorage.getItem('decoded_token'));
    return decoded_data.user;
  }

  public logout() {
    localStorage.removeItem('token');
    localStorage.removeItem('decoded_token');
    this._loggedUser = null;
  }

  public register(registerInput: {
    name: string;
    surname: string;
    email: string;
    password: string;
  }): Observable<any> {
    let headers = new HttpHeaders().set('Content-type', 'application/json');
    return this._http.post(`${environment.url}/auth/register`, registerInput, {
      headers: headers,
    });
  }
}
