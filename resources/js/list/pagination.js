export function renderPagination({ current_page, last_page, inputPlaceholderText }, onPageChange) {
  const wrapper = document.createElement('div');
  wrapper.className = 'pagination__container';

  if (last_page > 1) {
    // Prev button
    const prev = document.createElement('button');
    prev.classList.add('pagination__button', 'button', '-small', '-icon', '-selectable');
    prev.innerHTML = '<div class="icon">chevron_left</div>';
    prev.disabled = current_page === 1;
    prev.addEventListener('click', () => onPageChange(current_page - 1));
    wrapper.appendChild(prev);

    // First page button
    const first = document.createElement('button');
    first.classList.add('pagination__button', 'button', '-small', '-selectable');
    first.textContent = '1';
    if (current_page === 1) first.classList.add('-active');
    first.addEventListener('click', () => onPageChange(1));
    wrapper.appendChild(first);

    if (last_page == 3) {
      const mid = document.createElement('button');
      mid.classList.add('pagination__button', 'button', '-small', '-selectable');
      mid.textContent = '2';
      if (current_page === 2) mid.classList.add('-active');
      mid.addEventListener('click', () => onPageChange(2));
      wrapper.appendChild(mid);
    } else if (last_page > 3) {
      const input = document.createElement('input');
      input.type = 'number';
      input.value = current_page !== 1 && current_page != last_page ? current_page : '';
      input.min = 1;
      input.max = last_page;
      input.step = 1;
      input.placeholder = inputPlaceholderText;
      input.className = 'pagination__input textfield -small';
      input.addEventListener('change', () => {
        const val = Math.max(1, Math.min(last_page, parseInt(input.value, 10)));
        if (!isNaN(val)) onPageChange(val);
      });
      wrapper.appendChild(input);
    }

    // Last page button
    const last = document.createElement('button');
    last.classList.add('pagination__button', 'button', '-small', '-selectable');
    last.textContent = String(last_page);
    if (current_page === last_page) last.classList.add('-active');
    last.addEventListener('click', () => onPageChange(last_page));
    wrapper.appendChild(last);

    // Next button
    const next = document.createElement('button');
    next.classList.add('pagination__button', 'button', '-small', '-icon', '-selectable');
    next.innerHTML = '<div class="icon">chevron_right</div>';
    next.disabled = current_page === last_page;
    next.addEventListener('click', () => onPageChange(current_page + 1));
    wrapper.appendChild(next);
  }

  return wrapper;
}
