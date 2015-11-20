<?php

global $_;
/**
 * Use this function to evaluate any expression in a string interpolator.
 *
 * Ex:
 * > global $_; // if code is inside a function
 * > `$x = "Your {$_(Static::call(1,$arg))} is ready";`
 *
 * @param mixed $v
 * @return mixed
 */
$_ = function ($v) { return $v; };

/**
 * Escapes (secures) data for output.<br>
 * Hyperblade extends Blade's output escaping with support for additional data types.
 *
 * <p>Array attribute values are converted to space-separated value string lists.
 * > A useful use case for an array attribute is the `class` attribute.
 *
 * Object attribute values generate either:
 * - a space-separated list of keys who's corresponding value is truthy;
 * - a semicolon-separated list of key:value elements if at least one value is a string.
 *
 * Boolean values will generate the string "true" or "false".
 *
 * @param mixed $o
 * @return string
 */
function e ($o)
{
  if (!is_string ($o)) {
    switch (gettype ($o)) {
      case 'boolean':
        return $o ? 'true' : 'false';
      case 'integer':
      case 'double':
        return strval ($o);
      case 'array':
        $at = [];
        $s  = ' ';
        foreach ($o as $k => $v) {
          if (!is_string ($v) && !is_numeric ($v))
            throw new \InvalidArgumentException ("Can't output an array with values of type " . gettype ($v));
          if (is_numeric ($k))
            $at[] = $v;
          else {
            $at[] = "$k:$v";
            $s    = ';';
          }
        }
        $o = implode ($s, $at);
        break;
      case 'NULL':
        return '';
      default:
        throw new \InvalidArgumentException ("Can't output a value of type " . gettype ($o));
    }
  }
  return htmlentities ($o, ENT_QUOTES, 'UTF-8', false);
}

/**
 * Checks if the specified value is not empty.
 *
 * <p>**Note:** an empty value is `null` or an empty string.
 *
 * <p>**Warning:** do not use this for checking the existence of array elements or object properties.<br>
 * `exists()` is not equivalent to `empty()` or `isset()`, as those are special language constructs.
 * <br>For instance, these expression will cause PHP warnings:
 * <code>
 *   if (empty($array[$key])
 *   if (empty($obj->$key)
 * </code>
 *
 * @param mixed $exp
 *
 * @return bool `true` if the value is not empty.
 */
function exists ($exp)
{
  return isset($exp) && $exp !== '';
}

/**
 * Returns either `$a` or `$b`, whichever is not empty. If both are empty, returns `$c` (defaults to `null`).
 *
 * <p>**Note:** an empty value is `null` or an empty string.
 *
 * @param mixed $a
 * @param mixed $b
 * @param mixed $c
 * @return mixed
 * @see when
 * @see iftrue
 */
function either ($a, $b, $c = null)
{
  return isset($a) && $a !== '' ? $a : (isset($b) && $b !== '' ? $b : $c);
}

/**
 * Returns the first argument that is not empty, or `null` if none is found.
 *
 * <p>**Note:** an empty value is `null` or an empty string.
 *
 * @param mixed ...$args
 * @return mixed|null
 */
function coalesce ()
{
  foreach (func_get_args() as $a)
    if (isset($a) && $a !== '') return $a;
  return null;
}

/**
 * Returns `$a` if `$exp` is *truthy* (not 0, `null` or an empty string, excluding `'0'`), otherwise it returns `$b`
 * or `null` if `$b` is not specified.
 *
 * @param mixed $exp
 * @param mixed $a
 * @param mixed $b
 * @return mixed
 * @see when
 * @see either
 */
function iftrue ($exp, $a, $b = null)
{
  return $exp && $exp !== '0' ? $a : $b;
}

/**
 * Returns `$a` if `$exp` is not *empty* or false, otherwise it returns `$b` or `null` if `$b` is not specified.
 *
 * <p>**Note:** an empty value is `null` or an empty string.
 *
 * <p>**Warning:** unline the ternary ? operator, all arguments are always evaluated, regardless of the value of
 * `$exp`.
 * @param boolean $exp
 * @param mixed   $a
 * @param mixed   $b
 * @return mixed
 * @see either
 * @see iftrue
 */
function when ($exp, $a, $b = null)
{
  return isset($exp) && $exp !== '' && $exp !== false ? $a : $b;
}

/**
 * Builds a string with a list of the given items that are not empty, delimited by `$delimiter`.
 *
 * <p>**Note:** an empty value is `null` or an empty string.
 *
 * @param string $delimiter
 * @param mixed ...$args
 * @return string
 */
function enum ($delimiter)
{
  $args = func_get_args();
  array_shift($args);
  return join ($delimiter, array_prune ($args));
}

/**
 * Swaps the values of the given variables.
 * @param mixed $a A variable.
 * @param mixed $b A variable.
 */
function swap (& $a, & $b) {
  $x = $a;
  $a = $b;
  $b = $x;
}

/**
 * Returns the class name of the argument or, if not an object, a description of its data type.
 * @param mixed $x
 * @return string
 */
function typeOf ($x) {
  return get_class($x) ?: gettype ($x);
}
