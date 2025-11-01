import { networkError } from '../ui/message';
import { removeContainer } from '../utils/animate-remove';
import { saveUserSettings, removeUserSettings } from '../utils/user-settings';

/**
 * Init developer settings
 */
export function initDeveloperSettingsPage() {
  initIgnoreSystemWarnings();
  initResetSystemWarnings();
}

/**
 * Init system warnings
 */
export function initIgnoreSystemWarnings() {
  document.querySelectorAll('[data-remove-warning]').forEach(el => {
    const id = el.dataset.removeWarning;
    const containerEl = el.closest('[data-warning="' + id + '"]');
    if (!containerEl) return;
    const userSettingId = el.dataset.warningId;

    el.addEventListener('click', () => {
      saveUserSettings(
        { [userSettingId]: { [id]: true } },
        response => {
          if (response.success) {
            removeContainer(containerEl);
          } else {
            networkError(response);
          }
        },
        xhr => {
          networkError(xhr);
        }
      );
    });
  });
}

/**
 * Init reset system warnings
 */
export function initResetSystemWarnings() {
  document.querySelectorAll('[data-reset-warnings]').forEach(el => {
    const userSettingId = el.dataset.resetWarnings;
    el.addEventListener('click', () => {
      removeUserSettings(
        { [userSettingId]: true },
        response => {
          if (response.success) {
            window.location.reload();
          } else {
            networkError(response);
          }
        },
        xhr => {
          networkError(xhr);
        }
      );
    });
  });
}
