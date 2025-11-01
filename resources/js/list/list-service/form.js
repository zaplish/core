/**
 * Get the form link
 */
export function getFormLink(listConfig, item) {
  const basePath = listConfig.key || '';
  const formLink = listConfig.editUri
    ? listConfig.editUri.replace('__ID__', item.id)
    : `/admin/${basePath}/edit/${item.id}`;
  return formLink;
}

/**
 * Apply events to open form in sidebar or modal
 */
export function applyFormLink(listConfig, item, formLinkEl) {
  formLinkEl.href = getFormLink(listConfig, item);

  if (listConfig.editView === 'sidebar' || listConfig.editView === 'modal') {
    applyFormEvents(listConfig, item, formLinkEl);
  }
}

/**
 * Apply events to open form in sidebar or modal
 */
// TODO
// [ ] Sidebar
// [ ] Modal
export function applyFormEvents(listConfig, item, formLinkEl) {
  formLinkEl.addEventListener('click', (ev) => {
    ev.preventDefault();
    
    console.log('click');
  });
}
