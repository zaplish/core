import { hidden } from './input/hidden';
import { textfield } from './input/textfield';
import { textarea } from './input/textarea';
import { password } from './input/password';
import { select } from './input/select';

const inputTypes = {
  hidden,
  textfield,
  textarea,
  password,
  select,
};

export function input({
  key = null,
  source = null,
  label = null,
  description = null,
  inputEl = null,
  inputOptions = {},
  clearErrorOnInput = false,
  onEnter = null,
}) {
  const wrapperEl = document.createElement('div');
  wrapperEl.className = 'input__wrapper';

  if (key) {
    wrapperEl.dataset.formValue = key;
  }

  if (source) {
    wrapperEl.dataset.inputSource = source;
  }

  if (inputOptions && inputOptions.name) {
    wrapperEl.dataset.inputName = inputOptions.name;
  }

  if (inputOptions && inputOptions.type) {
    wrapperEl.dataset.inputType = inputOptions.type;
  }

  if (label) {
    const labelEl = document.createElement('label');
    labelEl.className = 'input__label';
    labelEl.innerHTML = label;

    if (inputOptions.id) {
      labelEl.setAttribute('for', inputOptions.id);
    }

    wrapperEl.appendChild(labelEl);
  }

  if (inputEl) {
    wrapperEl.appendChild(inputEl);
  } else if (inputOptions) {
    const { type } = inputOptions;
    const factory = inputTypes[type];
    inputOptions.clearErrorOnInput = clearErrorOnInput;
    inputOptions.onEnter = onEnter;

    if (factory) {
      const factoryInputEl = factory(inputOptions);
      wrapperEl.appendChild(factoryInputEl);
    }
  }

  if (description) {
    const descEl = document.createElement('div');
    descEl.className = 'input__description';
    descEl.innerHTML = description;
    wrapperEl.appendChild(descEl);
  }

  return wrapperEl;
}

/**
 * Remove error from input element
 */
export function clearInputError(inputEl) {
  const wrapperEl = inputEl.closest('.input__wrapper');
  const containerEl = inputEl.closest('.input__container');

  if (containerEl) {
    containerEl.classList.remove('-error');
  }

  if (wrapperEl) {
    const errorEl = wrapperEl.querySelector('.input__error');
    errorEl && errorEl.remove();
  }
}
