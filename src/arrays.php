<?php

/**
 * Reads a value from the given array at the specified index/key.
 * <br><br>
 * Unlike the usual array access operator [], this function does not generate warnings when
 * the key is not present on the array; instead, it returns null or a default value.
 *
 * @param array         $array The target array.
 * @param number|string $key   The list index or map key.
 * @param mixed         $def   An optional default value.
 *
 * @return mixed
 */
function get (array $array = null, $key, $def = null)
{
  if (!is_array ($array))
    return null;

  return isset ($array[$key]) ? $array[$key] : $def;
}

/**
 * Checks if either the specified key is missing from the given array or it's corresponding value in the array is empty.
 *
 * @param array      $array An array reference.
 * @param string|int $key   An array key / offset.
 *
 * @return bool True if the key is missing or the corresponding value in the array is empty (null or empty string).
 * @see is_empty()
 */
function missing (array &$array, $key)
{
  return !array_key_exists ($key, $array) || is_null ($x = $array[$key]) || $x === '';
}

/**
 * Concatenates array
 * @param array $a
 * @param array $b
 */
function concat (array &$a, array $b)
{
  $a = array_merge ($a, $b);
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
 * Extracts from an array all elements where the specified field matches the given value.
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
    if (is_object ($arr[0])) {
      if ($strict) {
        foreach ($arr as $v)
          if ($v->$fld === $val)
            $out[] = $v;
      }
      else foreach ($arr as $v)
        if ($v->$fld == $val)
          $out[] = $v;
    }
    if (is_array ($arr[0])) {
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
 * Searches an array for the first element where the specified field matches the given value.
 * Supports arrays of objects or arrays of arrays.
 *
 * @param array  $arr
 * @param string $fld
 * @param mixed  $val
 * @param bool   $strict TRUE to perform strict equality testing.
 *
 * @return array(value,index) The index and value of the first matching element or
 * array (null, false) if none found.
 * <p>Use <code>list ($v,$i) = array_find()</code> to immediately split the return value into separate variables.
 */
function array_find (array $arr, $fld, $val, $strict = false)
{
  if (count ($arr)) {
    if (is_object ($arr[0])) {
      if ($strict) {
        foreach ($arr as $i => $v)
          if ($v->$fld === $val)
            return [$v, $i];
      }
      else foreach ($arr as $i => $v)
        if ($v->$fld == $val)
          return [$v, $i];
    }
    if (is_array ($arr[0])) {
      if ($strict) {
        foreach ($arr as $i => $v)
          if ($v[$fld] === $val)
            return [$v, $i];
      }
      else foreach ($arr as $i => $v)
        if ($v[$fld] == $val)
          return [$v, $i];
    }
  }
  return [null, false];
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

function map ($src, callable $fn)
{
  if (is_array ($src)) {
    $o = [];
    foreach ($src as $k => $v)
      $o[] = $fn ($v, $k);
    return $o;
  }
  return $src;
}

function filter ($src, callable $fn)
{
  if (is_array ($src)) {
    $o = [];
    foreach ($src as $k => $v)
      if ($fn ($v, $k))
        $o[$k] = $v;
    return $o;
  }
  return $src;
}
