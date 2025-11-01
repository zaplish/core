export function hidden({
  id = '',
  name = '',
  value = '',
  className = '',
  onInput = null,
  onChange = null,
} = {}) {
  // Create wrapper
  const wrapper = document.createElement('div');
  wrapper.className = `input__container -hidden ${className}`.trim();

  // Create input
  const inputEl = document.createElement('input');
  inputEl.type = 'hidden';
  if (id) inputEl.id = id;
  if (name) inputEl.name = name;
  inputEl.value = value;

  inputEl.addEventListener('input', () => {
    if (onInput) onInput();
  });

  inputEl.addEventListener('change', () => {
    if (onChange) onChange();
  });

  wrapper.appendChild(inputEl);

  wrapper._inputEl = inputEl;

  return wrapper;
}
