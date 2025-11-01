/**
 * Removes a container with a fade-out + collapse animation.
 * @param {HTMLElement} outerEl - The outer container element
 * @param {number} fadeDuration - Fade out time (ms)
 * @param {number} collapseDuration - Height collapse time (ms)
 */
export function removeContainer(
  outerEl,
  { fadeDuration = 400, collapseDuration = 400, onComplete = null } = {}
) {
  if (!outerEl) return;

  const innerEl = outerEl.firstElementChild;
  if (!innerEl) return;

  // Fade out inner
  innerEl.style.transition = `opacity ${fadeDuration}ms`;
  innerEl.style.opacity = 0;

  setTimeout(() => {
    if (!outerEl) return;

    // Collapse outer
    const startHeight = outerEl.scrollHeight;

    // Set fixed height before transition
    outerEl.style.height = `${startHeight}px`;
    outerEl.style.overflow = 'hidden';

    // Force browser to paint this state
    outerEl.getBoundingClientRect();

    // Add transition
    outerEl.style.transition = `height ${collapseDuration}ms ease`;

    // Collapse to 0
    requestAnimationFrame(() => {
      outerEl.style.height = '0px';
    });

    // Remove on complete
    setTimeout(() => {
      if (!outerEl) return;
      outerEl.remove();
      onComplete?.();
    }, collapseDuration);
  }, fadeDuration);
}
