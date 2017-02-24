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
 * Converts a string representation of a boolean value, or any other value, into a true boolean.
 *
 * <p>Despite the name (which conveys the meaning of this being the reverse of {@see boolToStr}), it also accepts
 * non-string values.
 *
 * ### String argument
 * The `'false'`, `'no'`, `'off'`, `'0'` and `''` values evaluate to `false`.
 * <p>All other string values evaluate to `true`.
 *
 * ### Non-string argument
 * The `NULL`, `0`, `FALSE` or `[]` values evaluate to `false`.
 * <p>All other non-string values evaluate to `true`, except for empty iterables
 * (see {@see Iterator} and {@see IteratorAggregate}).
 *
 * @param mixed $val
 * @return bool
 */
function strToBool ($val)
{
  return is_string ($val)
    ? $val !== '' && $val !== 'false' && $val !== '0' && $val !== 'no' && $val !== 'off'
    : toBool ($val);
}

/**
 * Converts a truthy value into a true boolean.
 *
 * <p>This is an enhanced version of {@see boolval}.
 * <p>Unlike direct boolean casts, string '0' is considered TRUE, as any other non-empty string.
 * <p>It returns FALSE for `NULL`, `''`, `0`, `FALSE` and `[]`.
 * <p>All other values return TRUE, except for empty iterables (see {@see Iterator} and {@see IteratorAggregate}).
 *
 * @param mixed $val
 *
 * @return bool
 */
function toBool ($val)
{
  if (is_object ($val)) {
    if ($val instanceof Iterator)
      return $val->valid ();
    if ($val instanceof IteratorAggregate) {
      $it = $val->getIterator ();
      /** @var Iterator $it */
      return $it->valid ();
    }
    return true;
  }
  return $val !== '0' ? (bool)$val : true;
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

/**
 * Always rounds up (unlike {@see round}).
 *
 * @param float|string $number
 * @param int          $precision
 * @return float|int
 */
function round_up ($number, $precision = 0)
{
  $fig = (int)str_pad ('1', $precision + 1, '0');
  return (ceil ((float)$number * $fig) / $fig);
}

/**
 * Always rounds down (unlike {@see round}).
 *
 * @param float|string $number
 * @param int          $precision
 * @return float|int
 */
function round_down ($number, $precision = 0)
{
  $fig = (int)str_pad ('1', $precision + 1, '0');
  return (floor ((float)$number * $fig) / $fig);
}

function friendlySize ($size, $precision = 0)
{
  $units = ['bytes', 'Kb', 'Mb', 'Gb', 'Tb'];
  $p     = 0;
  $s     = $size;
  $sc    = 1;
  $sc2   = 1;
  while (strlen ($s) > 3) {
    ++$p;
    $sc  *= 1024;
    $sc2 *= 1000;
    $s   = floor ($s / 1024);
  }
  $d = round (($size - $sc * $s) / $sc2, $precision);
  $s += $d;
  return "$s $units[$p]";
}

/**
 * Converts the argument into an iterator, if possible, otherwise it throws an exception.
 *
 * @param mixed $t            An iterable value or null. If null, an empty iterator is returned.
 * @param bool  $throwOnError If false, the function returns false.
 * @return Iterator|false
 */
function iteratorOf ($t, $throwOnError = true)
{
  if (is_array ($t))
    return new ArrayIterator ($t);
  if (is_object ($t)) {
    if ($t instanceof IteratorAggregate)
      return iterator ($t->getIterator ());
    if ($t instanceof Iterator)
      return $t;
  }
  if (is_null ($t))
    return new EmptyIterator;
  if ($throwOnError)
    throw new InvalidArgumentException("Value is not iterable");
  return false;
}

/**
 * Encodes a value as a javascript constant expression.
 * <p>The output is similar to {@see json_encode} but without quoted object keys.
 *
 * @param mixed $value     Any value type except `resource`.
 * @param bool  $formatted When TRUE, the output is formatted with indentation and proper spacing between elements.
 * @return string
 */
function javascriptLiteral ($value, $formatted = true)
{
  $o = json_encode ($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PARTIAL_OUTPUT_ON_ERROR |
                            JSON_PRETTY_PRINT);
  // Unquote keys that may be unquoted
  $o = preg_replace ('/^(\s*)"([^"\s]+)": /m', '$1$2: ', $o);
  // Compact arrays that have a single value
  $o = preg_replace ('/(^|: )\[\s+(\S+)\s+]/', '$1[ $2 ]', $o);
  return $formatted ? $o : preg_replace (['/^(.*?:)\s+/m', '/\n\s*/'], ['$1', ''], $o);
}
