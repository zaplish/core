/**
 * Init the submit form on enter events
 */
export function initSubmitOnEnter(scope = document) {
  const attributeName = 'data-submit-on-enter';

  // Elements
  const elements = [];

  // Include scope itself if it matches the selector
  if (scope.matches?.('[' + attributeName + ']')) {
    elements.push(scope);
  }

  // Also include all matching descendants
  elements.push(...scope.querySelectorAll('[' + attributeName + ']'));

  elements.forEach(inputEl => {
    inputEl.addEventListener('keydown', ev => {
      if (ev.key === 'Enter') {
        ev.preventDefault();

        let submitButton;

        const attr = inputEl.getAttribute(attributeName);

        if (attr) {
          submitButton = document.querySelector('[data-submit-button="' + attr + '"]');
        } else {
          const form = inputEl.closest('form');

          if (form) {
            submitButton = form.querySelector('[data-submit-button]');
          }
        }
        if (submitButton) {
          submitButton.click();
        }
      }
      inputEl.removeAttribute(attributeName);
    });
  });
}

/**
 * Clear input errors on input
 */
export function initClearErrorOnInput(scope = document) {
  const attributeName = 'data-clear-error-on-input';

  // Elements
  const elements = [];

  // Include the scope itself if it matches
  if (scope.matches?.('[' + attributeName + ']')) {
    elements.push(scope);
  }

  // Include all matching children
  elements.push(...scope.querySelectorAll('[' + attributeName + ']'));

  elements.forEach(inputEl => {
    const clearError = () => {
      inputEl.classList.remove('-error');
    };

    inputEl.addEventListener('input', clearError);
    inputEl.addEventListener('change', clearError);
    inputEl.addEventListener('paste', clearError);
    inputEl.removeAttribute(attributeName);
  });
}

/**
 * Attach search event to input
 */
export function initAttachSearchEvent(inputEl) {
  const attributeName = 'data-attach-search-event';

  // Block when search event is already attached
  if (document.body._searchEventAttached) {
    return;
  }

  // Get input element
  if (!inputEl) {
    inputEl = document.querySelector('[' + attributeName + ']');
  }

  if (!inputEl) {
    return;
  }

  // Attach search event
  document.body._searchEventAttached = true;

  document.addEventListener('keydown', e => {
    const isSearchShortcut = (e.metaKey && e.key === 'f') || (e.ctrlKey && e.key === 'f');
    if (isSearchShortcut) {
      e.preventDefault();
      inputEl.focus();
      inputEl.select();
    }
  });

  inputEl.removeAttribute(attributeName);
}

export function initFormEvents(scope = document) {
  initClearErrorOnInput(scope);
  initSubmitOnEnter(scope);
  initAttachSearchEvent();
}
