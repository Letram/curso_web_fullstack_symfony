import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { VideoEntryDialogComponent } from './video-entry-dialog.component';

describe('VideoEntryDialogComponent', () => {
  let component: VideoEntryDialogComponent;
  let fixture: ComponentFixture<VideoEntryDialogComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ VideoEntryDialogComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(VideoEntryDialogComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
