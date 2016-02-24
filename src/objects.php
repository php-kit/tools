<?php

/**
 * Reads a value from the given object at the specified key.
 * <br><br>
 * Unlike the usual object access operator ->, this function does not generate warnings when
 * the key is not present on the object; instead, it returns null or the specified default value.
 *
 * @param object        $obj The target object.
 * @param number|string $key The property name.
 * @param mixed         $def An optional default value.
 *
 * @return mixed
 */
function property ($obj, $key, $def = null)
{
  return isset ($obj->$key) ? $obj->$key : $def;
}

/**
 * Converts a PHP object to an instance of the specified class.
 *
 * @param mixed  $instance
 * @param string $className Fully-qualified class name.
 *
 * @return mixed
 */
function object_toClass ($instance, $className)
{
  return unserialize (sprintf (
    'O:%d:"%s"%s',
    strlen ($className),
    $className,
    strstr (strstr (serialize ($instance), '"'), ':')
  ));
}

/**
 * Extracts the values with the given keys from a given object, in the same order as the key list.
 *
 * > **Tips:**
 * > - You may typecast the result to `(object)` if you need an object.
 * > - You may call `array_values()` on the result if you need a linear list of values; ex. for use with `list()`.
 *
 * @param object $o    The object.
 * @param array  $keys A list of keys to be extracted.
 * @param mixed  $def  An optional default value to be returned for non-existing keys.
 *
 * @return array A map of extracted values.
 */
function object_only ($o, array $keys, $def = null)
{
  $r = [];
  foreach ($keys as $k)
    $r[$k] = isset($o->$k) ? $o->$k : $def;
  return $r;
}

/**
 * Returns a map of the defined public non-static properties of the specified object.
 *
 * > **Note:** this is similar to {@see get_object_vars()} but when called from within a class method it still returns
 * only the public properties.
 *
 * @param object $o
 * @return array
 */
function object_publicProps ($o)
{
  return get_object_vars ($o);
}

/**
 * Returns a list of names of the defined public non-static properties of the specified object.
 *
 * @param object $o
 * @return string[]
 */
function object_propNames ($o)
{
  return array_keys (get_object_vars ($o));
}

/**
 * Copies properties from a source object (or array) into a given object.
 *
 * @param object       $target
 * @param object|array $src
 *
 * @throws Exception
 */
function extend ($target, $src)
{
  if (isset($src)) {
    if (is_object ($target)) {
      foreach ($src as $k => $v)
        $target->$k = $v;
    }
    else throw new InvalidArgumentException('Invalid target for ' . __FUNCTION__);
  }
}

/**
 * Copies non-empty properties from a source object (or array) into a given object.
 * > **Note:** empty properties are those containing null or an empty string.
 * > **Note:** if the object supports ArrayAccess, that interface will also be used for checking and assignement.
 *
 * @param object|ArrayAccess       $target
 * @param object|array|Traversable $src
 *
 * @throws Exception
 */
function extendExisting ($target, $src)
{
  $c = $target instanceof ArrayAccess;
  if (isset($src)) {
    if (is_object ($target)) {
      foreach ($src as $k => $v)
        if (property_exists ($target, $k))
          $target->$k = $v;
        else if ($c && $target->offsetExists ($k))
          $target[$k] = $v;
    }
    else throw new InvalidArgumentException('Invalid target for ' . __FUNCTION__);
  }
}

/**
 * Checks if a class (or class instance) uses a specific trait.
 *
 * @param string|object $class Class name or class instance.
 * @param string        $trait Fully qualified trait name.
 * @return bool
 */
function usesTrait ($class, $trait)
{
  return isset(class_uses ($class)[$trait]);
}

/**
 * Checks if a class (or class instance) implements a specific interface.
 *
 * > **Note:** for instances, it is better to use the {@see instanceof} operator.
 *
 * @param string|object $class Class name or class instance.
 * @param string        $interfaceName
 * @return bool
 */
function implementsInterface ($class, $interfaceName)
{
  return in_array ($interfaceName, class_implements ($class));
}
