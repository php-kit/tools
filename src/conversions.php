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
 * Converts a number or a textual description of a boolean value into a true boolean.
 *
 * @param string|int $val 'true', 'yes', 'on' and '1' evaluate to true. All other values evaluate to false.
 *
 * @return bool
 */
function strToBool ($val)
{
  return $val == 'true' || $val == '1' || $val == 'yes' || $val == 'on';
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
  $val = str_replace (' ', '',str_replace (',', '.', $val));
  return floatval ($val);
}

