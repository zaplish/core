import { initMenus } from './ui/menu';
import { initInstall } from './auth/install';
import { initLogin } from './auth/login';
import { initResetPassword } from './auth/reset-password';
import { initNewPassword } from './auth/new-password';
import { initList } from './list/bootstrap';
import { initForm } from './form/bootstrap';
import { initFormEvents } from './form/events';
import { initListUpload } from './list/list-upload';
import { initModals } from './ui/modal';
import { initTooltips } from './ui/tooltip';
import { initTabs } from './ui/tabs';
import { initProfilePage } from './pages/profile';
import { initDeveloperSettingsPage } from './pages/settings-developer';

document.addEventListener('DOMContentLoaded', () => {
  // Forms
  initForm();
  initInstall();
  initLogin();
  initResetPassword();
  initNewPassword();
  initFormEvents();

  // Menus
  initMenus();

  // List
  initList();
  initListUpload();

  // Modals
  initModals();

  // Tooltips
  initTooltips();

  // Pages
  initProfilePage();
  initDeveloperSettingsPage();

  // Tabs
  initTabs();
});
