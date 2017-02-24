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
 * Tests if the given string encodes a valid date and time in the specified format.
 * <p>By default, it tests the ISO8601 format.
 * <p>**Note:** wrong dates, like 30 of February, will fail validation.
 *
 * @param string $date
 * @param string $format
 *
 * @return int
 */
function is_datetime ($date, $format = 'Y-m-d H:i:s')
{
  $d = DateTime::createFromFormat ($format, $date);
  return $d && $d->format ($format) == $date;
}

/**
 * Tests if the given string encodes a valid date in the specified format.
 * <p>By default, it tests the ISO8601 format.
 * <p>**Note:** wrong dates or times, like 30 of February, will fail validation.
 *
 * @param string $date
 * @param string $format
 *
 * @return int
 */
function is_date ($date, $format = 'Y-m-d')
{
  $d = DateTime::createFromFormat ($format, $date);
  return $d && $d->format ($format) == $date;
}

/**
 * Returns the current date and time in YYYY-MM-DD HH:MM:SS format.
 *
 * @return string
 */
function now ()
{
  return date ('Y-m-d H:i:s');
}

/**
 * Returns the current date in YYYY-MM-DD format.
 *
 * @return string
 */
function today ()
{
  return date ('Y-m-d');
}
