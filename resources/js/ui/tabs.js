/**
 * Initialize tabs
 */
export function initTabs() {
    document.querySelectorAll('[data-tabs]').forEach(tabsEl => {
        if (tabsEl._tabsEventAdded) return;
        tabsEl._tabsEventAdded = true;

        tabsEl.querySelectorAll('[data-tab]').forEach(tabEl => {
            tabEl.addEventListener('click', () => {
                const tabId = tabEl.dataset.tab;

                tabsEl.querySelectorAll('[data-tab], [data-tab-content]').forEach(tabEl => {
                    tabEl.classList.remove('-active');
                });
                
                tabsEl.querySelector(`[data-tab="${tabId}"]`).classList.add('-active');
                tabsEl.querySelector(`[data-tab-content="${tabId}"]`).classList.add('-active');
            });
        });
    });
}