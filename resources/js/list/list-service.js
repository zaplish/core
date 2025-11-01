import Sortable from 'sortablejs';
import { apiFetch } from '../services/api-fetch';
import { getNestedValue } from '../utils/object';
import { formatDatetime } from '../utils/datetime';
import { networkError, success } from '../ui/message';
import { config } from '../config/config';
import { confirmModal, closeConfirmModal } from '../ui/modal';
import { debounce } from '../utils/debounce';
import { textfield } from '../form/input/textfield';
import { select } from '../form/input/select';
import { renderPagination } from './pagination';
import { getFilePreview } from '../utils/file-icon';
import { initAttachSearchEvent } from '../form/events';
import { getMultiselectTogglerEl, updateMultiselect } from './list-service/multiselect';
import { resolveText } from '../utils/text';
import { updateUserConfig } from '../services/user-config';
import { applyFormLink } from './list-service/form';
import { adjustTooltipPosition, initTooltips } from '../ui/tooltip';
import { closeMenu, initMenus } from '../ui/menu';
import { keepInBounds } from '../utils/keep-in-bounds';
import { getListFilterUi } from './list-service/filters';

export class ListService {
  constructor({ key, wrapper }) {
    if (!key || !wrapper) return;

    this.key = key;
    this.wrapper = wrapper;
    this.wrapper._listService = this;

    this.listData = window.listData || null;

    // TODO
    // this.data
    // this.config
    // this.texts

    // TODO if list data is missing, get list data then init

    this.init();
  }

  init() {
    // Container
    this.container = document.createElement('div');
    this.container.className = 'list__container';

    // Header
    const headerEl = document.createElement('div');
    headerEl.className = 'list-header__container';

    // Filters container
    const filtersWrapperEl = document.createElement('div');
    filtersWrapperEl.className = 'list-filters__wrapper';
    headerEl.appendChild(filtersWrapperEl);

    // Views
    if (this.listData.config.hasGridView) {
      const activeView = this.listData.config.view || this.listData.config.defaultView;

      const viewOptionsContainerEl = document.createElement('div');
      viewOptionsContainerEl.className = 'list-view-options__container selectable-button-list';
      filtersWrapperEl.appendChild(viewOptionsContainerEl);

      const viewOptionGridViewContainerEl = document.createElement('div');
      viewOptionGridViewContainerEl.className =
        'list-view-option__container button -selectable no-select -single-icon -list-view';
      viewOptionGridViewContainerEl.innerHTML = '<div class="icon">grid_view</div>';
      viewOptionGridViewContainerEl.dataset.listView = 'grid';
      activeView === 'grid' && viewOptionGridViewContainerEl.classList.add('-active');
      viewOptionGridViewContainerEl.addEventListener('click', () => {
        this.listData.config.view = 'grid';
        this.setView('grid');
      });
      viewOptionsContainerEl.appendChild(viewOptionGridViewContainerEl);

      const viewOptionListViewContainerEl = document.createElement('div');
      viewOptionListViewContainerEl.className =
        'list-view-option__container button -selectable no-select -single-icon -grid-view';
      viewOptionListViewContainerEl.innerHTML = '<div class="icon">lists</div>';
      viewOptionListViewContainerEl.dataset.listView = 'list';
      activeView === 'list' && viewOptionListViewContainerEl.classList.add('-active');
      viewOptionListViewContainerEl.addEventListener('click', () => {
        this.listData.config.view = 'list';
        this.setView('list');
      });
      viewOptionsContainerEl.appendChild(viewOptionListViewContainerEl);
    }

    // Search
    const searchContainerEl = document.createElement('div');
    searchContainerEl.className = 'list-search__container';
    filtersWrapperEl.appendChild(searchContainerEl);
    const searchInputContainerEl = textfield({
      name: 'list-search',
      size: 'small',
      icon: 'search',
      hasClear: true,
      clearOnEsc: true,
      autocomplete: 'off',
      attributes: {
        'data-attach-search-event': true,
      },
    });
    searchContainerEl.appendChild(searchInputContainerEl);
    this.searchInputEl = searchInputContainerEl._inputEl;
    initAttachSearchEvent(searchInputContainerEl._inputEl);
    const handleSearch = debounce(() => {
      const searchTerm = this.searchInputEl.value.trim();
      this.listData.config.searchTerm = searchTerm;
      this.listData.config.page = 1;
      this.loadData({}, true);
    }, 300);
    this.searchInputEl.addEventListener('input', handleSearch);

    // Filters
    if (this.listData.config.filters) {
      const filtersEl = getListFilterUi(this);
      filtersWrapperEl.appendChild(filtersEl);
    }

    // Options container
    const optionsContainerEl = document.createElement('div');
    optionsContainerEl.className = 'list-options__container';
    headerEl.appendChild(optionsContainerEl);

    // Items amount
    this.itemsAmountContainerEl = document.createElement('div');
    this.itemsAmountContainerEl.className =
      'list-items-amount__container button -selectable -all no-select';
    optionsContainerEl.appendChild(this.itemsAmountContainerEl);
    if (this.listData.config.hasSoftDelete) {
      this.itemsAmountContainerEl.addEventListener('click', () => {
        if (this.listData.config.trashed) {
          this.listData.config.trashed = false;
          this.listData.config.orderBy = null;
          this.listData.config.orderDirection = null;
          this.listData.config.page = 1;
          this.loadData({
            renderHeader: true,
          });
        }
      });
    }

    // Trashed items amount
    this.trashItemsAmountContainerEl = null;
    if (this.listData.config.hasSoftDelete) {
      this.trashItemsAmountContainerEl = document.createElement('div');
      this.trashItemsAmountContainerEl.className =
        'list-items-amount__container button -selectable -trashed no-select';
      optionsContainerEl.appendChild(this.trashItemsAmountContainerEl);
      this.trashItemsAmountContainerEl.addEventListener('click', () => {
        if (!this.listData.config.trashed) {
          this.listData.config.trashed = true;
          this.listData.config.orderBy = null;
          this.listData.config.orderDirection = null;
          this.listData.config.page = 1;
          this.loadData({
            renderHeader: true,
          });
        }
      });
    }

    updateItemAmountButtons(
      this.itemsAmountContainerEl,
      this.trashItemsAmountContainerEl,
      this.listData
    );

    // Per-page
    const perPageContainerEl = document.createElement('div');
    perPageContainerEl.className = 'list-per-page__container';
    optionsContainerEl.appendChild(perPageContainerEl);

    const perPageSelectContainerEl = select({
      name: 'per-page',
      size: 'small',
      value: this.listData.config.perPage || this.listData.config.defaultPerPage || 25,
      options: [
        { value: '3', label: '3' },
        { value: '10', label: '10' },
        { value: '25', label: '25' },
        { value: '50', label: '50' },
        { value: '100', label: '100' },
      ],
      onChange: () => {
        const perPage = perPageSelectContainerEl._selectEl.value;
        this.listData.config.perPage = parseInt(perPage);
        this.listData.config.page = 1;
        this.loadData();
      },
    });
    perPageContainerEl.appendChild(perPageSelectContainerEl);

    // Content
    const contentEl = document.createElement('div');
    contentEl.className = 'list-content__container';
    contentEl.classList.add('-list-view-' + this.listData.config.view);

    this.contentHeader = document.createElement('div');
    this.contentHeader.className = 'list-content__header';
    contentEl.appendChild(this.contentHeader);

    this.contentItems = document.createElement('div');
    this.contentItems.className = 'list-content__items';
    contentEl.appendChild(this.contentItems);

    // Footer
    const footerEl = document.createElement('div');
    footerEl.className = 'list-footer__container';

    // Multiselect container
    this.multiselectContainerEl = document.createElement('div');
    this.multiselectContainerEl.className = 'list-multiselect__container';
    footerEl.appendChild(this.multiselectContainerEl);

    // Pagination container
    this.paginationContainerEl = document.createElement('div');
    this.paginationContainerEl.className = 'list-pagination__container';
    footerEl.appendChild(this.paginationContainerEl);

    this.container.appendChild(headerEl);
    this.container.appendChild(contentEl);
    this.container.appendChild(footerEl);
    this.wrapper.appendChild(this.container);

    this.render({
      renderHeader: true,
    });
  }

  // Load data
  loadData(params = {}, cancelPrevious = false) {
    if (this.loading) {
      if (cancelPrevious && this.xhr) {
        this.pendingReload = false;
        this.pendingReloadParams = null;
        this.xhr.abort();
      } else {
        this.pendingReload = true;
        this.pendingReloadParams = params;
        return false;
      }
    }

    const listConfig = this.listData?.config || {};

    this.xhr = apiFetch({
      url: '/admin/api/list',
      data: getListParams(this, params),
      headers: {
        Accept: 'application/json',
      },
      before: () => {
        this.loading = true;
        this.wrapper.classList.add('-loading');
      },
      complete: () => {
        this.loading = false;
        this.wrapper.classList.remove('-loading');

        if (this.pendingReload) {
          this.pendingReload = false;
          this.loadData(this.pendingReloadParams || {});
          this.pendingReloadParams = null;
        }
      },
      success: response => {
        if (response.success && response.listData) {
          this.listData = response.listData;
          this.render({
            renderHeader: params.renderHeader,
          });
        } else {
          networkError(response);
        }
      },
      error: xhr => {
        networkError(xhr);
      },
    });
  }

  // Render
  render(params = {}) {
    // Config
    const listConfig = this.listData?.config || {};
    let listColumns = listConfig?.columns || [];
    const listItems = this.listData?.items?.data || [];
    const listTexts = this.listData?.texts || {};

    // Update view class
    this.wrapper
      .querySelector('.list-content__container')
      .classList.toggle('-list-view-grid', listConfig.view === 'grid');
    this.wrapper
      .querySelector('.list-content__container')
      .classList.toggle('-list-view-list', listConfig.view === 'list');

    // Colummns for trash
    if (listConfig.trashed) {
      listColumns.unshift({
        key: 'multiselect',
        type: 'multiselect',
        label: null,
        allowTrashed: true,
      });

      listColumns.push({
        key: 'deleted-at',
        type: 'datetime',
        label: 'Deleted',
        allowTrashed: true,
        source: 'deleted_at',
        sortable: true,
      });

      listColumns.push({
        key: 'actions',
        type: 'actions',
        label: null,
        allowTrashed: true,
        actions: [
          {
            type: 'restore',
          },
          {
            type: 'force-delete',
          },
        ],
      });
    }

    // Head columns
    if (params.renderHeader) {
      this.contentHeader.innerHTML = '';

      listColumns.forEach(column => {
        if (listConfig.trashed && !column.allowTrashed && column.type !== 'title') {
          return;
        }

        const columnEl = document.createElement('div');
        columnEl.classList.add('list__column', '-head', '-type-' + column.type);

        if (column.visibility) {
          Object.entries(column.visibility).forEach(([breakpoint, isVisible]) => {
            if (isVisible === false) {
              columnEl.classList.add(`-hide-${breakpoint}`);
            }
          });
        }

        if (listConfig.orderBy == column.source) {
          columnEl.classList.add('-current-order');
        }

        if (column.label) {
          const columnLabelEl = document.createElement('div');
          columnLabelEl.innerHTML = resolveText(listTexts, column.label);
          columnEl.append(columnLabelEl);
        }

        if (column.type == 'multiselect') {
          const columnMultiselectIconEl = getMultiselectTogglerEl(this);
          columnEl.append(columnMultiselectIconEl);
        }

        if (column.sortable) {
          // TODO move to list-service/sortable.js
          columnEl.classList.add('-sortable');
          columnEl.dataset.orderBy = column.source;
          columnEl.dataset.orderDirection =
            listConfig.orderDirection || column.defaultOrderDirection || 'asc';
          columnEl.dataset.defaultOrderDirection = column.defaultOrderDirection || 'asc';

          const columnSortableEl = document.createElement('div');
          columnSortableEl.classList.add('icon', 'list__order-icon');
          columnSortableEl.innerHTML = 'keyboard_arrow_up';

          columnEl.addEventListener('click', () => {
            const listConfig = this.listData?.config || {};
            let newDirection;
            if (listConfig.orderBy === column.source) {
              const currentDirection = columnEl.dataset.orderDirection;
              newDirection = currentDirection === 'asc' ? 'desc' : 'asc';
            } else {
              const currentDirection = columnEl.dataset.orderDirection;
              newDirection = currentDirection;
            }
            this.listData.config.orderBy = column.source;
            this.listData.config.orderDirection = newDirection;
            this.loadData();
          });

          columnEl.append(columnSortableEl);
        }

        if (column.type == 'actions') {
          column.actions.forEach(action => {
            if (!action.onlyMenu) {
              const actionHeaderEl = document.createElement('div');
              actionHeaderEl.classList.add('list__action');
              columnEl.append(actionHeaderEl);
            }
          });
        }

        this.contentHeader.append(columnEl);
      });
    } else {
      const currentOrderEl = document.querySelector('.list__column.-head.-sortable.-current-order');
      currentOrderEl?.classList.remove('-current-order');

      const nextOrderEl = document.querySelector(
        '.list__column.-head.-sortable[data-order-by="' + listConfig.orderBy + '"]'
      );
      if (nextOrderEl) {
        nextOrderEl.classList.add('-current-order');
        nextOrderEl.dataset.orderDirection = listConfig.orderDirection;
      }
    }

    // Items
    this.contentItems.innerHTML = '';

    if (!listItems || !listItems.length) {
      this.contentItems.innerHTML =
        '<div class="list-item__container">' +
        (listConfig.trashed && !listConfig.meta.trashCount
          ? listTexts.empty.trash
          : listTexts.empty.items) +
        '</div>';
      this.wrapper.classList.add('-empty');
    } else {
      this.wrapper.classList.remove('-empty');
    }

    listItems.forEach(item => {
      const itemContainerEl = document.createElement('div');
      itemContainerEl.className = 'list-item__container';
      itemContainerEl.dataset.id = item.id;
      if (!listConfig.trashed && (item.active === false || item.active === 0)) {
        itemContainerEl.classList.add('-inactive');
      }
      this.contentItems.appendChild(itemContainerEl);

      listColumns.forEach(column => {
        if (listConfig.trashed && !column.allowTrashed && column.type !== 'title') {
          return;
        }

        if (
          listConfig.view === 'grid' &&
          column.type !== 'title' &&
          column.type !== 'actions' &&
          column.type !== 'multiselect' &&
          column.type !== 'filepreview'
        ) {
          return;
        }

        const itemColumnEl = document.createElement('div');
        itemColumnEl.classList.add('list__column', '-body', '-type-' + column.type);

        if (column.visibility) {
          Object.entries(column.visibility).forEach(([breakpoint, isVisible]) => {
            if (isVisible === false) {
              itemColumnEl.classList.add(`-hide-${breakpoint}`);
            }
          });
        }

        switch (column.type) {
          case 'multiselect':
            itemColumnEl.classList.add('no-select');
            const itemColumnMultiselectIconEl = document.createElement('div');
            itemColumnMultiselectIconEl.classList.add('list__multiselect-icon', 'icon');
            itemColumnMultiselectIconEl.innerText = 'check_box_outline_blank';
            itemColumnEl.append(itemColumnMultiselectIconEl);
            itemColumnEl.addEventListener('click', () => {
              let isSelected = itemContainerEl.hasAttribute('data-is-selected');
              if (isSelected) {
                itemContainerEl.removeAttribute('data-is-selected');
                isSelected = false;
              } else {
                itemContainerEl.setAttribute('data-is-selected', '');
                isSelected = true;
              }
              itemColumnMultiselectIconEl.innerText = isSelected
                ? 'check_box'
                : 'check_box_outline_blank';
              updateMultiselect(this);
            });
            break;

          case 'sortable':
            itemColumnEl.classList.add('no-select');
            const itemColumnSortableEl = document.createElement('div');
            itemColumnSortableEl.classList.add('list__sortable-handle', 'icon');
            itemColumnSortableEl.innerText = 'drag_handle';
            itemColumnEl.append(itemColumnSortableEl);
            break;

          case 'icon':
            const itemColumnIconEl = document.createElement('div');
            itemColumnIconEl.classList.add('icon');
            itemColumnIconEl.innerHTML = getNestedValue(item, column.source);
            itemColumnEl.append(itemColumnIconEl);
            break;

          case 'filepreview':
            let itemColumnFilepreviewEl;
            itemColumnFilepreviewEl = document.createElement('div');
            itemColumnFilepreviewEl.classList.add('list__filepreview');
            itemColumnFilepreviewEl.classList.add('-media-type-' + item.media_type);

            let previewUri = null;
            if (item.extension == 'ico' || item.extension == 'svg') {
              previewUri = item.uri;
            } else if (column.source) {
              previewUri = getNestedValue(item, column.source + '.uri');
            }

            itemColumnFilepreviewEl.appendChild(
              getFilePreview({
                extension: item.extension,
                previewUri: previewUri,
                linkUrl: listConfig.view !== 'grid' && column.isLink ? item.uri : null,
                linkTarget: listConfig.view !== 'grid' && column.isLink ? '_blank' : null,
              })
            );
            itemColumnEl.append(itemColumnFilepreviewEl);
            break;

          case 'title':
            const title = getNestedValue(item, column.source);
            const itemColumnTitleLinkEl = document.createElement('a');
            applyFormLink(listConfig, item, itemColumnTitleLinkEl);
            itemColumnTitleLinkEl.innerHTML =
              listConfig.view === 'grid' ? '<div><div>' + title + '</div></div>' : title;
            itemColumnEl.append(itemColumnTitleLinkEl);
            break;

          case 'email':
            const email = getNestedValue(item, column.source);
            const itemColumnEmailLinkEl = document.createElement('a');
            itemColumnEmailLinkEl.href = 'mailto:' + email;
            itemColumnEmailLinkEl.innerHTML = email;
            itemColumnEl.append(itemColumnEmailLinkEl);
            break;

          case 'badge':
            const badgeKey = getNestedValue(item, column.source);
            const badgeText = column.config?.map[badgeKey]?.text || badgeKey;
            const itemColumnBadgeEl = document.createElement('div');
            itemColumnBadgeEl.classList.add('badge', '-' + badgeKey);
            itemColumnBadgeEl.innerHTML = badgeText;
            itemColumnEl.append(itemColumnBadgeEl);
            break;

          case 'datetime':
            let datetime = getNestedValue(item, column.source);
            datetime = formatDatetime(datetime, { relative: column.relativeDatetime });
            itemColumnEl.innerHTML = datetime;
            break;

          case 'username':
            itemColumnEl.innerHTML = getNestedValue(item, column.source);
            break;

          // Type actions
          case 'actions':
            const isTrashed = listConfig.trashed;
            const menuId = 'list-action-menu-' + (isTrashed ? '' : '') + item.id;

            // List container
            const actionsListContainerEl = document.createElement('div');
            actionsListContainerEl.classList.add('list__actions-container');
            itemColumnEl.append(actionsListContainerEl);

            // Menu containers
            const actionsMenuContainerEl = document.createElement('div');
            actionsMenuContainerEl.classList.add('list__actions-menu-container');
            itemColumnEl.append(actionsMenuContainerEl);

            const actionsMenuEl = document.createElement('div');
            actionsMenuEl.classList.add('list__actions-menu', 'menu-overlay__wrapper', '-compact');
            actionsMenuEl.dataset.menu = menuId;
            actionsMenuEl.dataset.flip = 4;
            actionsMenuEl.onMenuOpen = () => {
              itemContainerEl.classList.add('-menu-open');
            };
            actionsMenuEl.onMenuClose = () => {
              itemContainerEl.classList.add('-menu-closing');
              const insideMenus = actionsMenuEl.querySelectorAll('[data-menu]');
              insideMenus.forEach(menuEl => {
                closeMenu(menuEl.dataset.menu);
              });
            };
            actionsMenuEl.onMenuCloseComplete = () => {
              itemContainerEl.classList.remove('-menu-open', '-menu-closing');
            };
            actionsMenuEl.keepInBounds = menuEl => {
              keepInBounds(menuEl, {
                padding: config.menuPadding,
                container: getBoundsContainer(this),
                attribute: 'marginRight',
              });
            };
            actionsMenuContainerEl.append(actionsMenuEl);

            const actionsMenuLinksEl = document.createElement('div');
            actionsMenuLinksEl.classList.add('menu-overlay__links');
            actionsMenuEl.append(actionsMenuLinksEl);

            const actionMenuTogglerEl = document.createElement('div');
            actionMenuTogglerEl.classList.add(
              'list__actions-menu-toggler',
              'list__action',
              '-type-menu',
              'no-select'
            );
            actionMenuTogglerEl.innerHTML = '<div class="list__action-icon icon">more_horiz</div>';
            actionMenuTogglerEl.dataset.toggleMenu = menuId;
            actionsMenuContainerEl.append(actionMenuTogglerEl);

            column.actions.forEach(action => {
              let actionType = action.type;
              let labelActionType = actionType;
              let actionLabel = action.label;

              // Action container
              const actionListElDiv = document.createElement('div');
              const actionListElA = document.createElement('a');

              [actionListElDiv, actionListElA].forEach(el => {
                el.classList.add('list__action', 'no-select', '-type-' + actionType);
                el.dataset.listAction = actionType;
                el.dataset.tooltipTrigger = 'list-action-' + actionType;
              });

              const actionMenuElDiv = document.createElement('div');
              const actionMenuElA = document.createElement('a');

              [actionMenuElDiv, actionMenuElA].forEach(el => {
                el.classList.add('menu-overlay__link', 'no-select');
                el.dataset.menuAction = actionType;
              });

              let actionListEl = actionListElDiv;
              let actionMenuEl = actionMenuElDiv;

              // Icon
              const actionListIconEl = document.createElement('div');
              actionListIconEl.classList.add('icon', 'list__action-icon');

              const actionMenuIconEl = document.createElement('div');
              actionMenuIconEl.classList.add('icon', 'menu-overlay__icon');

              // Label
              const actionListLabelEl = document.createElement('div');
              actionListLabelEl.dataset.tooltip = 'list-action-' + actionType;
              actionListLabelEl.classList.add('list__action-label');

              const actionMenuLabelEl = document.createElement('div');
              actionMenuLabelEl.classList.add('menu-overlay__label');

              switch (actionType) {
                // Action toggle
                case 'toggle':
                  [actionListIconEl, actionMenuIconEl].forEach(iconEl => {
                    iconEl.innerHTML = item.active ? 'toggle_on' : 'toggle_off';
                  });

                  labelActionType = item.active ? 'deactivate' : 'activate';

                  [actionListEl, actionMenuEl].forEach(actionEl => {
                    actionEl.addEventListener('click', () => {
                      if (item._toggleRequestRunning) return;

                      apiFetch({
                        url: '/admin/api/toggle',
                        data: {
                          key: listConfig.key,
                          id: item.id,
                        },
                        headers: {
                          Accept: 'application/json',
                        },
                        before: () => {
                          item._toggleRequestRunning = true;
                        },
                        complete: () => {
                          item._toggleRequestRunning = false;
                        },
                        success: response => {
                          if (response.success) {
                            item.active = response.value;
                            actionListIconEl.innerHTML = actionMenuIconEl.innerHTML = item.active
                              ? 'toggle_on'
                              : 'toggle_off';
                            itemContainerEl.classList[item.active ? 'remove' : 'add']('-inactive');
                            updateListActionToggleLabel(this, item.id);
                            response.message && success(response.message);
                            closeMenu(menuId);
                          } else {
                            networkError(response);
                          }
                        },
                        error: xhr => {
                          networkError(xhr);
                        },
                      });
                    });
                  });
                  break;

                case 'duplicate':
                  [actionListIconEl, actionMenuIconEl].forEach(iconEl => {
                    iconEl.innerHTML = 'content_copy';
                  });

                  [actionListEl, actionMenuEl].forEach(actionEl => {
                    actionEl.addEventListener('click', () => {
                      if (item._duplicateRequestRunning) return;

                      apiFetch({
                        url: '/admin/api/duplicate',
                        data: getListParams(this, {}, { id: item.id }),
                        headers: {
                          Accept: 'application/json',
                        },
                        before: () => {
                          item._duplicateRequestRunning = true;
                        },
                        complete: () => {
                          item._duplicateRequestRunning = false;
                        },
                        success: response => {
                          if (response.success) {
                            this.listData = response.listData;
                            this.render();
                            response.message && success(response.message);
                            closeMenu('list-action-menu-' + item.id);
                          } else {
                            networkError(response);
                          }
                        },
                        error: xhr => {
                          networkError(xhr);
                        },
                      });
                    });
                  });
                  break;

                case 'reorder':
                  [actionListIconEl, actionMenuIconEl].forEach(iconEl => {
                    iconEl.innerHTML = 'format_line_spacing';
                  });

                  [actionListEl, actionMenuEl].forEach(actionEl => {
                    const menuPosition = actionEl.hasAttribute('data-menu-action')
                      ? 'menu'
                      : 'list';
                    const menuId = 'list-reorder-menu-' + menuPosition + '-' + item.id;
                    actionEl.dataset.toggleMenu = menuId;

                    const listReorderMenuEl = document.createElement('div');
                    listReorderMenuEl.classList.add(
                      'list__reorder-menu',
                      'menu-overlay__wrapper',
                      '-compact'
                    );
                    listReorderMenuEl.dataset.menu = menuId;
                    listReorderMenuEl.dataset.flip = 4;
                    listReorderMenuEl.addEventListener('click', ev => {
                      ev.stopPropagation();
                    });
                    if (menuPosition === 'list') {
                      listReorderMenuEl.classList.add('-list');
                      listReorderMenuEl.onMenuOpen = () => {
                        itemContainerEl.classList.add('-menu-open');
                      };
                      listReorderMenuEl.onMenuClose = () => {
                        itemContainerEl.classList.add('-menu-closing');
                      };
                      listReorderMenuEl.onMenuCloseComplete = () => {
                        itemContainerEl.classList.remove('-menu-open', '-menu-closing');
                      };
                    } else {
                      listReorderMenuEl.ignoreClickOutside = true;
                      listReorderMenuEl.classList.add('-menu', '-left');
                    }
                    listReorderMenuEl.keepInBounds = menuEl => {
                      // TODO in modal use different container
                      const boundsContainerSelector =
                        '.content__container, .TODO__MODAL__CONTENT__CONTAINER';
                      const boundsContainerEl = this.wrapper.closest(boundsContainerSelector);
                      keepInBounds(menuEl, {
                        padding: config.menuPadding,
                        container: boundsContainerEl,
                      });
                    };
                    actionEl.append(listReorderMenuEl);

                    const listReorderMenuLinksEl = document.createElement('div');
                    listReorderMenuLinksEl.classList.add('menu-overlay__links');
                    listReorderMenuEl.append(listReorderMenuLinksEl);

                    [
                      {
                        type: 'move-up',
                        icon: 'move_up',
                        label: listData.texts.actionLabel['reorder-up'],
                      },
                      {
                        type: 'move-down',
                        icon: 'move_down',
                        label: listData.texts.actionLabel['reorder-down'],
                      },
                      {
                        type: 'move-to-top',
                        icon: 'low_priority',
                        label: listData.texts.actionLabel['reorder-to-top'],
                      },
                      {
                        type: 'move-to-bottom',
                        icon: 'low_priority',
                        label: listData.texts.actionLabel['reorder-to-bottom'],
                      },
                    ].forEach(action => {
                      const listReorderMenuLinkEl = document.createElement('div');
                      listReorderMenuLinkEl.classList.add('menu-overlay__link', 'no-select');
                      listReorderMenuLinkEl.classList.add(
                        'list__reorder-menu-link',
                        '-type-' + action.type
                      );
                      listReorderMenuLinksEl.append(listReorderMenuLinkEl);

                      const listReorderMenuIconEl = document.createElement('div');
                      listReorderMenuIconEl.classList.add('icon', 'menu-overlay__icon');
                      listReorderMenuIconEl.innerHTML = action.icon;
                      listReorderMenuLinkEl.append(listReorderMenuIconEl);

                      const listReorderMenuLabelEl = document.createElement('div');
                      listReorderMenuLabelEl.classList.add('menu-overlay__label');
                      listReorderMenuLabelEl.innerHTML = action.label;
                      listReorderMenuLinkEl.append(listReorderMenuLabelEl);

                      listReorderMenuLinkEl.addEventListener('click', ev => {
                        if (item._reorderRequestRunning) return;

                        apiFetch({
                          url: '/admin/api/reorder-item',
                          data: getListParams(
                            this,
                            {},
                            {
                              id: item.id,
                              action: action.type,
                            }
                          ),
                          headers: {
                            Accept: 'application/json',
                          },
                          before: () => {
                            item._reorderRequestRunning = true;
                          },
                          complete: () => {
                            item._reorderRequestRunning = false;
                          },
                          success: response => {
                            if (response.success) {
                              this.listData = response.listData;
                              this.render();
                              closeMenu('list-action-menu-' + item.id);
                              response.message && success(response.message);
                            } else {
                              networkError(response);
                            }
                          },
                          error: xhr => {
                            networkError(xhr);
                          },
                        });
                      });
                    });
                  });
                  break;

                case 'edit':
                  [actionListIconEl, actionMenuIconEl].forEach(iconEl => {
                    iconEl.innerHTML = 'edit';
                  });

                  actionListEl = actionListElA;
                  actionMenuEl = actionMenuElA;

                  [actionListEl, actionMenuEl].forEach(actionEl => {
                    applyFormLink(listConfig, item, actionEl);
                  });
                  break;

                case 'media-preview':
                  [actionListIconEl, actionMenuIconEl].forEach(iconEl => {
                    iconEl.innerHTML = 'open_in_new';
                  });

                  actionListEl = actionListElA;
                  actionMenuEl = actionMenuElA;

                  [actionListEl, actionMenuEl].forEach(actionEl => {
                    actionEl.href = item.uri;
                    actionEl.target = '_blank';
                  });
                  break;

                case 'media-download':
                  [actionListIconEl, actionMenuIconEl].forEach(iconEl => {
                    iconEl.innerHTML = 'download';
                  });

                  actionListEl = actionListElA;
                  actionMenuEl = actionMenuElA;

                  [actionListEl, actionMenuEl].forEach(actionEl => {
                    actionEl.href = item.uri;
                    actionEl.setAttribute('download', item.slug + '.' + item.extension);
                  });
                  break;

                case 'copy-url':
                  [actionListIconEl, actionMenuIconEl].forEach(iconEl => {
                    iconEl.innerHTML = 'link';
                  });

                  [actionListEl, actionMenuEl].forEach(actionEl => {
                    actionEl.addEventListener('click', () => {
                      navigator.clipboard.writeText(window.location.origin + item.uri);
                      success(this.listData.texts.actionLabel['copy-url-success']);
                    });
                  });
                  break;

                case 'delete':
                case 'force-delete':
                  const forceDeleting = actionType === 'force-delete' || !listConfig.hasSoftDelete;

                  labelActionType = forceDeleting ? 'force-delete' : 'delete';

                  [actionListIconEl, actionMenuIconEl].forEach(iconEl => {
                    iconEl.innerHTML = forceDeleting ? 'delete_forever' : 'delete';
                  });

                  [actionListEl, actionMenuEl].forEach(actionEl => {
                    actionEl.addEventListener('click', () => {
                      confirmModal({
                        title: this.listData.texts.deleteModal.title,
                        text: this.listData.texts.deleteModal[
                          forceDeleting ? 'textForceDelete' : 'textSoftDelete'
                        ],
                        cancelButtonText: this.listData.texts.deleteModal.cancelButtonText,
                        submitButtonText: this.listData.texts.deleteModal.submitButtonText,
                        submitCallback: (modalEl, submitBtn) => {
                          if (item._deleteRequestRunning) return;

                          apiFetch({
                            url: '/admin/api/delete',
                            data: getListParams(
                              this,
                              {},
                              {
                                id: item.id,
                                force: forceDeleting,
                              }
                            ),
                            headers: {
                              Accept: 'application/json',
                            },
                            before: () => {
                              item._deleteRequestRunning = true;
                              submitBtn.classList.add('-loading');
                              submitBtn.disabled = true;
                            },
                            complete: () => {
                              item._deleteRequestRunning = false;
                              submitBtn.classList.remove('-loading');
                              submitBtn.disabled = false;
                            },
                            success: response => {
                              if (response.success) {
                                this.listData = response.listData;
                                this.render();
                                response.message && success(response.message);
                                closeConfirmModal();
                              } else {
                                networkError(response);
                              }
                            },
                            error: xhr => {
                              networkError(xhr);
                            },
                          });
                        },
                      });
                    });
                  });
                  break;

                case 'restore':
                  [actionListIconEl, actionMenuIconEl].forEach(iconEl => {
                    iconEl.innerHTML = 'restore_from_trash';
                  });

                  [actionListEl, actionMenuEl].forEach(actionEl => {
                    actionEl.addEventListener('click', () => {
                      if (item._restoreRequestRunning) return;

                      apiFetch({
                        url: '/admin/api/restore',
                        data: getListParams(this, {}, { id: item.id }),
                        headers: {
                          Accept: 'application/json',
                        },
                        before: () => {
                          item._restoreRequestRunning = true;
                        },
                        complete: () => {
                          item._restoreRequestRunning = false;
                        },
                        success: response => {
                          if (response.success) {
                            this.listData = response.listData;
                            this.render();
                            response.message && success(response.message);
                          } else {
                            networkError(response);
                          }
                        },
                        error: xhr => {
                          networkError(xhr);
                        },
                      });
                    });
                  });
                  break;
              }

              // Icon
              actionListEl.append(actionListIconEl);
              actionMenuEl.append(actionMenuIconEl);

              // Label
              if (!actionLabel) {
                actionLabel = this.listData.texts.actionLabel[labelActionType];
              }

              if (actionLabel) {
                [actionListLabelEl, actionMenuLabelEl].forEach(labelEl => {
                  labelEl.innerHTML = actionLabel;
                });

                actionListEl.append(actionListLabelEl);
                actionMenuEl.append(actionMenuLabelEl);
              }

              // Append
              let appendToList = !action.onlyMenu;
              let appendToMenu = !action.onlyList;

              if (appendToList) {
                actionsListContainerEl.append(actionListEl);
              }

              if (appendToMenu) {
                actionsMenuLinksEl.append(actionMenuEl);
              }
            });
            break;
        }

        itemContainerEl.append(itemColumnEl);
      });
    });

    initMenus();
    initTooltips();

    // Sortable lists
    this.wrapper.classList.remove('-sortable');

    if (this.sortable) {
      this.sortable.destroy();
      this.sortable = null;
    }

    if (
      listItems &&
      listItems.length &&
      !listConfig.trashed &&
      listConfig.orderBy === 'order' &&
      listConfig.orderDirection === 'asc' &&
      !listConfig.searchTerm
    ) {
      this.wrapper.classList.add('-sortable');

      this.sortable = Sortable.create(this.contentItems, {
        handle: '.list__sortable-handle',
        animation: config.fastTransitionSpeed,
        ghostClass: '-sortable-ghost',
        dragClass: '-sortable-dragging',
        onStart: () => {
          document.body.classList.add('-is-dragging');
        },
        onEnd: evt => {
          document.body.classList.remove('-is-dragging');
          const oldIndex = evt.oldIndex;
          const newIndex = evt.newIndex;
          if (oldIndex === newIndex) return;

          const rows = Array.from(this.contentItems.children);
          const [start, end] = [oldIndex, newIndex].sort((a, b) => a - b);
          const affectedRows = rows.slice(start, end + 1);

          const reorderPayload = affectedRows.map((el, i) => ({
            id: el.dataset.id,
            order: start + i + 1,
          }));

          apiFetch({
            url: '/admin/api/reorder-list',
            data: {
              key: listConfig.key,
              items: reorderPayload,
            },
            headers: {
              Accept: 'application/json',
            },
            success: response => {
              if (response.success) {
                response.message && success(response.message);
              } else {
                networkError(response);
              }
            },
            error: xhr => {
              networkError(xhr);
            },
          });
        },
      });
    }

    // Multiselect
    this.multiselectContainerEl.innerHTML = '';

    if (
      this.listData.config.trashed ||
      this.listData.config.columns.some(column => column.type === 'multiselect')
    ) {
      const multiSelectTogglerEl = getMultiselectTogglerEl(this);
      this.multiselectContainerEl.appendChild(multiSelectTogglerEl);

      const multiselectButtonContainerEl = document.createElement('div');
      multiselectButtonContainerEl.className = 'list-multiselect__button-container';
      this.multiselectContainerEl.appendChild(multiselectButtonContainerEl);
    }

    // Update multiselect
    updateMultiselect(this);

    // Update item amount buttons
    updateItemAmountButtons(
      this.itemsAmountContainerEl,
      this.trashItemsAmountContainerEl,
      this.listData
    );

    // Update navigation
    const current_page = this.listData.items.current_page;
    const last_page = this.listData.items.last_page;
    const inputPlaceholderText = this.listData.texts.pagination.inputPlaceholderText;
    const onPageChange = function (page) {
      this.listData.config.page = page;
      this.loadData();
    }.bind(this);
    this.paginationContainerEl.innerHTML = '';
    const paginationContainerEl = renderPagination(
      { current_page, last_page, inputPlaceholderText },
      onPageChange
    );
    this.paginationContainerEl.append(paginationContainerEl);
  }

  // Check if trash page
  onTrashPage() {
    const trashButton = document.querySelector('.list-items-amount__container.-trashed');
    return trashButton && trashButton.classList.contains('-active');
  }

  // Get view
  getView() {
    return this.listData.config.view;
  }

  // Set view
  setView(view) {
    this.listData.config.view = view;
    this.render();

    document.querySelectorAll('.list-view-option__container').forEach(el => {
      el.classList.toggle('-active', el.dataset.listView === view);
    });

    updateUserConfig({
      'list-settings': {
        [this.listData.config.key]: {
          view: view,
        },
      },
    });
  }
}

/**
 * Get list params
 */
export function getListParams(listService, params = {}, obj = {}) {
  const listConfig = listService.listData.config;

  return {
    key: listConfig?.key,
    orderBy: params?.orderBy || listConfig?.orderBy,
    orderDirection: params?.orderDirection || listConfig?.orderDirection,
    searchTerm: params?.searchTerm || listConfig?.searchTerm,
    perPage: params?.perPage || listConfig?.perPage,
    trashed: params?.trashed || listConfig?.trashed,
    page: params?.page || listConfig?.page,
    view: params?.view || listConfig?.view,
    filters: getListFilters(listService),
    ...obj,
  };
}

/**
 * Get list filters
 */
function getListFilters(listService) {
  const filters = [];

  listService.wrapper.querySelectorAll('[data-list-filter]').forEach(optionEl => {
    const filterKey = optionEl.dataset.listFilter;
    const filterType = optionEl.dataset.listFilterType;
    const filterWhereColumn = optionEl.dataset.listFilterWhereColumn;

    const filter = {
      key: filterKey,
      type: filterType,
      column: filterWhereColumn,
    };

    switch (filterType) {
      case 'radio':
        const selectedOptionEl = optionEl.querySelector(
          '[data-list-filter-value][data-is-selected]'
        );

        if (selectedOptionEl) {
          filter.value = selectedOptionEl.dataset.listFilterValue;
          filters.push(filter);
        }
        break;

      case 'checkbox':
        const selectedOptionsEl = optionEl.querySelectorAll(
          '[data-list-filter-value][data-is-selected]'
        );

        if (selectedOptionsEl.length > 0) {
          filter.value = Array.from(selectedOptionsEl).map(el => el.dataset.listFilterValue);
          filters.push(filter);
        }
        break;
    }
  });

  return filters;
}

/**
 * Update item amount buttons
 */
function updateItemAmountButtons(itemsEl, trashItemsEl, listData) {
  itemsEl.classList[listData.config.trashed ? 'remove' : 'add']('-active');

  const countItems = listData.config.meta.totalCount;

  const textItemKey = countItems == 1 || countItems == 0 ? countItems : 'N';
  const textItems = listData.texts.itemCount['items' + textItemKey];

  itemsEl.innerHTML = textItems.replace('{n}', countItems);

  if (trashItemsEl) {
    trashItemsEl.classList[listData.config.trashed ? 'add' : 'remove']('-active');
    const countTrash = listData.config.meta.trashCount;
    const textTrashKey = countTrash == 1 || countTrash == 0 ? countTrash : 'N';
    const textTrash = listData.texts.itemCount['trash' + textTrashKey];
    trashItemsEl.innerHTML = textTrash.replace('{n}', countTrash);
  }
}

/**
 * Update list action toggle label
 */
function updateListActionToggleLabel(list, id) {
  const itemRowEl = list.wrapper.querySelector(`.list-item__container[data-id="${id}"]`);
  const actionListLabelEl = itemRowEl.querySelector(
    '[data-list-action="toggle"] .list__action-label'
  );
  const actionMenuLabelEl = itemRowEl.querySelector(
    '[data-menu-action="toggle"] .menu-overlay__label'
  );
  const labelActionType = itemRowEl.classList.contains('-inactive') ? 'activate' : 'deactivate';
  actionListLabelEl.innerHTML = actionMenuLabelEl.innerHTML =
    list.listData.texts.actionLabel[labelActionType];
  adjustTooltipPosition(actionListLabelEl);
}

/**
 * Get the bounds container
 */
export function getBoundsContainer(listService) {
  const boundsContainerSelectors = ['.content__container', '.TODO__MODAL__CONTENT__CONTAINER'];
  return listService.wrapper.closest(boundsContainerSelectors.join(', '));
}
