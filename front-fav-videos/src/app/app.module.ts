import { BrowserModule } from '@angular/platform-browser';
import { NgModule } from '@angular/core';

import { AppRoutingModule } from './app-routing.module';
import { AppComponent } from './app.component';
import { BrowserAnimationsModule } from '@angular/platform-browser/animations';

import {
  MAT_FORM_FIELD_DEFAULT_OPTIONS,
  MatFormFieldModule,
} from '@angular/material/form-field';
import { FormsModule, ReactiveFormsModule } from '@angular/forms';
import { DemoMaterialModule } from './material-module';
import {
  ErrorStateMatcher,
  ShowOnDirtyErrorStateMatcher,
} from '@angular/material/core';
import { HttpClientModule } from '@angular/common/http'
import { LoginComponent } from './pages/login/login.component';
import { ErrorComponent } from './pages/error/error.component';
import { RegisterComponent } from './pages/register/register.component';
import { HomeComponent } from './pages/home/home.component';
import { EditUserComponent } from './pages/edit-user/edit-user.component';
import { NavbarComponent } from './components/navbar/navbar.component';
import { AddVideoDialogComponent } from './components/add-video-dialog/add-video-dialog.component';
import { VideoEntryComponent } from './components/video-entry/video-entry.component';
import { VideoEntryDialogComponent } from './components/video-entry-dialog/video-entry-dialog.component';
import { YoutubeThumbnailPipe } from './pipes/youtube-thumbnail.pipe'
@NgModule({
  declarations: [
    AppComponent,
    LoginComponent,
    ErrorComponent,
    RegisterComponent,
    HomeComponent,
    EditUserComponent,
    NavbarComponent,
    AddVideoDialogComponent,
    VideoEntryComponent,
    VideoEntryDialogComponent,
    YoutubeThumbnailPipe,
  ],
  imports: [
    BrowserModule,
    AppRoutingModule,
    BrowserAnimationsModule,
    DemoMaterialModule,
    FormsModule,
    ReactiveFormsModule,
    HttpClientModule
  ],
  providers: [
    {
      provide: MAT_FORM_FIELD_DEFAULT_OPTIONS,
      useValue: { appearance: 'fill' },
    },
    {
      provide: ErrorStateMatcher,
      useClass: ShowOnDirtyErrorStateMatcher,
    },
  ],
  bootstrap: [AppComponent],
})
export class AppModule {}
