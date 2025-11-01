import { resolveText } from '../../utils/text';
import { menuIsOpen, closeMenu, openMenu,  } from '../../ui/menu';
import { confirmModal, closeConfirmModal } from '../../ui/modal';
import { apiFetch } from '../../services/api-fetch';
import { getListParams } from '../list-service';
import { networkError, success } from '../../ui/message';

/**
 * Get the multiselect toggler element
 */
export const getMultiselectTogglerEl = (listService, classNames = '') => {
  const columnMultiselectIconEl = document.createElement('div');
  columnMultiselectIconEl.classList.add('list__multiselect-icon', 'icon', 'no-select');
  classNames && columnMultiselectIconEl.classList.add(...classNames.split(' '));
  columnMultiselectIconEl.innerText = 'check_box_outline_blank';
  columnMultiselectIconEl.setAttribute('data-toggle-multiselect', '');
  columnMultiselectIconEl.addEventListener('click', () => {
    const multiselectAllEls = listService.wrapper.querySelectorAll('.list-item__container');
    const multiselectSelectedEls = listService.wrapper.querySelectorAll(
      '.list-item__container[data-is-selected]'
    );
    const multiselectNotSelectedEls = listService.wrapper.querySelectorAll(
      '.list-item__container:not([data-is-selected])'
    );

    let triggerEls;

    if (
      multiselectSelectedEls.length === 0 ||
      multiselectAllEls.length === multiselectSelectedEls.length
    ) {
      triggerEls = multiselectAllEls;
    } else {
      triggerEls = multiselectNotSelectedEls;
    }

    triggerEls.forEach(container => {
      const multiselectEl = container.querySelector('.list__multiselect-icon');
      multiselectEl && multiselectEl.click();
    });

    updateMultiselect(listService);
  });

  return columnMultiselectIconEl;
};

/**
 * Get the multiselect menu element
 */
export const getMultiselectMenuEl = listService => {
  const listConfig = listService.listData.config;
  const listTexts = listService.listData.texts;
  const onTrashPage = listService.onTrashPage();

  const multiselectMenuEl = document.createElement('div');
  multiselectMenuEl.classList.add(
    'list-multiselect__menu',
    'menu-overlay__wrapper',
    '-secondary-links',
    '-multiselect',
  );
  onTrashPage && multiselectMenuEl.classList.add('-trash');
  multiselectMenuEl.dataset.menu = 'multiselect' + (onTrashPage ? '-trash' : '');

  const multiselectMenuLinksEl = document.createElement('div');
  multiselectMenuLinksEl.classList.add('menu-overlay__links');
  multiselectMenuEl.append(multiselectMenuLinksEl);

  const multiselectActions = [];

  // Restore action
  if (onTrashPage) {
    multiselectActions.push({
      action: 'restore',
      icon: 'restore_from_trash',
      text: 'multiselect.actionRestore',
      callback: () => {
        multiSelectRequest(listService, {
          url: '/admin/api/restore',
          success: response => {
            closeMenu('multiselect-trash', 'multiselect');
            listService.listData = response.listData;
            listService.render();
            success(response.message);
          },
        })();
      },
    });
  }

  // Not on trah page
  if (!onTrashPage) {
    // Activate action
    if (listConfig.hasMultiSelect?.includes('activate')) {
      multiselectActions.push({
        action: 'activate',
        icon: 'toggle_on',
        text: 'multiselect.actionActivate',
        callback: () => {
          multiSelectRequest(listService, {
            url: '/admin/api/toggle',
            params: {
              action: 'activate',
            },
            success: (response, ids) => {
              closeMenu('multiselect');
              ids.forEach(id => {
                const itemContainerEl = document.querySelector(
                  `.list-item__container[data-id="${id}"]`
                );
                const actionIconEl = itemContainerEl?.querySelector(
                  '.list__action.-type-toggle .list__action-icon'
                );
                if (!itemContainerEl || !actionIconEl) return;
                actionIconEl.innerHTML = 'toggle_on';
                itemContainerEl.classList.remove('-inactive');
              });
              deselectAll(listService);
              success(response.message);
            },
          })();
        },
      });
    }

    // Deactivate action
    if (listConfig.hasMultiSelect?.includes('deactivate')) {
      multiselectActions.push({
        action: 'deactivate',
        icon: 'toggle_off',
        text: 'multiselect.actionDeactivate',
        callback: () => {
          multiSelectRequest(listService, {
            url: '/admin/api/toggle',
            params: {
              action: 'deactivate',
            },
            success: (response, ids) => {
              closeMenu('multiselect');
              ids.forEach(id => {
                const itemContainerEl = document.querySelector(
                  `.list-item__container[data-id="${id}"]`
                );
                const actionIconEl = itemContainerEl?.querySelector(
                  '.list__action.-type-toggle .list__action-icon'
                );
                if (!itemContainerEl || !actionIconEl) return;
                actionIconEl.innerHTML = 'toggle_off';
                itemContainerEl.classList.add('-inactive');
              });
              deselectAll(listService);
              success(response.message);
            },
          })();
        },
      });
    }
  }

  // Delete action
  if (
    onTrashPage ||
    listConfig.hasMultiSelect?.includes('delete') ||
    listConfig.hasMultiSelect?.includes('force-delete')
  ) {
    const forceDelete =
      onTrashPage ||
      listConfig.hasMultiSelect?.includes('force-delete') ||
      !listConfig.hasSoftDelete;

    multiselectActions.push({
      action: forceDelete ? 'force-delete' : 'delete',
      icon: forceDelete ? 'delete_forever' : 'delete',
      text: 'multiselect.action' + (forceDelete ? 'ForceDelete' : 'Delete'),
      callback: () => {
        confirmModal({
          title: listTexts.deleteModal.title,
          text: listTexts.deleteModal[forceDelete ? 'textForceDeleteBulk' : 'textSoftDeleteBulk'],
          cancelButtonText: listTexts.deleteModal.cancelButtonText,
          submitButtonText: listTexts.deleteModal.submitButtonText,
          submitCallback: (modalEl, submitButtonEl) => {
            multiSelectRequest(listService, {
              url: '/admin/api/delete',
              params: {
                force: forceDelete,
              },
              before: () => {
                submitButtonEl.classList.add('-loading');
                submitButtonEl.disabled = true;
              },
              complete: () => {
                submitButtonEl.classList.remove('-loading');
                submitButtonEl.disabled = false;
              },
              success: response => {
                listService.listData = response.listData;
                listService.render();
                success(response.message);
                closeMenu('multiselect-trash', 'multiselect');
                closeConfirmModal();
              },
            })();
          },
        });
      },
    });
  }

  multiselectActions.forEach(action => {
    const multiselectMenuLinkEl = document.createElement('div');
    multiselectMenuLinkEl.className = 'menu-overlay__link';
    multiselectMenuLinkEl.addEventListener('click', () => {
      action.callback && action.callback();
    });
    multiselectMenuLinksEl.append(multiselectMenuLinkEl);

    const multiselectMenuLinkIconEl = document.createElement('div');
    multiselectMenuLinkIconEl.className = 'menu-overlay__icon icon';
    multiselectMenuLinkIconEl.innerHTML = action.icon;
    multiselectMenuLinkEl.append(multiselectMenuLinkIconEl);

    const multiselectMenuLinkTextEl = document.createElement('div');
    multiselectMenuLinkTextEl.className = 'menu-overlay__label';
    multiselectMenuLinkTextEl.innerHTML = resolveText(listTexts, action.text);
    multiselectMenuLinkEl.append(multiselectMenuLinkTextEl);
  });

  return multiselectMenuEl;
};

/**
 * Update multiselect
 */

export const updateMultiselect = listService => {
  const wrapperEl = listService.wrapper;
  const listTexts = listService.listData.texts;

  const multiselectTogglerEls = wrapperEl.querySelectorAll('[data-toggle-multiselect]');
  if (multiselectTogglerEls) {
    const multiselectAllEls = wrapperEl.querySelectorAll('.list-item__container');
    const multiselectSelectedEls = wrapperEl.querySelectorAll(
      '.list-item__container[data-is-selected]'
    );

    const multiselectAmount = multiselectSelectedEls.length;
    const multiselectButtonContainerEl = wrapperEl.querySelector('.list-multiselect__button-container');
    const multiselectCurrentButtonEl = wrapperEl.querySelector('.list-multiselect__button');
    multiselectCurrentButtonEl && multiselectCurrentButtonEl.remove();

    if (multiselectAmount === 0) {
      multiselectTogglerEls.forEach(el => {
        el.innerHTML = 'check_box_outline_blank';
      });
    } else {
      if (multiselectAllEls.length === multiselectAmount) {
        multiselectTogglerEls.forEach(el => {
          el.innerHTML = 'check_box';
        });
      } else {
        multiselectTogglerEls.forEach(el => {
          el.innerHTML = 'indeterminate_check_box';
        });
      }

      const multiselectButtonEl = document.createElement('div');
      multiselectButtonEl.className = 'list-multiselect__button button -selectable no-select';
      multiselectButtonEl.dataset.toggleMenu = 'multiselect';
      multiselectButtonEl.innerHTML = resolveText(
        listTexts,
        'multiselect.buttonText' + (multiselectAmount == 1 ? '1' : 'N')
      ).replace('{n}', multiselectAmount);

      multiselectButtonEl.addEventListener('click', () => {
        openMultiselectMenu(listService);
      });

      multiselectButtonContainerEl.append(multiselectButtonEl);
    }
  }
};

/**
 * Deselect all selected elements
 */
export const deselectAll = listService => {
  const wrapperEl = listService.wrapper;
  wrapperEl.querySelectorAll('.list-item__container[data-is-selected]').forEach(el => {
    el.removeAttribute('data-is-selected');
    el.querySelector('.list__multiselect-icon').innerHTML = 'check_box_outline_blank';
  });
  updateMultiselect(listService);
};

/**
 * Open the multiselect menu
 */
export const openMultiselectMenu = listService => {
  const multiselectContainerEl = document.querySelector('.list-multiselect__container');
  const currentMultiselectMenuEl = document.querySelector(
    '.list-multiselect__menu' + (listService.onTrashPage() ? '.-trash' : ':not(.-trash)')
  );

  if (!currentMultiselectMenuEl) {
    const multiselectMenuEl = getMultiselectMenuEl(listService);
    multiselectContainerEl.append(multiselectMenuEl);
  }

  const triggerId = 'multiselect';
  const menuId = 'multiselect' + (listService.onTrashPage() ? '-trash' : '');
  menuIsOpen(menuId) ? closeMenu(menuId, triggerId) : openMenu(menuId, triggerId);
};

/**
 * Multiselect request
 */
function multiSelectRequest(listService, { url, params = {}, before, complete, success, error }) {
  return () => {
    if (listService._bulkRequestRunning) return;

    const ids = Array.from(
      document.querySelectorAll('.list-item__container[data-is-selected]')
    ).map(el => el.dataset.id);

    params.ids = ids;
    params.key = listService.listData.config.key;

    apiFetch({
      url: url,
      data: getListParams(listService, {}, params),
      before: () => {
        listService._bulkRequestRunning = true;
        before && before(ids);
      },
      complete: () => {
        listService._bulkRequestRunning = false;
        complete && complete(ids);
      },
      success: response => {
        if (response.success) {
          success && success(response, ids);
        } else {
          error ? error(response, ids) : networkError(response);
        }
      },
      error: xhr => {
        networkError(xhr);
      },
    });
  };
}
