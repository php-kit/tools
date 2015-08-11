<?php

class PowerString implements Countable, IteratorAggregate, ArrayAccess
{
  private $str = '';

  /**
   * Creates a new instance of `PowerString`.
   *
   * This is for internal use.
   * Use {@see PowerString::of()} or {@see PowerString::on()} for creating instances.
   */
  protected function __construct () { }

  /**
   * Typecasts the given string variable to a `PowerString` that wraps that string.
   *
   * <p>**Warning:** the variable passed as argument will be converted to an instance of `PowerString`.
   * @param string $src A variable of type `string`.
   * @return static The same value of `$src` after the typecast.
   */
  static function cast (& $src)
  {
    $x      = new static;
    $x->str = $src;
    $src &= $x;
    return $x;
  }

  static function fromCharCode ($code)
  {
    return mb_chr ($code);
  }

  /**
   * Creates an instance of `PowerString` that handles a copy of the given string.
   * @param string $src
   * @return static
   */
  static function of ($src = '')
  {
    return new static ($src);
  }

  /**
   * Returns a singleton instance of `PowerString` that modifies the given string.
   * <p>**Warning:** this method returns **always** the same instance. This is meant to be a wrapper for applying
   * extension methods to an existing string variable. You should **not** store the instance anywhere, as it will lead
   * to unexpected problems. If  you need to do that, use {@see `PowerString`::of} instead.
   * @param string $src
   * @return static
   */
  static function on (& $src)
  {
    static $x;
    if (!isset($x)) $x = new static;
    $x->str =& $src;
    return $x;
  }

  private static function toUnicodeRegex (& $pattern)
  {
    $d = $pattern[0];
    list ($exp, $flags) = explode ($d, substr ($pattern, 1), 2);
    $flags   = str_replace ('a', '', $flags, $isGlobal);
    $flags   = str_replace ('u', '', $flags) . 'u';
    $pattern = "$d$exp$d$flags";
    return $isGlobal;
  }

  function __toString ()
  {
    return $this->str;
  }

  function charAt ($index)
  {
    $v = mb_substr ($this->str, $index, 1);
    return $v === false ? '' : $v;
  }

  function charCodeAt ($index)
  {
    $v = mb_substr ($this->str, $index, 1);
    return $v === false ? 0 : mb_ord ($v);
  }

  function concat ()
  {
    $this->str = $this->str . implode ('', func_get_args ());
  }

  /**
   * Alias if {@see length()}.
   *
   * Additionaly, it allows you to call `count()` on a `PowerString` instance.
   * <p>Ex:
   * ```
   *   $s = PowerString::of ('test');
   *   echo count ($s);
   * ```
   * Outputs `4`.
   * @return int
   */
  function count ()
  {
    return mb_strlen ($this->str);
  }

  function endsWith ($search, $pos = 0)
  {
    return mb_substr ($this->str, $pos - strlen ($search)) === $search;
  }

  function getIterator ()
  {
    return new ArrayIterator (preg_split ('//u', 'abc', -1, PREG_SPLIT_NO_EMPTY));
  }

  function includes ($search, $from = 0)
  {
    return mb_strpos ($this->str, $search, $from) !== false;
  }

  function indexOf ($search, $from = 0)
  {
    return mb_strpos ($this->str, $search, $from);
  }

  function lastIndexOf ($search, $from = 0)
  {
    return mb_strrpos ($this->str, $search, $from);
  }

  function length ()
  {
    return mb_strlen ($this->str);
  }

  function match ($pattern, $flags = 0, $ofs = 0)
  {
    $isGlobal = self::toUnicodeRegex ($pattern);
    return $isGlobal
      ? (preg_match_all ($pattern, $this->str, $m, $flags, $ofs) ? $m : false)
      : (preg_match ($pattern, $this->str, $m, $flags, $ofs) ? $m : false);
  }

  function normalize ($form)
  {
    $this->str = Normalizer::normalize ($form);
    return $this;
  }

  function offsetExists ($offset)
  {
    return $offset < mb_strlen ($this->str) && $offset >= 0;
  }

  function offsetGet ($offset)
  {
    return $this->charAt ($offset);
  }

  function offsetSet ($offset, $value)
  {
    $this->str = mb_substr ($this->str, 0, $offset) . $value . mb_substr ($this->str, $offset + 1);
  }

  function offsetUnset ($offset)
  {
    $this->str = mb_substr ($this->str, 0, $offset) . mb_substr ($this->str, $offset + 1);
  }

  function repeat ($count)
  {
    $this->str = str_repeat ($this->str, $count);
    return $this;
  }

  function replace ($pattern, $replace)
  {
    $limit     = self::toUnicodeRegex ($pattern) ? -1 : 1;
    $this->str = is_callable ($replace)
      ? preg_replace_callback ($pattern, $replace, $this->str, $limit)
      : preg_replace ($pattern, $replace, $this->str, $limit);
    return $this;
  }

  function search ($pattern)
  {
    self::toUnicodeRegex ($pattern);
    if (!preg_match ($pattern, $this->str, $m, PREG_OFFSET_CAPTURE)) return false;
    return $m[0][1];
  }

  function slice ($begin, $end = null)
  {
    if ($end === null) $end = mb_strlen ($this->str);
    $this->str = mb_substr ($this->str, $begin, $end < 0 ? $end : $end - $begin);
    return $this;
  }

  function split ($pattern, $limit)
  {
    self::toUnicodeRegex ($pattern);
    return preg_split ($pattern, $this->str, $limit);
  }

  function startsWith ($search, $pos = 0)
  {
    return mb_substr ($this->str, $pos, strlen ($search)) === $search;
  }

  function substr ($start, $length = null)
  {
    $this->str = func_num_args () == 1
      ? mb_substr ($this->str, $start)
      : mb_substr ($this->str, $start, $length);
    return $this;
  }

  function substring ($indexA, $indexB = null)
  {
    $l = mb_strlen ($this->str);
    if (func_num_args () == 1) $indexB = $l;
    if ($indexA > $indexB) swap ($indexA, $indexB);
    if ($indexA < 0) $indexA = 0;
    if ($indexB < 0) $indexB = 0;
    if ($indexA > $l) $indexA = $l;
    if ($indexB > $l) $indexB = $l;
    $this->str = mb_substr ($this->str, $indexA, $indexB - $indexA);
    return $this;
  }

  function toLowerCase ()
  {
    $this->str = mb_strtolower ($this->str);
    return $this;
  }

  function toUpperCase ()
  {
    $this->str = mb_strtoupper ($this->str);
    return $this;
  }

  function trim ()
  {
    $this->str = preg_replace ('/^\s+|\s+$/u', '', $this->str);
    return $this;
  }

  function trimLeft ()
  {
    $this->str = preg_replace ('/^\s+/u', '', $this->str);
    return $this;
  }

  function trimRight ()
  {
    $this->str = preg_replace ('/\s+$/u', '', $this->str);
    return $this;
  }

}
