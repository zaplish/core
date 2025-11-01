import { apiFetch } from '../services/api-fetch';
import { networkError } from '../ui/message';

export class MediaLibraryService {
  constructor({ wrapper }) {
    if (!wrapper) return;

    this.wrapper = wrapper;

    this.mediaLibraryData = window.mediaLibraryData || null;

    // TMP
    console.log(this.mediaLibraryData);

    if (!this.mediaLibraryData) {
      this.loadData({
        initial: true,
      });
    }

    this.init();
  }

  init() {
    // Container
    this.container = document.createElement('div');
    this.container.className = 'media-library__container';

    // Header
    const headerEl = document.createElement('div');
    headerEl.className = 'media-library-header__container';
    headerEl.innerHTML = '<h2>Media Library</h2>';

    this.container.appendChild(headerEl);
    this.wrapper.appendChild(this.container);

    console.log('Media service initialized');
  }

  loadData(params = {}) {
    if (this.loading) return false;

    const mediaLibraryConfig = this.mediaLibraryData?.config || {};

    apiFetch({
      url: '/admin/api/media-library',
      data: getMediaLibraryParams(params, mediaLibraryConfig),
      before: () => {
        this.loading = true;
        this.wrapper.classList.add('-loading');
      },
      complete: () => {
        this.loading = false;
        this.wrapper.classList.remove('-loading');
      },
      success: response => {
        // TMP
        console.log(response);

        if (response.success && response.mediaLibraryData) {
          this.mediaLibraryData = response.mediaLibraryData;
          if (params.initial) {
            this.init();
          } else {
            this.render({
              renderHeader: params.renderHeader,
            });
          }
        } else {
          networkError(response);
        }
      },
      error: xhr => {
        networkError(xhr);
      },
    });
  }
}

function getMediaLibraryParams(params = {}, mediaLibraryConfig = {}, obj = {}) {
  return {
    orderBy: params?.orderBy || mediaLibraryConfig?.orderBy,
    orderDirection: params?.orderDirection || mediaLibraryConfig?.orderDirection,
    searchTerm: params?.searchTerm || mediaLibraryConfig?.searchTerm,
    perPage: params?.perPage || mediaLibraryConfig?.perPage,
    page: params?.page || mediaLibraryConfig?.page,
    ...obj,
  };
}
