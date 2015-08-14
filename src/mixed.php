<?php

/**
 * Unified interface for retrieving a value by property from an object or by key from an array.
 * @param mixed  $data
 * @param string $key
 * @param mixed  $default Value to return if the key doesn't exist.
 * @return mixed
 */
function getField ($data, $key, $default = null)
{
  if (is_object ($data))
    return property_exists ($data, $key) ? $data->$key : $default;
  if (is_array ($data))
    return array_key_exists ($key, $data) ? $data[$key] : $default;
  throw new \InvalidArgumentException;
}

/**
 * Unified interface to set a value on an object's property or at an array's key.
 * @param mixed  $data
 * @param string $key
 * @param mixed  $value
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
 * Unified interface for retrieving a reference to an object's property or to an array's element.
 *
 * If the key doesn't exist, it is initialized to a null value.
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

function getAt ($target, $path)
{
  $segs = explode ('.', $path);
  $cur  = $target;
  foreach ($segs as $seg) {
    if (is_null ($cur = getField ($cur, $seg))) break;;
  }
  return $cur;
}

function & getRefAt (& $target, $path)
{
  $segs = explode ('.', $path);
  $cur  = $target;
  foreach ($segs as $seg) {
    if (is_null ($cur =& getFieldRef ($cur, $seg))) break;;
  }
  return $cur;
}

function setAt (& $target, $path, $v, $assoc = false)
{
  $segs = explode ('.', $path);
  $cur  =& $target;
  foreach ($segs as $seg)
    $cur =& getFieldRef ($cur, $seg, [], !$assoc);
  $cur = $v;
}

function unsetAt (& $target, $path)
{
  $paths = explode ('.', $path);
  $key   = array_pop ($paths);
  $path  = implode ('.', $paths);
  $v     =& getRefAt ($target, $path);
  if (is_array ($v))
    unset ($v[$key]);
  else if (is_object ($v))
    unset ($v->$key);
  else throw new InvalidArgumentException ("Not an object or array");
}

