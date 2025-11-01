import { clearInputError } from "../input";

export function textarea({
  id = '',
  name = '',
  value = '',
  placeholder = '',
  autocomplete = '',
  maxlength = null,
  rows = 3,
  required = false,
  disabled = false,
  readonly = false,
  size = 'default',
  className = '',
  onInput = null,
  onChange = null,
  onFocus = null,
  onBlur = null,
  clearErrorOnInput = false,
  onEnter = null,
} = {}) {
  // Create wrapper
  const wrapper = document.createElement('div');
  wrapper.className = `input__container -textarea -size-${size} ${className}`.trim();

  if (required) wrapper.classList.add('-required');
  if (disabled) wrapper.classList.add('-disabled');
  if (readonly) wrapper.classList.add('-readonly');

  // Create textarea
  const inputEl = document.createElement('textarea');

  // Basic attributes
  if (id) inputEl.id = id;
  if (name) inputEl.name = name;
  if (placeholder) inputEl.placeholder = placeholder;
  if (autocomplete) inputEl.autocomplete = autocomplete;
  if (maxlength) inputEl.maxLength = maxlength;
  inputEl.rows = rows;
  inputEl.value = value;
  inputEl.disabled = disabled;
  inputEl.readOnly = readonly;

  // Classes
  inputEl.className = `textfield textfield--textarea ${className}`.trim();

  // Handle focus/blur styling
  inputEl.addEventListener('focus', e => {
    wrapper.classList.add('-has-focus');
    if (onFocus) onFocus(e);
  });

  inputEl.addEventListener('blur', e => {
    wrapper.classList.remove('-has-focus');
    if (onBlur) onBlur(e);
  });

  // Other listener
  const checkHasValue = () => {
    if (inputEl.value) {
      wrapper.classList.add('-has-value');
    } else {
      wrapper.classList.remove('-has-value');
    }
  };

  inputEl.addEventListener('input', () => {
    clearErrorOnInput && clearInputError(inputEl);
    checkHasValue();
    if (onInput) onInput();
  });

  inputEl.addEventListener('change', () => {
    clearErrorOnInput && clearInputError(inputEl);
    checkHasValue();
    if (onChange) onChange();
  });

  inputEl.addEventListener('keydown', e => {
    if (e.key === 'Enter' && onEnter) {
      onEnter();
    }
  });

  checkHasValue();

  wrapper.appendChild(inputEl);

  wrapper._inputEl = inputEl;

  return textarea;
}
