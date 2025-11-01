import { DateTime } from 'luxon';
import { __ } from './locale';

export function formatDatetime(value, options = {}) {
  if (!value) return '';

  const {
    zone = DateTime.local().zoneName,
    relative = false
  } = options;

  const dateFormat = getUserDateFormat();
  const timeFormat = getUserTimeFormat();
  const fullFormat = `${dateFormat} ${timeFormat}`;

  const date = DateTime.fromISO(value, { zone: 'utc' }).setZone(zone);

  if (relative) {
    const now = DateTime.now().setZone(zone);
    const minutesAgo = Math.abs(Math.round(date.diffNow('minutes').minutes));

    if (date.diffNow('seconds').seconds >= -120) {
      return __('relativeDateJustNow');
    }

    if (date.hasSame(now, 'day')) {
      if (minutesAgo < 60) {
        return __('relativeDateMinutes', { minutes: minutesAgo });
      }
      return __('relativeDateToday', { time: date.toFormat(timeFormat) });
    }

    if (date.hasSame(now.minus({ days: 1 }), 'day')) {
      return __('relativeDateYesterday', { time: date.toFormat(timeFormat) });
    }

    return date.toFormat(fullFormat);
  }

  return date.toFormat(fullFormat);
}


export function getUserAmPm(locale = undefined) {
  return Intl.DateTimeFormat(locale).resolvedOptions().hour12 ?? false;
}

export function getUserTimeFormat(locale = undefined) {
  const isAmPm = getUserAmPm(locale);
  return isAmPm ? 'hh:mm a' : 'HH:mm';
}

function getUserDateFormat(locale = undefined) {
  const sampleDate = new Date(2025, 0, 2);

  const parts = Intl.DateTimeFormat(locale, {
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
  }).formatToParts(sampleDate);

  const formatMap = {
    year: 'yyyy',
    month: 'MM',
    day: 'dd',
  };

  return parts
    .map(part => formatMap[part.type] || part.value)
    .join('');
}
