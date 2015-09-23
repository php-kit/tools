<?php

/**
 * Converts a boolean value to its string representation.
 *
 * @param bool $val
 *
 * @return string
 */
function boolToStr ($val)
{
  return (bool)$val ? 'true' : 'false';
}

/**
 * Enhanced version of boolval().
 * <p>Converts a truthy value or a textual description of a boolean value into a true boolean.
 * <p>It also supports evaluating Traversables.
 *
 * @param mixed $val 'true', 'yes', 'on', '1', non empty traversables and any truthy value evaluate to `true`.
 *                   All other values evaluate to `false`.
 *
 * @return bool
 */
function toBool ($val)
{
  if ($val instanceof Iterator)
    return $val->valid ();
  if ($val instanceof IteratorAggregate) {
    $it = $val->getIterator ();
    /** @var Iterator $it */
    $it->rewind ();
    return $it->valid ();
  }
  return is_string ($val) ? $val == 'true' || $val == '1' || $val == 'yes' || $val == 'on' : (bool)$val;
}

/**
 * Formats the given currency value into a string compatible with the pt_PT locale format.
 *
 * @param float $value
 *
 * @return string
 */
function formatMoney ($value)
{
  return number_format ($value, 2, ',', ' ');
}

/**
 * Converts a number in string format into a float value, taking into consideration the PT-PT locale.
 *
 * @param string|null $val
 *
 * @return float|null
 */
function toFloat ($val)
{
  if (is_null ($val) || $val === '')
    return null;
  $val = str_replace (' ', '', str_replace (',', '.', $val));
  return floatval ($val);
}

