import { config } from "../config/config";
import { locale } from "../config/locale";

/**
 * Translate strings
 */
export function __(id, replace = {}, localeId, useLocale = locale) {

  // TODO window.app.locale
  const currentLocale = localeId || window.app?.locale || config.fallbackLocale;

  if (!useLocale[id]) {
    return id;
  }

  let str = useLocale[id][currentLocale] || useLocale[id][config.fallbackLocale] || id;

  for (const [key, value] of Object.entries(replace)) {
    str = str.replaceAll(`{${key}}`, value ?? '');
  }

  return str;
}
