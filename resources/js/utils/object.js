/**
 * Get a nested value from an object
 *
 * @param {*} obj
 * @param {*} path
 * @returns {any}
 */
export function getNestedValue(obj, path) {
  if (!obj || !path) return null;

  if (path.includes('.')) {
    return path
      .split('.')
      .reduce((acc, key) => (acc && acc[key] !== undefined ? acc[key] : null), obj);
  }
  return obj[path];
}
