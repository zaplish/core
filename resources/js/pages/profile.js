import { confirmModal } from '../ui/modal';
import { apiFetch } from '../services/api-fetch';
import { networkError, networkErrorText } from '../ui/message';
import { password } from '../form/input/password';
import { animate } from '../utils/animate';
import { __ } from '../utils/locale';

let deleteAccountRequestRunning;

export function initProfilePage() {
  document.querySelectorAll('[data-delete-account-modal]').forEach(triggerEl => {
    if (triggerEl._deleteAccountModalEventAdded) return;
    triggerEl.addEventListener('click', () => {
      triggerEl._deleteAccountModalEventAdded = true;

      const modalTexts = window.deleteAccountModalTexts;

      confirmModal({
        title: modalTexts.title,
        text: modalTexts.text,
        cancelButtonText: modalTexts.cancelButtonText,
        submitButtonText: modalTexts.submitButtonText,
        onOpen: function (modalEl, sourceEl) {
          modalEl.classList.add('delete-account__modal');
          const passwordEl = password({
            name: 'password',
            autocomplete: 'new-password',
            placeholder: modalTexts.textfieldPlaceholder,
          });
          const modalContainerEl = modalEl.querySelector('.confirm-modal__container');
          const modalFooterEl = modalEl.querySelector('.modal__footer');
          modalContainerEl.insertBefore(passwordEl, modalFooterEl);

          const passwordErrorEl = document.createElement('div');
          passwordErrorEl.classList.add('delete-account__modal-error');
          modalContainerEl.insertBefore(passwordErrorEl, passwordEl);
        },
        submitCallback: (modalEl, submitBtn) => {
          if (deleteAccountRequestRunning) return;

          const passwordEl = modalEl.querySelector('.input__container input');
          const password = passwordEl.value;

          apiFetch({
            url: '/admin/delete-account',
            data: {
              password
            },
            headers: {
              'Accept': 'application/json',
            },
            before: () => {
              deleteAccountRequestRunning = true;
              submitBtn.classList.add('-loading');
              submitBtn.disabled = true;
            },
            complete: () => {
              deleteAccountRequestRunning = false;
              submitBtn.classList.remove('-loading');
              submitBtn.disabled = false;
            },
            success: response => {
              if (response.success) {
                window.location.href = response.redirect || '/admin';
              } else if (response.success === false) {
                const errorEl = modalEl.querySelector('.delete-account__modal-error');
                errorEl.innerHTML = networkErrorText(response);
                errorEl.classList.add('-active');
                animate(submitBtn, 'shake');
              } else {
                networkError(response);
              }
            },
            error: xhr => {
              networkError(xhr);
            },
          });
        },
      });
    });
  });
}
