<?php
/*
 * A library that complements PHP's built-in array manipulation functions.
 */

/**
 * Searches for an element on a **sorted** array.
 *
 * @param array    $array      Where to search.
 * @param string   $what       What to search for.
 * @param int      $probe      The position where the element was found, or where it would be if it existed.
 * @param callable $comparator A function that returns zero for equality or a positive or negative number.
 *
 * @return bool True a match was found.
 */
function array_binarySearch (array $array, $what, &$probe, $comparator)
{
  $count = count ($array);
  $high  = $count - 1;
  $low   = 0;

  while ($high >= $low) {
    $probe      = (($high + $low) >> 1);
    $comparison = $comparator($array[$probe], $what);
    if ($comparison < 0)
      $low = $probe + 1;
    elseif ($comparison > 0) {
      if ($high == $low)
        break;
      $high = $probe;
    }
    else return true;
  }
  $probe = $low;
  return false;
}

/**
 * Merges an array to the target array, modifying the original.
 *
 * @param array $a Target being modified.
 * @param array $b Source data.
 */
function array_mergeInto (array &$a, array $b)
{
  $a = array_merge ($a, $b);
}

/**
 * Merges an array or an object to the target array, modifying the original, recursively.
 * It supports nested object properties.
 *
 * @param array                    $a Target being modified.
 * @param array|object|Traversable $b Source data.
 */
function array_recursiveMergeInto (array &$a, $b)
{
  foreach ($b as $k => $v) {
    if (!isset($a[$k]))
      $a[$k] = $v;
    else {
      $c = $a[$k];
      if (is_array ($c))
        $a[$k] = array_merge ($c, $v);
      elseif (is_object ($c))
        extend ($c, $v);
      else $a[$k] = $v;
    }
  }
}

/**
 * Merges an array, object or iterable to the target array, modifying the original, but only for keys already existing
 * on the target.
 *
 * @param array                    $array Target being modified.
 * @param array|object|Traversable $data  Source data.
 */
function array_mergeExisting (array &$array, $data)
{
  foreach ($data as $k => $v)
    if (array_key_exists ($k, $array))
      $array[$k] = $v;
}

/**
 * Merges an array, object or iterable to the target array, modifying the original, but only for keys already existing
 * on the target.
 *
 * @param array                    $array Target being modified.
 * @param array|object|Traversable $data  Source data.
 * @param array                    $only  A List of key names.
 */
function array_mergeOnly (array &$array, $data, array $only)
{
  $keys = array_flip ($only);
  foreach ($data as $k => $v)
    if (isset ($keys[$k]) && array_key_exists ($k, $array))
      $array[$k] = $v;
}

/**
 * Generates a new array where each element is a list of values extracted from the corresponding element on the input
 * array.
 *
 * @param array $a    The source data.
 * @param array $keys The keys of the values to be extracted from each $array element.
 * @param mixed $def  An optional default value to be returned for non-existing keys.
 * @return array      An array with the same cardinality as the input array.
 */
function array_extract (array $a, array $keys, $def = null)
{
  return map ($a, function ($e) use ($keys, $def) { return array_fields ($e, $keys, $def); });
}

/**
 * Extracts from an array a map of values having specific keys, in the same order as the given key list.
 *
 * ><p>**Note:** keys that have no value on the target array will generate values of value $def (defaults to null),
 * so the cardinality of the resulting array matches that of the keys array, unlike the result from {@see array_only}.
 *
 * ><p>**Tip:**<p>
 * ><p>- You may call `array_values()` on the result if you need a linear list of values; ex. for use with `list()`.
 *
 * @param array $a    The array.
 * @param array $keys A list of keys to be extracted.
 * @param mixed $def  An optional default value to be returned for non-existing keys.
 * @return array A subset of the original array, having the extracted values or defaults.
 */
function array_fields (array $a, array $keys, $def = null)
{
  $o = [];
  foreach ($keys as $k)
    $o[$k] = array_key_exists ($k, $a) ? $a[$k] : $def;
  return $o;
}

if (!function_exists ('array_only')) {
  /**
   * Returns a copy of the given array having only the specified keys.
   *
   * <p>Properties not present on the target array will not be present on the output array.
   *
   * @param array $a    The original array.
   * @param array $keys A list of keys to be copied.
   * @return array A subset of the original array.
   */
  function array_only (array $a, array $keys)
  {
    return array_intersect_key ($a, array_flip ($keys));
  }
}

/**
 * Searches an array for the first element where the specified field matches the given value.
 * Supports arrays of objects or arrays of arrays.
 *
 * @param array  $arr
 * @param string $fld
 * @param mixed  $val
 * @param int|string $key Outputs the key of the matched element or NULL if it was not found.
 * @param bool   $strict TRUE to perform strict equality testing.
 * @return array The value of the first matching element or NULL if none found.
 */
function array_find (array $arr, $fld, $val, &$key = null, $strict = false)
{
  if (isset ($arr[0])) {
    if (is_object ($arr[0])) {
      if ($strict) {
        foreach ($arr as $key => $v)
          if ($v->$fld === $val)
            return $v;
      }
      else foreach ($arr as $key => $v)
        if ($v->$fld == $val)
          return $v;
    }
    if (is_array ($arr[0])) {
      if ($strict) {
        foreach ($arr as $key => $v)
          if ($v[$fld] === $val)
            return $v;
      }
      else foreach ($arr as $key => $v)
        if ($v[$fld] == $val)
          return $v;
    }
  }
  $key = null;
  return null;
}

/**
 * Estracts from an array all elements where the specified field matches the given value.
 * Supports arrays of objects or arrays of arrays.
 *
 * @param array  $arr
 * @param string $fld
 * @param mixed  $val
 * @param bool   $strict TRUE to perform strict equality testing.
 *
 * @return array A list of matching elements.
 */
function array_findAll (array $arr, $fld, $val, $strict = false)
{
  $out = [];
  if (count ($arr)) {
    reset ($arr);
    $v = current ($arr);
    if (is_object ($v)) {
      if ($strict) {
        foreach ($arr as $v)
          if ($v->$fld === $val)
            $out[] = $v;
      }
      else foreach ($arr as $v)
        if ($v->$fld == $val)
          $out[] = $v;
    }
    elseif (is_array ($v)) {
      if ($strict) {
        foreach ($arr as $v)
          if ($v[$fld] === $val)
            $out[] = $v;
      }
      else foreach ($arr as $v)
        if ($v[$fld] == $val)
          $out[] = $v;
    }
  }
  return $out;
}

/**
 * Returns the values from a single column of the array, identified by the column key.
 * This is a simplified implementation of the native array_column function for PHP < 5.5 but it
 * additionally allows fetching properties from an array of objects.
 * Array elements can be objects or arrays.
 * The first element in the array is used to determine the element type for the whole array.
 *
 * @param array      $array
 * @param int|string $key Null value is not supported.
 *
 * @return array
 */
function array_getColumn (array $array, $key)
{
  return empty($array)
    ? []
    :
    (is_array ($array[0])
      ? array_map (function ($e) use ($key) {
        return $e[$key];
      }, $array)
      : array_map (function ($e) use ($key) {
        return $e->$key;
      }, $array)
    );
}

/**
 * Inserts a value with an optional key after an existing array element with a specific key, shifting other values to
 * make room.
 *
 * <p>This function preserves the current order and keys of the array elements.
 * <p>String keys are supported.
 * <p>If no array element with the specified key is found, the new value is appended (with the corresponding key) at
 * the end of the array.
 *
 * @param array           $array
 * @param int|string|null $afterKey When null, the value is appended to the array.
 * @param array           $values   The array to be inserted.
 * @return array
 */
function array_insertAfterKey (array $array, $afterKey, $values)
{
  $pos = array_keyIndex ($array, $afterKey);
  if (!isset($pos))
    $pos = count ($array);
  else ++$pos;
  return array_insert ($array, $pos, $values);
}

/**
 * Inserts a value with an optional key before an existing array element with a specific key, shifting other values to
 * make room.
 *
 * <p>This function preserves the current order and keys of the array elements.
 * <p>String keys are supported.
 * <p>If no array element with the specified key is found, the new value is prepended (with the corresponding key) to
 * the beginning of the array.
 *
 * @param array           $array
 * @param int|string|null $beforeKey When null, the value is prepended to the array. When empty string (or an invalid
 *                                   key) the value is appended to the array.
 * @param array           $values    The array to be inserted.
 * @return array
 */
function array_insertBeforeKey (array $array, $beforeKey, $values)
{
  return array_insert ($array, isset($beforeKey) ? array_keyIndex ($array, $beforeKey) : 0, $values);
}

/**
 * Inserts an array into another at the specified position, irrespective of the target's keys.
 *
 * @param array $target The array where data will be inserted into.
 * @param int   $pos    If offset is non-negative, the sequence will start at that offset in the array. If offset is
 *                      negative, the sequence will start that far from the end of the array.
 * @param array $source The data to be merged,
 * @return array        The resulting array.
 */
function array_insert (array $target, $pos, array $source)
{
  return array_merge (array_slice ($target, 0, $pos, true), $source, array_slice ($target, $pos, null, true));
}

/**
 * Gets the position of a key on an array.
 *
 * @param array      $array
 * @param string|int $key
 * @param int        $notFoundIndex Value to be returned if the key was not found.
 * @return int|null
 */
function array_keyIndex (array $array, $key, $notFoundIndex = null)
{
  $keys = array_flip (array_keys ($array));
  return isset($keys[$key]) ? $keys[$key] : $notFoundIndex;
}

/**
 * Returns the values from multiple columns of the array, identified by the given column keys.
 * Array elements can be objects or arrays.
 * The first element in the array is used to determine the element type for the whole array.
 *
 * @param array $array
 * @param array $keys A list of integer or string keys.
 *
 * @return array A list of objects or arrays, each one having the specified keys.
 * If some keys are absent from the input data, they will also be absent from the output.
 */
function array_getColumns (array $array, array $keys)
{
  $o = [];
  if (!empty($array)) {
    $mask = array_flip ((array)$keys);
    if (is_array ($array[0])) {
      foreach ($array as $k => $v)
        $o[$k] = array_intersect_key ($v, $mask);
    }
    else if (is_object ($array[0])) {
      foreach ($array as $k => $v)
        $o[$k] = (object)array_intersect_key ((array)$v, $mask);
    }
    else throw new RuntimeException('Cannot invoke array_getColumns on an array of primitives.');
  }
  return $o;
}

/**
 * Splits an array by one or more field values, generating a tree-like structure.
 * <p>The first argument is the input array.
 * <p>Each subsequent argument can be a field name or a function that returns the value to split on.
 * <p>Array elements can be arrays or objects.
 *
 * Ex:
 * ```
 * array_group ($data, 'type', 'date', function (v) { return datePart(v['date']); });
 * ```
 * Ex:
 * ```
 * $a = [
 *   [
 *     "type" => "animal",
 *     "color" => "red",
 *   ],
 *   [
 *     "type" => "animal",
 *     "color" => "green",
 *   ],
 *   [
 *     "type" => "robot",
 *     "color" => "red",
 *   ],
 *   [
 *     "type" => "robot",
 *     "color" => "green",
 *   ],
 *   [
 *     "type" => "robot",
 *     "color" => "blue",
 *   ],
 *   [
 *     "type" => "robot",
 *     "color" => "blue",
 *     "name" => "bee",
 *   ],
 * ];
 * array_group ($data, 'type', 'color');
 * ```
 * Generates:
 * ```
 * [
 *   "animal" => [
 *     "red" => [
 *       [
 *         "type" => "animal",
 *         "color" => "red",
 *       ],
 *     ],
 *     "green" => [
 *       [
 *         "type" => "animal",
 *         "color" => "green",
 *       ],
 *     ],
 *   ],
 *   "robot" => [
 *     "red" => [
 *       [
 *         "type" => "robot",
 *         "color" => "red",
 *       ],
 *     ],
 *     "green" => [
 *       [
 *         "type" => "robot",
 *         "color" => "green",
 *       ],
 *     ],
 *     "blue" => [
 *       [
 *         "type" => "robot",
 *         "color" => "blue",
 *       ],
 *       [
 *         "type" => "robot",
 *         "color" => "blue",
 *         "name" => "bee",
 *       ],
 *     ],
 *   ],
 * ]
 * ```
 *
 * @param array  $a       The source data.
 * @param string ...$args The field names.
 * @return array
 */
function array_group (array $a)
{
  $args = func_get_args ();
  array_shift ($args);
  $c = count ($args) - 1;
  $o = [];
  foreach ($a as $v) {
    $ptr =& $o;
    foreach ($args as $n => $field) {
      // Must be string, otherwise decimal places will be truncated.
      $idx = is_callable ($field) ? $field($v) : (string)getField ($v, $field);
      if (!isset($ptr[$idx]))
        $ptr[$idx] = [];
      if ($n < $c)
        $ptr =& $ptr[$idx];
      else $ptr[$idx][] = $v;
    }
  }
  return $o;
}

/**
 * Converts a PHP array of maps to an array if instances of the specified class.
 *
 * @param array  $array
 * @param string $className
 *
 * @return array
 */
function array_hidrate (array $array, $className)
{
  $o = [];
  foreach ($array as $k => $v)
    $o[$k] = array_toClass ($v, $className);
  return $o;
}

/**
 * Reindexes the array using the specified key field.
 * Array items should be arrays or objects.
 *
 * @param array  $a     The source data.
 * @param string $field The field name.
 * @return array Self, for chaining.
 */
function array_indexBy (array $a, $field)
{
  return map ($a, function ($v, &$k) {
    $k = getField ($v, $k);
    return $v;
  });
}

/**
 * Calls a function for each element of an array.
 * The function will receive one argument for each specified column.
 *
 * @param array    $data
 * @param array    $cols
 * @param callable $fn
 */
function array_iterateColumns (array $data, array $cols, callable $fn)
{
  foreach ($data as $r)
    call_user_func_array ($fn, array_fields ($r, $cols));
}

/**
 * Calls a transformation function for each element of an array.
 * The function will receive one argument for each specified column.
 * It should return an array/object that will replace the original array element.
 * Unlike array_map, the original keys will be preserved.
 *
 * @param array    $data
 * @param array    $cols
 * @param callable $fn
 *
 * @return array A transformed copy of the input array.
 */
function array_mapColumns (array $data, array $cols, callable $fn)
{
  $o = [];
  foreach ($data as $k => $r) {
    $o[$k] = call_user_func_array ($fn, array_fields ($r, $cols));
  }
  return $o;
}

/**
 * Sorts an array by one or more field values.
 * Ex: array_orderBy ($data, 'volume', SORT_DESC, 'edition', SORT_ASC);
 *
 * @return array
 */
function array_orderBy ()
{
  $args = func_get_args ();
  $data = array_shift ($args);
  foreach ($args as $n => $field) {
    if (is_string ($field)) {
      $tmp = [];
      foreach ($data as $key => $row)
        $tmp[$key] = $row[$field];
      $args[$n] = $tmp;
    }
  }
  $args[] = &$data;
  call_user_func_array ('array_multisort', $args);
  return array_pop ($args);
}

/**
 * Returns the input array stripped of null elements (with strict comparison).
 *
 * ><p>**Note:** the resulting array may have sequence gaps on its keys. Use {@see array_values} on it if you want
 * ordinal sequential keys.
 *
 * @param array $data
 * @return array
 */
function array_prune (array $data)
{
  return array_diff_key ($data, array_flip (array_keys ($data, null, true)));
}

/**
 * Returns the input array stripped of empty elements (those that are either `null` or empty strings).
 *
 * ><p>**Note:** `false`, `0` and`'0'` are considered NOT to be empty.
 * ><p>**Note:** the resulting array may have sequence gaps on its keys. Use {@see array_values} on it if you want
 * ordinal sequential keys.
 *
 * @param array $data
 * @return array
 */
function array_prune_empty (array $data)
{
  return array_diff_key ($data,
    array_flip (array_merge (array_keys ($data, null, true), array_keys ($data, '', true))));
}

/**
 * Returns a copy of the input array with a set of **keys** excluded from it.
 *
 * <p>Unlike {@see array_diff_key}, the keys are specified as a list of string values.
 *
 * @param array    $data
 * @param string[] $keys
 * @return array
 * @see array_diff if you want to exclude **values** instead of keys.
 */
function array_exclude (array $data, array $keys)
{
  return array_diff_key ($data, array_flip ($keys));
}

/**
 * Removes duplicates values from an array.
 *
 * <p>This is similar to {@see array_unique} but its much faster when dealing with large amounts of string values.
 *
 * @param array $data
 * @return array
 */
function array_deduplicate (array $data)
{
  return array_flip (array_flip ($data));
}

/**
 * Converts a PHP array map to an instance of the specified class.
 *
 * @param array  $array
 * @param string $className
 *
 * @return mixed
 */
function array_toClass (array $array, $className)
{
  return unserialize (sprintf (
    'O:%d:"%s"%s',
    strlen ($className),
    $className,
    strstr (serialize ($array), ':')
  ));
}

/**
 * Reads a value from the given array (or object implementing ArrayAccess) at the specified index/key.
 * <br><br>
 * Unlike the usual array access operator [], this function does not generate warnings when the key is not present on
 * the array; instead, it returns null or a default value.
 *
 * @param array|ArrayAccess $array The target array.
 * @param number|string     $key   The list index or map key.
 * @param mixed             $def   An optional default value.
 *
 * @return mixed
 */
function get ($array, $key, $def = null)
{
  return isset ($array[$key]) ? $array[$key] : $def;
}

/**
 * Calls a transformation function for each element of an array.
 *
 * The function will receive a value and a key for each array element and it should return a value that will replace
 * the original array element.
 *
 * Unlike array_map, the original keys will be preserved, unless the callback defines the
 * key parameter as a reference and modifies it.
 *
 * @param array|Traversable $src     Anything that can be iterated on a `foreach` loop.
 *                                   If `null`, `null` is returned.
 * @param callable          $fn      The callback.
 * @param bool              $useKeys [optional] When true, the iteration keys are passed as a second argument to the
 *                                   callback. Set to false for compatibility with native PHP functions used as
 *                                   callbacks, as they will complain if an extra argument is provided.
 * @return array
 */
function map ($src, callable $fn, $useKeys = true)
{
  if (isset ($src)) {
    if (is_array ($src) || $src instanceof Traversable) {
      $o = [];
      if ($useKeys)
        foreach ($src as $k => $v)
          $o[$k] = $fn ($v, $k);
      else foreach ($src as $k => $v)
        $o[$k] = $fn ($v);
      return $o;
    }
    throw new InvalidArgumentException;
  }
  return $src;
}

/**
 * Generates a new array where each item is an array consisting of the values from all specified arrays at the same
 * index.
 *
 * <p>The arrays are assumed to be indexed, not associative. The resulting array is indexed.
 *
 * #### Example
 * ```
 * $a = array(1, 2, 3, 4, 5);
 * $b = array("one", "two", "three");
 * $c = array("uno", "dos", "tres");
 *
 * $d = array_combine_values ($a, $b, $c);
 * ```
 * ##### Yields
 * ```
 * [
 *   [1, "one",   "uno"],
 *   [2, "two",   "dos"],
 *   [3, "three", "tres"],
 * ]
 * ```
 *
 * @param array ...$args
 * @return array
 */
function array_combine_values ()
{
  return array_map (null, func_get_args ());
}

/**
 * Filters an array or a {@see Traversable} sequence by calling a callback.
 *
 * The function will receive a value and a key for each array element and it should return `true` if the element will
 * be
 * kept on the resulting array, `false` to drop it.
 *
 * Unlike array_filter, the original keys will be preserved, unless the callback defines the
 * key parameter as a reference and modifies it.
 *
 * @param array|Traversable $src       Anything that can be iterated on a `foreach` loop.
 *                                     If `null`, `null` is returned.
 * @param callable          $fn        The callback.
 * @param bool              $resetKeys When true, the resulting keys will be regenerated as a monotonic increasing
 *                                     sequence.
 * @return array
 */
function filter ($src, callable $fn, $resetKeys = false)
{
  if (isset($src)) {
    if (is_array ($src))
      $o = array_filter ($src, $fn, ARRAY_FILTER_USE_BOTH);
    elseif ($src instanceof Traversable) {
      $o = [];
      foreach ($src as $k => $v)
        if ($fn ($v, $k))
          $o[$k] = $v;
    }
    else throw new InvalidArgumentException;
  }
  else return $src;
  return $resetKeys ? array_values ($o) : $o;
}

/**
 * Calls a transformation function for each element of an array and allows that function to drop elements from the
 * resulting array,
 *
 * The function will receive a value and a key for each array element and it should return a value that will replace
 * the original array element, or `null` to drop the element.
 *
 * Unlike array_map, the original keys will be preserved, unless the callback defines the
 * key parameter as a reference and modifies it.
 *
 * @param array|Traversable $src Anything that can be iterated on a `foreach` loop.
 *                               If `null`, `null` is returned.
 * @param callable          $fn  The callback.
 * @return array
 * @throws InvalidArgumentException If `$src` is not iterable.
 */
function mapAndFilter ($src, callable $fn)
{
  if (isset ($src)) {
    if (is_array ($src) || $src instanceof Traversable) {
      $o = [];
      foreach ($src as $k => $v)
        if (!is_null ($r = $fn ($v, $k)))
          $o[$k] = $r;
      return $o;
    }
    throw new InvalidArgumentException;
  }
  return $src;
}

/**
 * Checks if either the specified key is missing from the given array or it's corresponding value in the array is
 * empty.
 *
 * @param array|null $array The target array.
 * @param string|int $key   An array key / offset.
 *
 * @return bool True if the key is missing or the corresponding value in the array is empty (null or empty string).
 * @see exists()
 */
function missing (array $array = null, $key)
{
  return !isset($array[$key]) || $array[$key] === '';
}

/**
 * Converts all values that are empty strings to `null`.
 *
 * @param array $array The source array.
 * @param bool  $recursive
 * @return array The modified array.
 */
function array_normalizeEmptyValues (array $array, $recursive = false)
{
  foreach ($array as $k => &$v)
    if ($v === '')
      $v = null;
    elseif ($recursive && is_array ($v))
      $v = array_normalizeEmptyValues ($v, true);
  return $array;
}

/**
 * **ArrayOf** - Creates an array from the given arguments.
 *
 * <p>This is quite useful when used with the splat/spread operator.<br>
 * >Ex: `aof($a, ...$b)`
 *
 * @return array
 */
function aof ()
{
  return func_get_args ();
}

if (!function_exists ('last')) {
  /**
   * Returns the last element of an array.
   *
   * @param $array
   * @return mixed|false false if the array is empty.
   */
  function last ($array)
  {
    return end ($array);
  }
}

/**
 * Returns an iterator that iterates an array on reverse.
 *
 * @param array $a
 * @param bool  $preserveKeys If set to `true` numeric keys are preserved. Non-numeric keys are not affected by this
 *                            setting and will always be preserved.
 * @return \Generator
 */
function array_reverse_iterator (array $a, $preserveKeys = false)
{
  if ($preserveKeys)
    for (end ($a); ($key = key ($a)) !== null; prev ($a))
      yield $key => current ($a);
  else for (end ($a); ($key = key ($a)) !== null; prev ($a))
    yield current ($a);
}
