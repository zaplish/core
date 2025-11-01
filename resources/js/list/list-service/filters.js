import { config } from '../../config/config';
import { getBoundsContainer } from '../list-service';
import { keepInBounds } from '../../utils/keep-in-bounds';
import { resolveText } from '../../utils/text';
import { closeMenu } from '../../ui/menu';

export const getListFilterUi = listService => {
  const filtersContainerEl = document.createElement('div');
  filtersContainerEl.className = 'list-filters__container';

  const filtersButtonEl = document.createElement('div');
  filtersButtonEl.className = 'list-filters__button button -selectable no-select -single-icon';
  filtersButtonEl.dataset.toggleMenu = 'list-filters-menu-' + listService.key;
  filtersContainerEl.appendChild(filtersButtonEl);

  const filtersButtonIconEl = document.createElement('div');
  filtersButtonIconEl.className = 'list-filters__button-icon icon';
  filtersButtonIconEl.innerHTML = 'filter_list';
  filtersButtonEl.appendChild(filtersButtonIconEl);

  const filtersButtonAmountEl = document.createElement('div');
  filtersButtonAmountEl.className = 'list-filters__button-amount icon';
  filtersButtonAmountEl.innerHTML = 'check';
  filtersButtonEl.appendChild(filtersButtonAmountEl);

  const filtersOptionsContainerEl = document.createElement('div');
  filtersOptionsContainerEl.className =
    'list-filters__options-container menu-overlay__wrapper -compact -left';
  filtersOptionsContainerEl.dataset.menu = 'list-filters-menu-' + listService.key;
  filtersOptionsContainerEl.keepInBounds = menuEl => {
    keepInBounds(menuEl, {
      padding: config.menuPadding,
      container: getBoundsContainer(listService),
    });
  };
  filtersContainerEl.appendChild(filtersOptionsContainerEl);

  const filtersMenuHeaderEl = document.createElement('div');
  filtersMenuHeaderEl.classList.add('list-filters__options-header');
  filtersOptionsContainerEl.appendChild(filtersMenuHeaderEl);

  const filtersMenuHeaderTitleEl = document.createElement('div');
  filtersMenuHeaderTitleEl.classList.add('list-filters__options-title');
  filtersMenuHeaderTitleEl.innerHTML = resolveText(listService.listData.texts, 'filtersTitle');

  filtersMenuHeaderEl.appendChild(filtersMenuHeaderTitleEl);

  const filtersMenuHeaderClearButtonEl = document.createElement('div');
  filtersMenuHeaderClearButtonEl.classList.add('list-filters__options-clear-button');
  filtersMenuHeaderClearButtonEl.addEventListener('click', () => {
    filtersOptionsContainerEl
      .querySelectorAll('.list-filters__option-items')
      .forEach(optionItemsEl => {
        const filterType = optionItemsEl.dataset.listFilterType;

        optionItemsEl
          .querySelectorAll('.list-filters__option-item[data-is-selected]')
          .forEach(optionItemEl => {
            optionItemEl.removeAttribute('data-is-selected');
            optionItemEl.querySelector('.list-filters__option-item-icon').innerHTML =
              filterType == 'radio' ? 'radio_button_unchecked' : 'check_box_outline_blank';
          });
      });
    updateFilterAmount(listService);
    updateFilterTogglerLabels(listService);
    listService.loadData({}, true);
    closeMenu('list-filters-menu-' + listService.key);
  });
  filtersMenuHeaderEl.appendChild(filtersMenuHeaderClearButtonEl);

  const filtersMenuHeaderClearButtonIconEl = document.createElement('div');
  filtersMenuHeaderClearButtonIconEl.classList.add(
    'list-filters__options-clear-button-icon',
    'icon'
  );
  filtersMenuHeaderClearButtonIconEl.innerHTML = 'close';
  filtersMenuHeaderClearButtonEl.appendChild(filtersMenuHeaderClearButtonIconEl);

  const filtersMenuHeaderClearButtonTextEl = document.createElement('div');
  filtersMenuHeaderClearButtonTextEl.classList.add('list-filters__options-clear-button-text');
  filtersMenuHeaderClearButtonTextEl.innerHTML = resolveText(
    listService.listData.texts,
    'filtersClearAll'
  );
  filtersMenuHeaderClearButtonEl.appendChild(filtersMenuHeaderClearButtonTextEl);

  const filterOptionsEl = getListFilterOptions(listService);
  filtersOptionsContainerEl.appendChild(filterOptionsEl);

  return filtersContainerEl;
};

function getListFilterOptions(listService) {
  const filterOptionsEl = document.createElement('div');
  filterOptionsEl.classList.add('list-filters__options');

  const filterOptions = listService.listData.config.filters || [];

  filterOptions.forEach(filter => {
    const filterOptionEl = document.createElement('div');
    filterOptionEl.classList.add('list-filters__option', '-type-' + filter.type);
    filterOptionsEl.appendChild(filterOptionEl);

    const filterOptionLabelEl = document.createElement('div');
    filterOptionLabelEl.classList.add('list-filters__option-label');
    filterOptionLabelEl.innerHTML = resolveText(
      listService.listData.texts,
      'filterLabel.' + filter.key
    );
    filterOptionEl.appendChild(filterOptionLabelEl);

    const filterOptionItemsEl = document.createElement('div');
    filterOptionItemsEl.classList.add('list-filters__option-items');
    filterOptionItemsEl.dataset.listFilter = filter.key;
    filterOptionItemsEl.dataset.listFilterValueColumn = filter.valueColumn;
    filterOptionItemsEl.dataset.listFilterWhereColumn = filter.whereColumn;
    filterOptionItemsEl.dataset.listFilterType = filter.type;
    filterOptionEl.appendChild(filterOptionItemsEl);

    let filterOptionsContainerEl = filterOptionItemsEl;

    if (filter.render == 'menu') {
      const menuId = 'list-filters-menu-' + listService.key + '-' + filter.key;

      const filterOptionMenuContainerEl = document.createElement('div');
      filterOptionMenuContainerEl.classList.add('list-filters__option-menu-container');
      filterOptionItemsEl.appendChild(filterOptionMenuContainerEl);

      const filterOptionMenuTogglerEl = document.createElement('div');
      filterOptionMenuTogglerEl.classList.add(
        'list-filters__option-menu-toggler',
        'list-filters__option-item',
        'no-select',
        '-has-icon',
        '-down'
      );
      filterOptionMenuTogglerEl.dataset.toggleMenu = menuId;
      filterOptionMenuContainerEl.appendChild(filterOptionMenuTogglerEl);

      const filterOptionMenuTogglerIconEl = document.createElement('div');
      filterOptionMenuTogglerIconEl.classList.add(
        'list-filters__option-menu-toggler-icon',
        'list-filters__option-item-icon',
        'icon'
      );
      filterOptionMenuTogglerIconEl.innerHTML = 'keyboard_arrow_down';
      filterOptionMenuTogglerEl.appendChild(filterOptionMenuTogglerIconEl);

      const togglerLabel = resolveText(listService.listData.texts, filter.labelSelectButton);
      const togglerLabelN = resolveText(listService.listData.texts, filter.labelSelectButtonN);
      const filterOptionMenuTogglerLabelEl = document.createElement('div');
      filterOptionMenuTogglerLabelEl.classList.add(
        'list-filters__option-menu-toggler-label',
        'list-filters__option-item-label'
      );
      filterOptionMenuTogglerLabelEl.dataset.listFilterLabel = togglerLabel;
      filterOptionMenuTogglerLabelEl.dataset.listFilterLabelN = togglerLabelN;
      filterOptionMenuTogglerLabelEl.innerHTML = togglerLabel;
      filterOptionMenuTogglerEl.appendChild(filterOptionMenuTogglerLabelEl);

      const filterOptionMenuEl = document.createElement('div');
      filterOptionMenuEl.classList.add(
        'list-filters__option-menu',
        'menu-overlay__wrapper',
        '-compact',
        '-left'
      );
      filterOptionMenuEl.dataset.menu = menuId;
      filterOptionMenuContainerEl.appendChild(filterOptionMenuEl);

      const filterOptionMenuItemsEl = document.createElement('div');
      filterOptionMenuItemsEl.classList.add('list-filters__option-menu-items');
      filterOptionMenuItemsEl.dataset.menu = menuId;
      filterOptionMenuItemsEl.keepInBounds = menuEl => {
        keepInBounds(menuEl, {
          padding: config.menuPadding,
          container: getBoundsContainer(listService),
        });
      };
      filterOptionMenuEl.appendChild(filterOptionMenuItemsEl);

      filterOptionsContainerEl = filterOptionMenuItemsEl;
    }

    filter.options.forEach(option => {
      const filterOptionItemEl = getFilterOption(listService, option, filter.type);
      filterOptionsContainerEl.appendChild(filterOptionItemEl);
    });
  });

  return filterOptionsEl;
}

function getFilterAmount(listService, selector) {
  selector =
    selector ||
    '[data-menu="list-filters-menu-' + listService.key + '"] .list-filters__option-items';
  let amount = 0;
  listService.wrapper.querySelectorAll(selector).forEach(optionItemsEl => {
    amount += optionItemsEl.querySelectorAll('.list-filters__option-item[data-is-selected]').length;
  });
  return amount;
}

function updateFilterAmount(listService) {
  const amount = getFilterAmount(listService);
  listService.wrapper
    .querySelectorAll('.list-filters__button-amount, .list-filters__options-clear-button')
    .forEach(el => {
      el.classList.toggle('-active', amount > 0);
    });
}

function getFilterOption(listService, option, type) {
  const icon = type == 'radio' ? 'radio_button_unchecked' : 'check_box_outline_blank';
  const iconChecked = type == 'radio' ? 'radio_button_checked' : 'check_box';

  const filterOptionItemEl = document.createElement('div');
  filterOptionItemEl.classList.add(
    'list-filters__option-item',
    'no-select',
    '-has-icon',
    '-' + type
  );
  filterOptionItemEl.dataset.listFilterValue = option.value;

  const filterOptionItemIconEl = document.createElement('div');
  filterOptionItemIconEl.classList.add('list-filters__option-item-icon', 'icon');
  filterOptionItemIconEl.innerHTML = icon;
  filterOptionItemEl.appendChild(filterOptionItemIconEl);

  const filterOptionItemLabelEl = document.createElement('div');
  filterOptionItemLabelEl.classList.add('list-filters__option-item-label');
  filterOptionItemLabelEl.innerHTML = resolveText(listService.listData.texts, option.label);
  filterOptionItemEl.appendChild(filterOptionItemLabelEl);

  filterOptionItemEl.addEventListener('click', () => {
    const isSelected = filterOptionItemEl.hasAttribute('data-is-selected');
    if (isSelected) {
      filterOptionItemEl.removeAttribute('data-is-selected');
      filterOptionItemIconEl.innerHTML = icon;
    } else {
      if (type == 'radio') {
        filterOptionItemEl
          .closest('.list-filters__option-items')
          .querySelectorAll('.list-filters__option-item')
          .forEach(itemEl => {
            itemEl.removeAttribute('data-is-selected');
            itemEl.querySelector('.list-filters__option-item-icon').innerHTML = icon;
          });
      }
      filterOptionItemEl.setAttribute('data-is-selected', '');
      filterOptionItemIconEl.innerHTML = iconChecked;
    }
    updateFilterAmount(listService);
    updateFilterTogglerLabels(listService);
    listService.loadData({}, true);
  });
  return filterOptionItemEl;
}

function updateFilterTogglerLabels(listService) {
  listService.wrapper.querySelectorAll('[data-list-filter]').forEach(listFilterEl => {
    const togglerLabelEl = listFilterEl.querySelector('.list-filters__option-menu-toggler-label');
    if (togglerLabelEl) {
      const label = togglerLabelEl.dataset.listFilterLabel;
      const labelN = togglerLabelEl.dataset.listFilterLabelN;
      const amount = getFilterAmount(
        listService,
        '[data-list-filter="' + listFilterEl.dataset.listFilter + '"]'
      );

      if (amount == 1) {
        togglerLabelEl.innerHTML = listFilterEl
          .querySelector(
            '.list-filters__option-item[data-is-selected] .list-filters__option-item-label'
          )
          .innerHTML.trim();
      } else if (amount > 1) {
        togglerLabelEl.innerHTML = labelN.replace('{n}', amount);
      } else {
        togglerLabelEl.innerHTML = label;
      }
    }
  });
}
