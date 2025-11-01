import { config } from '../config/config';
import { keepInBounds } from '../utils/keep-in-bounds';

// Variables
const menuTimeouts = {};
const menuHandlers = {};

/**
 * Initialize the menus
 */
export function initMenus() {
  document.querySelectorAll('[data-toggle-menu]').forEach(triggerEl => {
    if (triggerEl._menuEventAdded) return;
    triggerEl._menuEventAdded = true;
    triggerEl.addEventListener('click', () => {
      const id = triggerEl.getAttribute('data-toggle-menu');
      removeMenuHandler(id);
      menuIsOpen(id) ? closeMenu(id) : openMenu(id);
    });
  });
}

/**
 * Check if the menu is open
 *
 * @param {*} id
 * @returns
 */
export function menuIsOpen(id) {
  const menuEl = document.querySelector(`[data-menu="${id}"]`);
  return menuEl?.classList.contains('-open');
}

/**
 * Open the menu
 *
 * @param {*} id
 * @param {*} triggerId
 * @returns
 */
export function openMenu(id, triggerId = null) {
  const triggerEl = document.querySelector(`[data-toggle-menu="${triggerId || id}"]`);
  const menuEl = document.querySelector(`[data-menu="${id}"]`);
  if (!triggerEl || !menuEl) return;

  triggerEl.classList.add('-active');
  menuEl.classList.add('-open', '-show');

  if (menuEl.onMenuOpen) {
    menuEl.onMenuOpen(id, triggerId);
  }

  if (menuEl.keepInBounds) {
    menuEl.keepInBounds(menuEl);
  } else {
    keepInBounds(menuEl, { padding: config.menuPadding });
  }

  adjustPosition(menuEl, triggerEl);

  if (menuTimeouts['closeMenuTimeout-' + id]) clearTimeout(menuTimeouts['closeMenuTimeout-' + id]);

  requestAnimationFrame(() => {
    menuEl.classList.add('-animate-start');

    requestAnimationFrame(() => {
      menuEl.classList.add('-animate');
      menuEl.classList.remove('-animate-start');

      removeMenuHandler(id);

      if (!menuEl.ignoreClickOutside) {
        const handler = ev => {
          if (!menuEl.contains(ev.target) && !triggerEl.contains(ev.target)) {
            closeMenu(id);
          }
        };

        document.addEventListener('click', handler);
        menuHandlers[id] = handler;
      }
    });
  });
}

/**
 * Close the menu
 *
 * @param {*} id
 * @param {*} triggerId
 * @returns
 */
export function closeMenu(id, triggerId = null) {
  const triggerEl = document.querySelector(`[data-toggle-menu="${triggerId || id}"]`);
  const menuEl = document.querySelector(`[data-menu="${id}"]`);
  if (!menuEl || !menuEl.classList.contains('-open')) return;

  if (menuEl.onMenuClose) {
    menuEl.onMenuClose(id, triggerId);
  }

  triggerEl?.classList.remove('-active');
  menuEl.classList.remove('-open', '-animate');
  menuEl.classList.add('-closing');

  menuTimeouts['closeMenuTimeout-' + id] = setTimeout(() => {
    menuEl.classList.remove('-show', '-animate-start', '-closing');
    if (menuEl.onMenuCloseComplete) {
      menuEl.onMenuCloseComplete(id, triggerId);
    }
  }, config.defaultTransitionSpeed);

  removeMenuHandler(id);
}

/**
 * Remove the menu handler
 *
 * @param {*} id
 */
function removeMenuHandler(id) {
  if (menuHandlers[id]) {
    document.removeEventListener('click', menuHandlers[id]);
    delete menuHandlers[id];
  }
}

/**
 * Ensure the menu fits in the viewport
 *
 * @param {HTMLElement} menuEl
 * @param {HTMLElement} triggerEl
 */
function adjustPosition(menuEl, triggerEl) {
  if (!menuEl.dataset.flip) return;

  const menuSpacing = parseInt(menuEl.dataset.flip) || 8;
  menuEl.classList.remove('-flipped');

  const menuRect = menuEl.getBoundingClientRect();
  const triggerRect = triggerEl.getBoundingClientRect();
  const viewportHeight = window.innerHeight;

  const spaceBelow = viewportHeight - triggerRect.bottom - menuSpacing - config.menuPadding;
  const spaceAbove = triggerRect.top - menuSpacing - config.menuPadding;

  if (menuRect.height > spaceBelow && spaceAbove > spaceBelow) {
    menuEl.classList.add('-flipped');
  }
}
