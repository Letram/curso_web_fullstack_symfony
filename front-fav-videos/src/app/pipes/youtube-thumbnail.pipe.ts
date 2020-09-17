import { Pipe, PipeTransform } from '@angular/core';

@Pipe({
  name: 'youtubeThumbnail',
})
export class YoutubeThumbnailPipe implements PipeTransform {
  transform(url: string, size: string): string {
    let video_id, results, thumburl;
    if (url === null) {
      return '';
    }
    results = url.match('[\\?&]v=([^&#]*)');
    video_id = results === null ? url : results[1];
    if (size != null) {
      thumburl = `http://img.youtube.com/vi/${video_id}/${size}.jpg`;
    } else thumburl = `http://img.youtube.com/vi/${video_id}/mqdefault.jpg`;
    return thumburl;
  }
}
