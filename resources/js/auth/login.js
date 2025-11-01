import { apiFetch } from '../services/api-fetch';
import { animate } from '../utils/animate';
import { showAuthFormError } from './form';
import { networkErrorText } from '../ui/message';

function initLogin() {
  const submitButton = document.querySelector('[data-login-form-submit-button]');

  if (!submitButton) {
    return;
  }

  submitButton.addEventListener('click', () => {
    const csrfInput = document.querySelector('[data-login-form-input="csrf"]');
    const emailInput = document.querySelector('[data-login-form-input="email"]');
    const passwordInput = document.querySelector('[data-login-form-input="password"]');

    const csrf = csrfInput?.value;
    const email = emailInput?.value.trim();
    const password = passwordInput?.value;

    if (!email || !password) {
      !email && emailInput.classList.add('-error');
      !password && passwordInput.classList.add('-error');
      animate(submitButton, 'shake');
      return;
    }

    submitButton.classList.add('-loading');
    submitButton.disabled = true;

    apiFetch({
      url: '/admin/login',
      method: 'POST',
      headers: {
        'Accept': 'application/json',
      },
      data: { csrf, email, password },
      success: response => {
        if (response.success) {
          window.location.href = response.redirect || '/admin';
        } else {
          const errorText = networkErrorText(response);
          showAuthFormError(submitButton, errorText);
        }
      },
      error: xhr => {
        const errorText = networkErrorText(xhr);
        showAuthFormError(submitButton, errorText);
      },
      complete: () => {
        submitButton.classList.remove('-loading');
        submitButton.disabled = false;
      },
    });
  });
}

export { initLogin };
