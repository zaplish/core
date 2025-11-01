import { ListService } from './list-service';

export function initList() {
  const wrapper = document.querySelector('[data-list]');
  if (!wrapper) return;

  const key = wrapper.getAttribute('data-list');

  new ListService({
    key,
    wrapper,
  });
}
