import { apiFetch } from '../services/api-fetch';
import { animate } from '../utils/animate';
import { showAuthFormError, showAuthFormSuccess } from './form';
import { networkErrorText } from '../ui/message';

function initNewPassword() {
  const submitButton = document.querySelector('[data-new-password-form-submit-button]');

  if (!submitButton) {
    return;
  }

  submitButton.addEventListener('click', () => {
    const csrfInput = document.querySelector('[data-new-password-form-input="csrf"]');
    const userIdInput = document.querySelector('[data-new-password-form-input="user-id"]');
    const resetPasswordHashInput = document.querySelector(
      '[data-new-password-form-input="reset-password-hash"]'
    );
    const passwordInput = document.querySelector('[data-new-password-form-input="password"]');
    const passwordRepeatInput = document.querySelector(
      '[data-new-password-form-input="password-repeat"]'
    );

    const csrf = csrfInput?.value;
    const userId = userIdInput?.value;
    const resetPasswordHash = resetPasswordHashInput?.value;
    const password = passwordInput?.value;
    const passwordRepeat = passwordRepeatInput?.value;

    if (!password || !passwordRepeat || !userId || !resetPasswordHash) {
      !password && passwordInput.classList.add('-error');
      !passwordRepeat && passwordRepeatInput.classList.add('-error');
      animate(submitButton, 'shake');
      return;
    }

    submitButton.classList.add('-loading');
    submitButton.disabled = true;
    passwordInput.disabled = true;
    passwordRepeatInput.disabled = true;

    apiFetch({
      url: '/admin/new-password',
      method: 'POST',
      headers: {
        'Accept': 'application/json',
      },
      data: { csrf, userId, resetPasswordHash, password, passwordRepeat },
      success: response => {
        if (response.success) {
          response.message && showAuthFormSuccess(submitButton, response.message);
        } else {
          const errorText = networkErrorText(response);
          response.message && showAuthFormError(submitButton, errorText);
          submitButton.disabled = false;
          passwordInput.disabled = false;
          passwordRepeatInput.disabled = false;
        }
      },
      error: xhr => {
        const errorText = networkErrorText(xhr);
        showAuthFormError(submitButton, errorText);
        submitButton.disabled = false;
        passwordInput.disabled = false;
        passwordRepeatInput.disabled = false;
      },
      complete: () => {
        submitButton.classList.remove('-loading');
      },
    });
  });
}

export { initNewPassword };
