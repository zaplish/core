import { getNestedValue } from './object';

/**
 * Resolve text
 */
export function resolveText(texts, textId) {
  return getNestedValue(texts, textId) ?? textId;
}