export function flipSwap(el, direction = 'down') {
  const sibling = direction === 'down' ? el.nextElementSibling : el.previousElementSibling;
  if (!sibling) return;

  const elRect = el.getBoundingClientRect();
  const siblingRect = sibling.getBoundingClientRect();

  // Swap in DOM
  if (direction === 'down') {
    el.parentNode.insertBefore(sibling, el);
  } else {
    el.parentNode.insertBefore(el, sibling);
  }

  // New positions
  const elNewRect = el.getBoundingClientRect();
  const siblingNewRect = sibling.getBoundingClientRect();

  const elInvertY = elRect.top - elNewRect.top;
  const siblingInvertY = siblingRect.top - siblingNewRect.top;

  // Apply FLIP animation to both
  [el, sibling].forEach((element, i) => {
    const invert = i === 0 ? elInvertY : siblingInvertY;

    element.style.transition = 'none';
    element.style.transform = `translateY(${invert}px)`;

    requestAnimationFrame(() => {
      element.style.transition = 'transform 480ms ease';
      element.style.transform = '';
    });
  });

  updateBlockFlipButtons();
}

export function updateBlockFlipButtons() {
  document.querySelectorAll('.block__wrapper .block__action.-up, .block__wrapper .block__action.-down').forEach(el => el.classList.remove('-disabled'));
  document.querySelector('.block__wrapper:first-child .block__action.-up')?.classList.add('-disabled');
  document.querySelector('.block__wrapper:last-child .block__action.-down')?.classList.add('-disabled');
}
