import { Component, OnInit, Input } from '@angular/core';
import { Video } from 'src/app/models/video';

@Component({
  selector: 'app-video-entry',
  templateUrl: './video-entry.component.html',
  styleUrls: ['./video-entry.component.scss']
})
export class VideoEntryComponent implements OnInit {

  constructor() { }
  @Input() video: Video;
  ngOnInit(): void {
  }

  openVideoDetails(){
    console.log(this.video);
  }

}
