import { clearInputError } from '../input';

export function textfield({
  type = 'text',
  id = '',
  name = '',
  value = '',
  placeholder = '',
  autocomplete = null,
  maxlength = null,
  disabled = false,
  required = false,
  readonly = false,
  size = 'default',
  className = '',
  attributes = null,
  icon = '',
  iconClass = null,
  iconEvent = null,
  iconRight = null,
  iconRightClass = null,
  iconRightEvent = null,
  hasClear = false,
  clearOnEsc = false,
  onInput = null,
  onChange = null,
  onFocus = null,
  onBlur = null,
  clearErrorOnInput = false,
  onEnter = null,
} = {}) {
  // Create wrapper
  const wrapper = document.createElement('div');
  wrapper.className = `input__container -textfield -size-${size} ${className}`.trim();

  if (hasClear) {
    iconRight = 'close';
    iconRightClass = 'textfield__clear-button';
    iconRightEvent = inputEl => {
      inputEl.value = '';
      inputEl.dispatchEvent(new Event('change'));
      inputEl.dispatchEvent(new Event('input'));
    };
  }

  // Wrapper classes
  if (required) wrapper.classList.add('-required');
  if (disabled) wrapper.classList.add('-disabled');
  if (readonly) wrapper.classList.add('-readonly');
  if (icon) wrapper.classList.add('-has-icon-left');
  if (iconRight) wrapper.classList.add('-has-icon-right');
  if (icon || iconRight) wrapper.classList.add('-has-icon');

  // Create input
  const inputEl = document.createElement('input');
  inputEl.className = 'textfield';
  inputEl.type = type;
  if (id) inputEl.id = id;
  if (name) inputEl.name = name;
  if (placeholder) inputEl.placeholder = placeholder;
  if (autocomplete) inputEl.autocomplete = autocomplete;
  if (maxlength) inputEl.maxLength = maxlength;
  inputEl.value = value;
  inputEl.disabled = disabled;
  inputEl.readOnly = readonly;

  // Attributes
  if (attributes) {
    Object.entries(attributes).forEach(([key, value]) => {
      inputEl.setAttribute(key, value);
    });
  }

  // Optional icon
  let iconEl = null;
  if (icon) {
    iconEl = document.createElement('div');
    iconEl.className =
      'textfield__icon -left icon' + (iconClass ? ' ' + iconClass : '') + ' -icon-' + icon;
    iconEl.textContent = icon;
    iconEvent && iconEl.addEventListener('click', () => iconEvent(inputEl, wrapper));
    wrapper.appendChild(iconEl);
  }

  // Optional icon right
  if (iconRight) {
    const clearButtonEl = document.createElement('div');
    clearButtonEl.className = `textfield__icon -right icon ${iconRightClass}`;
    hasClear && clearButtonEl.classList.add('textfield__clear-button');
    clearButtonEl.textContent = iconRight;
    iconRightEvent &&
      clearButtonEl.addEventListener('click', () => iconRightEvent(inputEl, wrapper));
    wrapper.appendChild(clearButtonEl);
  }

  // Handle focus/blur styling
  inputEl.addEventListener('focus', e => {
    wrapper.classList.add('-has-focus');
    if (onFocus) onFocus(e);
  });

  inputEl.addEventListener('blur', e => {
    wrapper.classList.remove('-has-focus');
    if (onBlur) onBlur(e);
  });

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

    if (clearOnEsc && e.key === 'Escape') {
      inputEl.value = '';
      inputEl.dispatchEvent(new Event('change'));
      inputEl.dispatchEvent(new Event('input'));
    }
  });

  checkHasValue();

  wrapper.appendChild(inputEl);

  wrapper._inputEl = inputEl;

  return wrapper;
}
