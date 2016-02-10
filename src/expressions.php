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

if (! function_exists('e')) {
  /**
   * Escapes (secures) data for output.<br>
   *
   * <p>Array values are converted to space-separated value string lists.
   * > A useful use case for an array attribute is the `class` attribute.
   *
   * Object values generate either:
   * - a space-separated list of keys who's corresponding value is truthy;
   * - a semicolon-separated list of key:value elements if at least one value is a string.
   *
   * Boolean values will generate the string "true" or "false".
   *
   * NULL is converted to an empty string.
   *
   * Strings are HTML-encoded.
   *
   * @param mixed $o
   * @return string
   */
  function e ($o)
  {
    switch (gettype ($o)) {
      case 'string':
        break;
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
    return htmlentities ($o, ENT_QUOTES, 'UTF-8', false);
  }
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
 * @see when
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
  foreach (func_get_args () as $a)
    if (isset($a) && $a !== '') return $a;
  return null;
}

/**
 * Returns `$a` if `$exp` is not:
 * - `null`
 * - `false`
 * - `''`
 * - 0
 *
 * Otherwise, it returns `$b` or `null` if `$b` is not specified.
 *
 * > <p>**Note:** string `'0'` is considered to be `true`.
 *
 * > <p>**Warning:** unlike the ternary ? operator, all arguments are always evaluated, regardless of the value of
 * `$exp`.
 *
 * @param boolean $exp
 * @param mixed   $a
 * @param mixed   $b
 * @return mixed
 * @see either
 * @see when
 */
function when ($exp, $a, $b = null)
{
  return $exp || $exp === '0' ? $a : $b;
}

/**
 * Builds a string with a list of the given items that are not empty (after begin trimmed), delimited by `$delimiter`.
 *
 * > <p>**Note:** an empty value is `null` or an empty string.
 *
 * @param string $delimiter
 * @param mixed  ...$args
 * @return string
 */
function enum ($delimiter)
{
  $args = func_get_args ();
  array_shift ($args);
  return join ($delimiter, array_prune (array_map ('trim', $args)));
}

/**
 * Swaps the values of the given variables.
 *
 * @param mixed $a A variable.
 * @param mixed $b A variable.
 */
function swap (& $a, & $b)
{
  $x = $a;
  $a = $b;
  $b = $x;
}

/**
 * Returns the class name of the argument or, if not an object, a description of its data type.
 *
 * @param mixed $x
 * @return string
 */
function typeOf ($x)
{
  return is_object ($x) ? get_class ($x) : gettype ($x);
}

/**
 * If the argument is an object, this returns its class name without the namespace part.
 * Other argument types are converted the same way {@see typeOf()} does.
 *
 * @param mixed $x
 * @return string
 */
function shortTypeOf ($x)
{
  if (is_object ($x)) {
    $n = explode ('\\', get_class ($x));
    return array_pop ($n);
  }
  return gettype ($x);
}

/**
 * If the argument is an object, this returns a formatted HTML span showing its class name without the namespace part,
 * but it also includes the namespace via a tooltip.
 * Other argument types are converted the same way {@see typeOf()} does, but enclosed within a `kbd` tag.
 *
 * @param mixed $x
 * @return string
 */
function typeInfoOf ($x)
{
  return is_object ($x) ? formatClassName (get_class ($x)) : '<kbd>' . gettype ($x) . '</kbd>';
}

/**
 * Returns a formatted HTML span showing the given class name without the namespace part, but it also including the
 * namespace via a tooltip.
 *
 * @param string $name
 * @return string
 */
function formatClassName ($name) {
  $n = explode ('\\', $name);
  $c = array_pop ($n);
  return sprintf ("<kbd class=info title='%s'>%s</kbd>", $name, $c);
}
