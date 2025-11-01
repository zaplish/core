/**
 * Adjust element horizontally so it doesn't overflow viewport or container
 *
 * @param {HTMLElement} el - The element to adjust
 * @param {Object} options
 * @param {number} [options.padding=8] - Min spacing from container edges
 * @param {HTMLElement} [options.container=window] - Limit within this element's rect (defaults to viewport)
 */
export function keepInBounds(el, { padding = 4, container = null, attribute = 'marginLeft' } = {}) {
  if (!el) return;

  el.style[attribute] = 0;

  const rect = el.getBoundingClientRect();
  const containerRect = container
    ? container.getBoundingClientRect()
    : { left: 0, right: window.innerWidth };

  let shiftX = 0;

  // Left overflow
  if (rect.left < containerRect.left + padding) {
    const neededShift = containerRect.left + padding - rect.left;

    // Check if right side would still fit after shifting
    if (rect.right + neededShift <= containerRect.right - padding) {
      shiftX = neededShift;
    }
  }

  // Right overflow
  if (rect.right > containerRect.right - padding) {
    const neededShift = containerRect.right - padding - rect.right;

    // Check if left side would still fit after shifting
    if (rect.left + neededShift >= containerRect.left + padding) {
      shiftX = neededShift;
    }
  }

  if (attribute === 'marginRight') {
    shiftX = -shiftX;
  }

  el.style[attribute] = `${shiftX}px`;
}
