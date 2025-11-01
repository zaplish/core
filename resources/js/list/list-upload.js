import { apiFetch } from '../services/api-fetch';
import { attachDragDrop } from '../utils/drag-drop';
import { getFilePreviewFromFileInput } from '../utils/file-icon';
import { removeContainer } from '../utils/animate-remove';
import { config } from '../config/config';
import { networkErrorText } from '../ui/message';
import { __ } from '../utils/locale';

export function initListUpload() {
  const buttonsEl = document.querySelectorAll('[data-list-upload]');
  if (!buttonsEl.length) return;

  buttonsEl.forEach(buttonEl => {
    if (buttonEl._listUploadEventAdded) return;
    buttonEl._listUploadEventAdded = true;

    // List wrapper
    const listId = buttonEl.getAttribute('data-list-upload');
    const listWrapperEl = document.querySelector(`[data-list="${listId}"]`);

    if (!listWrapperEl) {
      console.warn('Target element not found.');
      return;
    }

    // Attach drag & drop
    attachDragDrop(listWrapperEl, {
      onDrop: files => {
        if (!files.length) return;
        uploadFiles(files, listWrapperEl);
      },
    });

    buttonEl.addEventListener('click', function (e) {
      e.preventDefault();

      // Hidden file input
      const input = document.createElement('input');
      input.type = 'file';
      input.multiple = true;

      // Change event
      input.onchange = function () {
        const files = Array.from(input.files);
        if (!files.length) return;
        uploadFiles(files, listWrapperEl);
      };

      input.click();
    });
  });
}

/**
 * Upload files to list
 */
function uploadFiles(files, listWrapperEl) {
  // Ensure main container exists
  let wrapperEl = listWrapperEl.querySelector('.upload-progress__wrapper');
  let containerEl = wrapperEl?.querySelector('.upload-progress__container');

  if (!wrapperEl) {
    wrapperEl = document.createElement('div');
    wrapperEl.className = 'upload-progress__wrapper';
    listWrapperEl.prepend(wrapperEl);

    containerEl = document.createElement('div');
    containerEl.className = 'upload-progress__container';
    wrapperEl.appendChild(containerEl);
  }

  // Get list service
  const listService = listWrapperEl._listService;

  // Upload files
  files.forEach(file => {
    const itemWrapperEl = document.createElement('div');
    itemWrapperEl.className = 'upload-progress__item-wrapper';
    itemWrapperEl.addEventListener('click', () => {
      if (itemWrapperEl.classList.contains('-complete')) {
        removeUploadProgressItem(itemWrapperEl, wrapperEl);
      }
    });
    containerEl.prepend(itemWrapperEl);

    const itemContainerEl = document.createElement('div');
    itemContainerEl.className = 'upload-progress__item-container';
    itemWrapperEl.appendChild(itemContainerEl);

    const itemEl = document.createElement('div');
    itemEl.className = 'upload-progress__item';
    itemContainerEl.appendChild(itemEl);

    const itemPreviewEl = document.createElement('div');
    itemPreviewEl.className = 'upload-progress__preview';

    const itemPreviewIconEl = getFilePreviewFromFileInput(file);
    itemEl.appendChild(itemPreviewIconEl);

    const itemContentEl = document.createElement('div');
    itemContentEl.className = 'upload-progress__content';
    itemEl.appendChild(itemContentEl);

    const itemTextContainerEl = document.createElement('div');
    itemTextContainerEl.className = 'upload-progress__text-container';
    itemContentEl.appendChild(itemTextContainerEl);

    const itemTextEl = document.createElement('div');
    itemTextEl.className = 'upload-progress__text';
    itemTextEl.innerHTML = '<div class="upload-progress__filename">' + file.name + '</div>';
    itemTextContainerEl.appendChild(itemTextEl);

    const itemStatusEl = document.createElement('div');
    itemStatusEl.className = 'upload-progress__status';
    itemStatusEl.innerHTML = '0%';
    itemTextContainerEl.appendChild(itemStatusEl);

    const itemProgressBarContainerEl = document.createElement('div');
    itemProgressBarContainerEl.className = 'upload-progress__bar-container';
    itemContentEl.appendChild(itemProgressBarContainerEl);

    const itemProgressBarEl = document.createElement('div');
    itemProgressBarEl.className = 'upload-progress__bar';
    itemProgressBarEl.style.width = '0%';
    itemProgressBarContainerEl.appendChild(itemProgressBarEl);

    // Upload request
    const formData = new FormData();
    formData.append('file', file);

    apiFetch({
      url: '/admin/api/media-upload',
      method: 'POST',
      headers: {
        'Accept': 'application/json',
      },
      data: formData,
      isUpload: true,
      progress: percent => {
        itemProgressBarEl.style.width = Math.floor(percent) + '%';
        itemStatusEl.innerHTML = Math.floor(percent) + '%';
      },
      complete: () => {
        itemWrapperEl.classList.add('-complete');
      },
      success: response => {
        if (response && response.success) {
          itemWrapperEl.classList.add('-success');
          if (response.listData) {
            listService.listData = response.listData;
            listService.render();
          }
          setTimeout(() => {
            removeUploadProgressItem(itemWrapperEl, wrapperEl);
          }, config.removeUploadPreviewDelay);
        } else {
          showUploadErrorMessage(itemWrapperEl, response);
        }
      },
      error: xhr => {
        showUploadErrorMessage(itemWrapperEl, xhr);
      },
    });
  });
}

/**
 * Show upload error message
 */
function showUploadErrorMessage(itemWrapperEl, responseOrError) {
  itemWrapperEl.classList.add('-error');
  const errorEl = document.createElement('div');
  errorEl.className = 'upload-progress__error';
  errorEl.innerHTML = networkErrorText(responseOrError);
  itemWrapperEl.querySelector('.upload-progress__text').appendChild(errorEl);
}

/**
 * Remove upload progress item
 */
function removeUploadProgressItem(itemWrapperEl, wrapperEl) {
  removeContainer(itemWrapperEl, {
    fadeDuration: config.slowTransitionSpeed,
    collapseDuration: config.defaultTransitionSpeed,
    onComplete: () => {
      if (!wrapperEl.querySelector('.upload-progress__item-wrapper')) {
        wrapperEl.remove();
      }
    },
  });
}
