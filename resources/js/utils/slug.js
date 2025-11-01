export function getSlug(value) {
  // Trim and decode HTML entities
  value = value.trim();
  const textarea = document.createElement('textarea');
  textarea.innerHTML = value;
  value = textarea.value;

  // Lowercase and replace German umlauts/ß
  value = value
    .toLowerCase()
    .replace(/ö/g, 'oe')
    .replace(/ä/g, 'ae')
    .replace(/ü/g, 'ue')
    .replace(/ß/g, 'ss')
    .replace(/ẞ/g, 'ss');

  // Normalize and strip other diacritics (é → e, ł → l, ñ → n, etc.)
  value = value.normalize('NFD').replace(/[\u0300-\u036f]/g, '');

  // Replace spaces and underscores with hyphens
  value = value.replace(/[\s_]+/g, '-');

  // Remove all characters except a-z, 0-9, hyphen
  value = value.replace(/[^a-z0-9\-]/g, '');

  // Replace multiple hyphens with a single one
  value = value.replace(/-+/g, '-');

  // Trim leading/trailing hyphens
  value = value.replace(/^[-]+|[-]+$/g, '');

  return value;
}
