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
 * Extracts from an object a list of values having specific property names, in the same order as the name list and
 * having the same cardinality of the later.
 *
 * ><p>Unlike {@see object_only}, properties that have no value on the target object will generate values of value $def
 * (defaults to null), so the cardinality of the resulting array matches that of the property names array.
 *
 * ><p>**Tips:**<p>
 * ><p>- You may typecast the result to `(object)` if you need an object.
 * ><p>- You may call `array_values()` on the result if you need a linear list of values; ex. for use with `list()`.
 *
 * @param array $o    The object.
 * @param array $keys A list of property names to be extracted.
 * @param mixed $def  An optional default value to be returned for non-existing keys.
 * @return array The sequential list of extracted values.
 */
function object_fields ($o, array $keys, $def = null)
{
  $r = [];
  foreach ($keys as $k)
    $r[$k] = isset($o->$k) ? $o->$k : $def;
  return $r;
}

/**
 * Extracts specific properties from an object.
 *
 * <p>Properties not present on the target object will not be present on the output array.
 *
 * ><p>**Tips:**<p>
 * ><p>- You may typecast the result to `(object)` if you need an object.
 * ><p>- You may call `array_values()` on the result if you need a linear list of values; ex. for use with `list()`.
 *
 * @param object $o    The object.
 * @param array  $keys A list of names of properties to extract.
 * @return array A map of the extracted values, with keys matching the corresponding property names.
 */
function object_only ($o, array $keys)
{
  return array_intersect_key (get_object_vars ($o), array_flip ($keys));
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
 * Merges properties from a source object (or array) into a target object.
 *
 * <p>Assignments are recursive.
 * <p>If the target property is an object implementing ArrayAccess, the assignment is performed via `[]`, otherwise it's
 * performed via `->`.
 *
 * @param object       $target
 * @param object|array $src
 * @throws InvalidArgumentException If any of the arguments is not of one of the expected types.
 */
function extend ($target, $src)
{
  $c = $target instanceof ArrayAccess;
  if (isset($src)) {
    if (is_iterable ($src)) {
      if (is_object ($target)) {
        foreach ($src as $k => $v) // iterates both objects and arrays
          if (isset($target->$k) && (is_array ($v) || is_object ($v))) {
            if (is_object ($target->$k))
              extend ($target->$k, $v);
            elseif (is_array ($target->$k))
              array_recursiveMergeInto ($target->$k, $v);
            elseif ($c) $target[$k] = $v;
            else $target->$k = $v;
          }
          elseif ($c) $target[$k] = $v;
          else $target->$k = $v;
      }
      else throw new InvalidArgumentException('Invalid target argument');
    }
    else throw new InvalidArgumentException('Invalid source argument');
  }
}

/**
 * Copies non-empty properties from a source object (or array) into a target object, but only those existing already on
 * that target.
 *
 * <p>Assignments are not recursive.
 * <p>If the target property is an object implementing ArrayAccess, the assignment is performed via `[]`, otherwise it's
 * performed via `->`.
 *
 * ><p>**Note:** empty properties are those containing null or an empty string.
 *
 * @param object|ArrayAccess            $target
 * @param object|array|Traversable|null $src If NULL, nothing happens.
 * @throws InvalidArgumentException If any of the arguments is not of one of the expected types.
 */
function mergeExisting ($target, $src)
{
  if (isset($src)) {
    if (is_iterable ($src)) {
      if (is_object ($target)) {
        if ($target instanceof ArrayAccess) {
          foreach ($src as $k => $v) {
            if ($target->offsetExists ($k))
              $target[$k] = $v;
          }
        }
        else foreach ($src as $k => $v)
          if (property_exists ($target, $k))
            $target->$k = $v;
      }
      else throw new InvalidArgumentException('Invalid target argument');
    }
    else throw new InvalidArgumentException('Invalid source argument');
  }
}


/**
 * Copies values from a source object (or array) into a target object, but only those whose keys are present on the
 * given list of allowed properties.
 *
 * <p>Assignments are not recursive.
 * <p>If the target property is an object implementing ArrayAccess, the assignment is performed via `[]`, otherwise
 * it's performed via `->`.
 *
 * ><p>**Note:** empty properties are those containing null or an empty string.
 *
 * @param object|ArrayAccess            $target
 * @param object|array|Traversable|null $src  If NULL, nothing happens.
 * @param array                         $only A List of property names.
 * @throws InvalidArgumentException If any of the arguments is not of one of the expected types.
 */
function mergeOnly ($target, $src, array $only)
{
  if (isset($src)) {
    if (is_iterable ($src)) {
      if (is_object ($target)) {
        $keys = array_flip ($only);
        if ($target instanceof ArrayAccess) {
          foreach ($src as $k => $v) {
            if (isset ($keys[$k]) && $target->offsetExists ($k))
              $target[$k] = $v;
          }
        }
        else foreach ($src as $k => $v)
          if (isset ($keys[$k]) && property_exists ($target, $k))
            $target->$k = $v;
      }
      else throw new InvalidArgumentException('Invalid target argument');
    }
    else throw new InvalidArgumentException('Invalid source argument');
  }
}

/**
 * Copies properties from a source object (or array) into a given object, but only for properties that are not yet set
 * on the tager.
 *
 * @param object       $target
 * @param object|array $src
 *
 * @throws Exception
 */
function defaults ($target, $src)
{
  if (isset($src)) {
    if (is_object ($target)) {
      foreach ($src as $k => $v)
        if (!exists ($target->$k))
          $target->$k = $v;
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

/**
 * Sets the value of a private/protected property of an object of any class, from outside of it.
 *
 * <p>Use of this function breaks encapsulation and is a bad practice, use it only as a last resort!
 *
 * @param object $obj   Target instance.
 * @param string $name  Property name.
 * @param mixed  $value Value to set.
 */
function forceSetProperty ($obj, $name, $value)
{
  $p = new ReflectionProperty($obj, $name);
  $p->setAccessible (true);
  $p->setValue ($obj, $value);
}
