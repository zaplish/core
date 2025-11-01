import { apiFetch } from '../services/api-fetch';
import { networkError } from '../ui/message';
import { initFormEvents } from './events';
import { input } from './input';
import { getNestedValue } from '../utils/object';
import { showFlashInfo } from '../ui/message';
import { animate } from '../utils/animate';

export class FormService {
  constructor({ key, wrapper }) {
    if (!key || !wrapper) return;

    this.key = key;
    this.wrapper = wrapper;
    this.wrapper._formService = this;

    this.formData = window.formData || null;

    // TODO if form data is missing, get form data then init

    this.init();
  }

  init() {
    this.container = document.createElement('div');
    this.container.className = 'form__container';

    const formConfig = this.formData?.config;

    if (!formConfig) {
      return console.warn('No form config found.');
    }

    const formItems = formConfig?.form || [];
    const item = this.formData?.item || null;
    const texts = this.formData?.texts || {};

    const inputIdContainerEl = input({
      key: formConfig.key,
      source: 'id',
      inputOptions: {
        type: 'hidden',
        name: 'id',
        value: item ? item.id : null,
      },
    });
    this.container.appendChild(inputIdContainerEl);

    formItems.forEach(formItem => {
      if (formItem.skipIf && formItem.skipIf === 'new' && !item) {
        return;
      }

      // Input elements
      switch (formItem.type) {
        case 'input':
          const inputOptions = formItem.inputOptions;

          if (item && formItem.source) {
            inputOptions.value = getNestedValue(item, formItem.source) || null;
          }

          if (!item && inputOptions.requiredIfNew) {
            inputOptions.required = true;
          }

          const inputContainerEl = input({
            key: formConfig.key,
            source: formItem.source,
            label: formItem.label ? resolveText(texts, formItem.label) : null,
            description: formItem.description ? resolveText(texts, formItem.description) : null,
            inputOptions,
            clearErrorOnInput: true,
            onEnter: () => {
              this.saveForm();
            },
          });

          this.container.appendChild(inputContainerEl);
          break;

        // TODO: Block creator
        case 'blocks':
          const blocksContainerEl = document.createElement('div');
          blocksContainerEl.className = 'form-blocks__wrapper';
          blocksContainerEl.innerHTML = 'TODO BLOCKS';
          this.container.appendChild(blocksContainerEl);
          break;

        case 'html':
          const htmlContainerEl = document.createElement('div');
          htmlContainerEl.className = 'form-html__wrapper';
          htmlContainerEl.innerHTML = formItem.content;
          this.container.appendChild(htmlContainerEl);
          break;

        case 'file-info':
          const fileInfoContainerEl = document.createElement('div');
          fileInfoContainerEl.className = 'form-file-info__wrapper';
          fileInfoContainerEl.innerHTML = 'TODO FILE INFO';
          this.container.appendChild(fileInfoContainerEl);
          break;
      }
    });

    this.wrapper.appendChild(this.container);

    initFormEvents();

    // Save form events
    const saveButton = document.querySelector('[data-save-form="' + formConfig.key + '"]');

    if (saveButton) {
      saveButton.addEventListener('click', () => {
        this.saveForm();
      });
    }

    // Save form when pressing CMD + S
    document.addEventListener('keydown', ev => {
      if (ev.metaKey && ev.key === 's') {
        ev.preventDefault();
        this.saveForm();
      }
    });
  }

  saveForm() {
    if (this.saving) return false;

    const formConfig = this.formData?.config || {};
    const key = formConfig.key;
    const saveButton = document.querySelector('[data-save-form="' + key + '"]');

    apiFetch({
      url: '/admin/api/save-form',
      data: {
        key,
        values: getFormData(key),
      },
      before: () => {
        this.saving = true;
        saveButton.disabled = true;
        saveButton.classList.add('-loading');
      },
      complete: () => {
        this.saving = false;
        saveButton.disabled = false;
        saveButton.classList.remove('-loading');
      },
      success: response => {
        if (response.success) {
          animate(saveButton, 'pulseUpSmall');
          response.message && showFlashInfo(response.message);
          document.querySelector(
            '[data-form-value="' + key + '"][data-input-source="id"] input'
          ).value = response.item.id;
          history.pushState(null, '', response.editRoute);
        } else if (response.success === false) {
          animate(saveButton, 'shake');
          response.message ? showFlashInfo(response.message, response.description) : error();
          response.inputErrors && showFormErrors(key, response.inputErrors);
        } else {
          networkError(response);
        }
      },
      error: xhr => {
        networkError(xhr);
      },
    });
  }
}

/**
 * Resolve text
 */
function resolveText(texts, textId) {
  return getNestedValue(texts, textId) ?? textId;
}

/**
 * Get form data
 */
function getFormData(key) {
  const data = {};

  const fields = document.querySelectorAll('[data-form-value="' + key + '"]');
  fields.forEach(wrapper => {
    const source = wrapper.dataset.inputSource;
    const type = wrapper.dataset.inputType;

    // Support various input types
    let value = null;

    if (type === 'textfield' || type === 'textarea' || type === 'email' || type === 'number') {
      const input = wrapper.querySelector('input, textarea');
      value = input?.value || '';
    } else if (type === 'checkbox') {
      const input = wrapper.querySelector('input[type="checkbox"]');
      value = input?.checked || false;
    } else if (type === 'select') {
      const select = wrapper.querySelector('select');
      if (select?.multiple) {
        value = Array.from(select.selectedOptions).map(opt => opt.value);
      } else {
        value = select?.value || '';
      }
    } else {
      const input = wrapper.querySelector('input, textarea, select');
      value = input?.value || '';
    }

    data[source] = value;
  });

  return data;
}

/**
 * Show form errors
 */
function showFormErrors(key, errors = {}) {
  Object.entries(errors).forEach(([source, messages]) => {
    const element = document.querySelector(
      '[data-form-value="' + key + '"][data-input-source="' + source + '"]'
    );
    if (!element) return;

    element.querySelector('.input__container')?.classList.add('-error');

    const existingError = element.querySelector('.input__error');
    if (existingError) {
      existingError.remove();
    }

    const errorEl = document.createElement('div');
    errorEl.className = 'input__error';
    errorEl.innerHTML = messages[0];

    element.appendChild(errorEl);
  });
}
