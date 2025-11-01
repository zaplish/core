import { apiFetch } from '../services/api-fetch';
import { animate } from '../utils/animate';
import { showAuthFormError, showAuthFormSuccess } from './form';
import { networkErrorText } from '../ui/message';

function initResetPassword() {
  const submitButton = document.querySelector('[data-reset-password-form-submit-button]');

  if (!submitButton) {
    return;
  }

  submitButton.addEventListener('click', () => {
    const csrfInput = document.querySelector('[data-reset-password-form-input="csrf"]');
    const emailInput = document.querySelector('[data-reset-password-form-input="email"]');

    const csrf = csrfInput?.value;
    const email = emailInput?.value.trim();

    if (!email) {
      !email && emailInput.classList.add('-error');
      animate(submitButton, 'shake');
      return;
    }

    submitButton.classList.add('-loading');
    submitButton.disabled = true;
    emailInput.disabled = true;

    apiFetch({
      url: '/admin/reset-password',
      method: 'POST',
      headers: {
        'Accept': 'application/json',
      },
      data: { csrf, email },
      success: response => {
        if (response.success) {
          const errorText = networkErrorText(response);
          response.message && showAuthFormSuccess(submitButton, errorText);
        } else {
          const errorText = networkErrorText(response);
          showAuthFormError(submitButton, errorText);
          submitButton.disabled = false;
          emailInput.disabled = false;
        }
      },
      error: xhr => {
        const errorText = networkErrorText(xhr);
        showAuthFormError(submitButton, errorText);
        submitButton.disabled = false;
        emailInput.disabled = false;
      },
      complete: () => {
        submitButton.classList.remove('-loading');
      },
    });
  });
}

export { initResetPassword };
