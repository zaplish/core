import { getCsrfToken } from '../utils/csrf';

export function apiFetch(options) {
  const {
    url,
    method = 'POST',
    data = null,
    headers = {},
    before,
    complete,
    success,
    error,
    progress,
    isUpload = false,
  } = options;

  const xhr = new XMLHttpRequest();
  xhr.open(method, url, true);

  if (!isUpload) {
    xhr.setRequestHeader('Content-Type', 'application/json');
    xhr.setRequestHeader('Accept', 'application/json');
  }

  // CSRF
  const csrfToken = getCsrfToken();
  if (csrfToken) {
    xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken);
  }

  // Headers
  for (const key in headers) {
    if (headers.hasOwnProperty(key)) {
      xhr.setRequestHeader(key, headers[key]);
    }
  }

  // Progress
  if (isUpload && typeof progress === 'function') {
    xhr.upload.onprogress = function (e) {
      if (e.lengthComputable) {
        const percent = Math.round((e.loaded / e.total) * 100);
        progress(percent, e);
      }
    };
  }

  // Success
  xhr.onload = function () {
    let response;
    try {
      response = JSON.parse(xhr.responseText);
    } catch (e) {
      response = xhr.responseText;
    }

    if (typeof success === 'function') success(response, xhr);
    if (typeof complete === 'function') complete(xhr);
  };

  // Error
  xhr.onerror = function () {
    if (typeof error === 'function') error(xhr);
    if (typeof complete === 'function') complete(xhr);
  };

  // Before
  if (typeof before === 'function') before();

  // Send
  if (isUpload && data instanceof FormData) {
    xhr.send(data);
  } else {
    xhr.send(data ? JSON.stringify(data) : null);
  }

  return xhr;
}
