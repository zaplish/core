import { MediaLibraryService } from './media-library-service';

export function initMediaLibrary() {
  const wrapper = document.querySelector('[data-media-library]');
  if (!wrapper) return;

  new MediaLibraryService({
    wrapper,
  });
}
