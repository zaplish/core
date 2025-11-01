import { config } from '../config/config';
import { keepInBounds } from '../utils/keep-in-bounds';

/**
 * Variables
 */
let tooltipTimer = null;
let openTooltip = null;

/**
 * Initialize tooltips
 */
export function initTooltips() {
  const tooltipTriggerEls = document.querySelectorAll('[data-tooltip-trigger]');

  tooltipTriggerEls.forEach(tooltipTriggerEl => {
    if (tooltipTriggerEl._tooltipEventAdded) return;
    tooltipTriggerEl._tooltipEventAdded = true;

    const tooltipEl = tooltipTriggerEl.querySelector('[data-tooltip]');
    if (!tooltipEl) return;

    tooltipTriggerEl.addEventListener('mouseenter', () => {
      clearTimeout(tooltipTimer);

      // Close currently open tooltip if it's different
      if (openTooltip && openTooltip !== tooltipEl) {
        closeTooltip(openTooltip);
      }

      // If another tooltip is already open → open immediately
      if (openTooltip && openTooltip !== tooltipEl) {
        openTooltipEl(tooltipEl);
      } else if (!openTooltip) {
        // Otherwise use the delay
        tooltipTimer = setTimeout(() => {
          openTooltipEl(tooltipEl);
        }, config.tooltipDelay);
      }
    });

    tooltipTriggerEl.addEventListener('mouseleave', () => {
      clearTimeout(tooltipTimer);
      closeTooltip(tooltipEl);
    });
  });
}

/**
 * Open a tooltip
 */
function openTooltipEl(tooltipEl) {
  if (openTooltip === tooltipEl) return;

  tooltipEl.classList.add('-show');

  requestAnimationFrame(() => {
    keepInBounds(tooltipEl, { padding: config.tooltipPadding });
    tooltipEl.classList.add('-animate');
  });

  openTooltip = tooltipEl;
}

/**
 * Close a tooltip
 */
function closeTooltip(tooltipEl) {
  clearTimeout(tooltipEl._tooltipCloseTimer);

  tooltipEl.classList.remove('-animate');

  // Remove -show after transition ends
  tooltipEl._tooltipCloseTimer = setTimeout(() => {
    tooltipEl.classList.remove('-show');
    if (openTooltip === tooltipEl) {
      openTooltip = null;
    }
  }, config.defaultTransitionSpeed);
}

/**
 * Close all tooltips
 */
export function closeAllTooltips() {
  const tooltipEls = document.querySelectorAll('[data-tooltip].-show');
  tooltipEls.forEach(tooltipEl => {
    closeTooltip(tooltipEl);
  });
}

/**
 * Adjust tooltip position
 *
 * @param {HTMLElement} tooltipEl
 */
export function adjustTooltipPosition(tooltipEl) {
  keepInBounds(tooltipEl, { padding: config.tooltipPadding });
}
