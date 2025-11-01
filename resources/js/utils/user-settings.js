import { apiFetch } from '../services/api-fetch';

/**
 * Save user settings
 */
export function saveUserSettings(data = {}, success, error) {
  return apiFetch({
    url: '/admin/api/save-user-settings',
    method: 'POST',
    headers: {
      Accept: 'application/json',
    },
    data: { data },
    success: response => {
      success && success(response);
      return response;
    },
    error: xhr => {
      error && error(xhr);
      return xhr;
    },
  });
}

/**
 * Remove user settings
 */
export function removeUserSettings(data = {}, success, error) {
  return apiFetch({
    url: '/admin/api/remove-user-settings',
    method: 'POST',
    headers: {
      Accept: 'application/json',
    },
    data: { data },
    success: response => {
      success && success(response);
      return response;
    },
    error: xhr => {
      error && error(xhr);
      return xhr;
    },
  });
}