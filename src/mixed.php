<?php

/**
 * Unified interface for checking if property exists an object or if a key exists on an array.
 *
 * @param array|object $data
 * @param string       $key
 * @return bool
 */
function hasField ($data, $key)
{
  if (is_object ($data))
    return property_exists ($data, $key) || ($data instanceof ArrayAccess && isset($data[$key]));
  if (is_array ($data))
    return array_key_exists ($key, $data);
  throw new \InvalidArgumentException;
}

/**
 * Unified interface for retrieving a value by property from an object or by key from an array.
 *
 * @param array|object $data
 * @param string       $key
 * @param mixed        $default Value to return if the key doesn't exist.
 * @return mixed
 */
function getField ($data, $key, $default = null)
{
  if (is_object ($data)) {
    if (property_exists ($data, $key))
      return $data->$key;
    if ($data instanceof ArrayAccess && isset($data[$key]))
      return $data[$key];
    return $default;
  }
  if (is_array ($data))
    return array_key_exists ($key, $data) ? $data[$key] : $default;
  throw new \InvalidArgumentException;
}

/**
 * Unified interface to set a value on an object's property or at an array's key.
 *
 * @param array|object $data
 * @param string       $key
 * @param mixed        $value
 */
function setField (&$data, $key, $value)
{
  if (is_object ($data))
    $data->$key = $value;
  else if (is_array ($data))
    $data[$key] = $value;
  else throw new \InvalidArgumentException;
}

/**
 * Extracts values having specific keys from the given array/object.
 *
 * @param array|object|null $target
 * @param array             $keys A list of keys to be extracted.
 * @return array A map of keys to extracted values or an empty array if $target is null.
 * @throws InvalidArgumentException If the target is not an array, object or null.
 */
function fields ($target, array $keys)
{
  if (is_array ($target))
    return array_only ($target, $keys);
  if (is_object ($target))
    return object_only ($target, $keys);
  if (is_null ($target))
    return [];
  throw new InvalidArgumentException ("Not an object or array");
}

/**
 * Unified interface for retrieving a reference to an object's property or to an array's element.
 *
 * If the key doesn't exist, it is initialized to a null value.
 *
 * @param mixed  $data
 * @param string $key
 * @param mixed  $default   Value to store at the specified key if that key doesn't exist.
 *                          Valid ony if `$createObj == false` (the default).
 * @param bool   $createObj When true, the `$default` is ignored and a new instance of StdClass is used instead.<br>
 *                          This avoids unnecessary object instantiations.
 * @return mixed Reference to the value.
 * @throws InvalidArgumentException
 */
function & getFieldRef (&$data, $key, $default = null, $createObj = false)
{
  if (is_object ($data)) {
    if (!property_exists ($data, $key))
      $data->$key = $createObj ? new StdClass : $default;
    return $data->$key;
  }
  if (is_array ($data)) {
    if (!array_key_exists ($key, $data))
      $data[$key] = $default;
    return $data[$key];
  }
  throw new InvalidArgumentException ("Not an object or array");
}

/**
 * Unified interface for retrieving a value by property name from an object or by key name from an array, using a
 * dot-delimited path to navigate a given data structure.
 *
 * @param array|object $data The target data structure.
 * @param string       $path A dot-delimited path.
 * @param mixed        $def  [optional] Default value if the key/property is missing or its value is null.
 * @return mixed|null
 */
function getAt ($data, $path, $def = null)
{
  $segs = $path === '' ? [] : explode ('.', $path);
  $cur  = $data;
  foreach ($segs as $seg) {
    if (is_null ($cur = getField ($cur, $seg))) break;;
  }
  return isset($cur) ? $cur : $def;
}

/**
 * Unified interface for retrieving a reference by property name from an object or by key name from an array, using a
 * dot-delimited path to navigate a given data structure.
 *
 * @param array|object $data The target data structure.
 * @param string       $path A dot-delimited path.
 * @return mixed|null
 */
function & getRefAt (& $data, $path)
{
  $segs = $path === '' ? [] : explode ('.', $path);
  $cur  = $data;
  foreach ($segs as $seg) {
    if (is_null ($cur =& getFieldRef ($cur, $seg))) break;;
  }
  return $cur;
}

/**
 * Unified interface for setting a value by property name on an object or by key name on an array, using a
 * dot-delimited path to navigate a given data structure.
 *
 * @param array|object $data  The target data structure.
 * @param string       $path  A dot-delimited path.
 * @param mixed        $v     The value.
 * @param bool         $assoc true if arrays should be provided for missing path nodes, otherwise objects will be
 *                            created.
 */
function setAt (& $data, $path, $v, $assoc = false)
{
  $segs = $path === '' ? [] : explode ('.', $path);
  $cur  =& $data;
  foreach ($segs as $seg)
    $cur =& getFieldRef ($cur, $seg, [], !$assoc);
  $cur = $v;
}

/**
 * Unified interface for unsetting a value by property name on an object or by key name on an array, using a
 * dot-delimited path to navigate a given data structure.
 *
 * @param array|object $data The target data structure.
 * @param string       $path A dot-delimited path.
 */
function unsetAt (& $data, $path)
{
  $paths = $path === '' ? [] : explode ('.', $path);
  $key   = array_pop ($paths);
  $path  = implode ('.', $paths);
  $v     =& getRefAt ($data, $path);
  if (is_array ($v))
    unset ($v[$key]);
  else if (is_object ($v))
    unset ($v->$key);
  else throw new InvalidArgumentException ("Not an object or array");
}
