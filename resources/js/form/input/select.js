import { clearInputError } from "../input";

export function select({
  id = '',
  name = '',
  value = '',
  placeholder = '',
  options = [],
  restrictOptions = {},
  required = false,
  disabled = false,
  multiple = false,
  size = 'default',
  className = '',
  icon = '',
  onChange = null,
  onFocus = null,
  onBlur = null,
  clearErrorOnInput = false,
  onEnter = null,
} = {}) {
  // Wrapper
  const wrapper = document.createElement('div');
  wrapper.className = `input__container -select -size-${size} ${className}`.trim();

  if (required) wrapper.classList.add('-required');
  if (disabled) wrapper.classList.add('-disabled');
  if (multiple) wrapper.classList.add('-multiple');

  // Pointer icon
  const painterIconEl = document.createElement('div');
  painterIconEl.className = 'textfield__icon -right icon select__pointer-icon';
  painterIconEl.textContent = 'keyboard_arrow_down';
  wrapper.appendChild(painterIconEl);

  // Optional icon
  let iconEl = null;
  if (icon) {
    wrapper.classList.add('-has-icon');
    iconEl = document.createElement('div');
    iconEl.className = 'textfield__icon -left icon';
    iconEl.textContent = icon;
    wrapper.appendChild(iconEl);
  }

  // Create select
  const selectEl = document.createElement('select');
  selectEl.className = 'textfield';
  if (id) selectEl.id = id;
  if (name) selectEl.name = name;
  selectEl.disabled = disabled;

  // Optional placeholder
  if (placeholder) {
    const placeholderOption = document.createElement('option');
    placeholderOption.disabled = true;
    placeholderOption.selected = !value;
    placeholderOption.hidden = true;
    placeholderOption.textContent = placeholder;
    selectEl.appendChild(placeholderOption);
  }

  // Get current user role
  const userRole = window.app?.auth?.role || null;

  options.forEach(opt => {
    const restrictedTo = restrictOptions[opt.value];

    const isRestricted = Array.isArray(restrictedTo);
    const isAllowed = !isRestricted || restrictedTo.includes(userRole);
    const isSelected = opt.value == value;

    // Skip completely if user isn't allowed and it's not selected
    if (!isAllowed && !isSelected) return;

    const optEl = document.createElement('option');
    optEl.value = opt.value;
    optEl.textContent = opt.label;

    if (isSelected) optEl.selected = true;

    if (!isAllowed && isSelected) selectEl.disabled = true;

    selectEl.appendChild(optEl);
  });

  // Disabled status
  if (selectEl.disabled) {
    wrapper.classList.add('-disabled');
  }

  const checkHasValue = () => {
    if (selectEl.value) {
      wrapper.classList.add('-has-value');
    } else {
      wrapper.classList.remove('-has-value');
    }
  };

  // Focus / blur styling
  selectEl.addEventListener('focus', e => {
    wrapper.classList.add('-has-focus');
    if (onFocus) onFocus(e);
  });

  selectEl.addEventListener('blur', e => {
    wrapper.classList.remove('-has-focus');
    if (onBlur) onBlur(e);
  });

  selectEl.addEventListener('change', e => {
    clearErrorOnInput && clearInputError(selectEl);
    checkHasValue();
    selectEl.blur();
    if (onChange) onChange(e);
  });

  selectEl.addEventListener('keydown', e => {
    if (e.key === 'Enter' && onEnter) {
      onEnter();
    }
  });

  checkHasValue();

  wrapper.appendChild(selectEl);
  wrapper._selectEl = selectEl;

  return wrapper;
}
