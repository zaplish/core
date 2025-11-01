import { __ } from '../utils/locale';
import { animate } from '../utils/animate';

function showAuthFormMessage(type = 'error', submitButton, message) {
  const formMessageEl = document.querySelector('.auth__form-message');
  formMessageEl.innerHTML = message || __('error');
  formMessageEl.classList[type === 'success' ? 'add' : 'remove']('-success');
  formMessageEl.classList[type === 'error' ? 'add' : 'remove']('-error');
  formMessageEl.classList.add('-active');
  type === 'error' && animate(submitButton, 'shake');
}

function showAuthFormError(submitButton, message) {
  showAuthFormMessage('error', submitButton, message);
}

function showAuthFormSuccess(submitButton, message) {
  showAuthFormMessage('success', submitButton, message);
}

export { showAuthFormError, showAuthFormSuccess };
