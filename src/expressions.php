<?php

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
global $_;
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
        foreach ($o as $k => $v)
          if (is_numeric ($k))
            $at[] = $v;
          else if (is_string ($v)) {
            $at[] = "$k:$v";
            $s    = ';';
          }
          else
            $at[] = $k;
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
 * Checks if the specified value is not null or an empty string.
 *
 * @param mixed $exp
 *
 * @return bool True if the value is empty.
 */
function exists ($exp)
{
  return isset($exp) && $exp !== '';
}

/**
 * Returns either $a or $default, whichever is not empty.
 * <br><br>
 * Returns $a if it is not empty (null or empty string), otherwise returns the $default value.
 *
 * @param mixed $a
 * @param mixed $default
 *
 * @return mixed
 */
function either ($a = null, $default = null)
{
  return isset($a) && $a !== '' ? $a : $default;
}

function firstNonNull ($a = null, $b = null, $c = null, $d = null)
{
  if (isset($a)) return $a;
  if (isset($b)) return $b;
  if (isset($c)) return $c;
  if (isset($d)) return $d;
  return null;
}

function ifset ($exp, $a, $b = null)
{
  if (isset($exp) && $exp !== '')
    return $a;
  return $b;
}

function iftrue ($exp, $a, $b = null)
{
  return $exp ? $a : $b;
}

function when ($exp, $a, $b = null)
{
  return $exp ? $a : $b;
}

function enum ($delimiter)
{
  $r = [];
  $t = func_num_args ();
  for ($n = 1; $n < $t; ++$n) {
    $v = func_get_arg ($n);
    if (!empty($v))
      $r[] = $v;
  }
  return join ($delimiter, $r);
}

