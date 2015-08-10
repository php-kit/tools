<?php

/**
 * Extracts the date part from a date encoded in ISO8601 format.
 * Ex: datePart('2012-10-05 18:23:45') returns '2012-10-05'.
 *
 * @param String $isoDateStr
 *
 * @return String An empty string if the date is not valid.
 */
function datePart ($isoDateStr)
{
  return substr ($isoDateStr, 0, strpos ($isoDateStr, ' '));
}

/**
 * Extracts the time part from a date encoded in ISO8601 format.
 * Ex: timePart('2012-10-05 18:23:45') returns '18:23:45'.
 *
 * @param String $isoDateStr
 *
 * @return String An empty string if the date is not valid.
 */
function timePart ($isoDateStr)
{
  return substr ($isoDateStr, strpos ($isoDateStr, ' ') + 1);
}

/**
 * Tests if the given string encodes a date in the specified format.
 * By default, it tests the ISO8601 format.
 *
 * @param string $date
 * @param string $format
 *
 * @return int
 */
function is_date ($date, $format = 'Y-m-d H:i:s')
{
  $d = DateTime::createFromFormat ($format, $date);
  return $d && $d->format ($format) == $date;
}

/**
 * Human-friendly textual descriptions for some dates.
 * For use by humanizeDate().
 */
$HUMANIZE_DATE_STR = [
  'today'     => 'Hoje, às',
  'yesterday' => 'Ontem, às',
];
/**
 * For the specified date, if its today or yesterday, it replaces it with a textual description.
 *
 * @param string $date
 *
 * @return string
 */
function humanizeDate ($date)
{
  global $HUMANIZE_DATE_STR;
  $today     = Date ('Y-m-d');
  $yesterday = Date ('Y-m-d', strtotime ("-1 days"));
  return str_replace ($yesterday, $HUMANIZE_DATE_STR['yesterday'],
    str_replace ($today, $HUMANIZE_DATE_STR['today'], $date));
}
