import { FormService } from './form-service';

export function initForm() {
  const wrapper = document.querySelector('[data-form]');
  if (!wrapper) return;

  const key = wrapper.getAttribute('data-form');

  new FormService({
    key,
    wrapper,
  });
}
