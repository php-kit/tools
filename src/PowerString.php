<?php

class PowerString implements Countable, IteratorAggregate, ArrayAccess
{
  /**
   * The string representation of this instance.
   *
   * Treat this as read-only - **do not modify** it directly!
   * @var string
   */
  public $S = '';

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
    $x    = new static;
    $x->S = $src;
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
    $x->S =& $src;
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
    return $this->S;
  }

  function charAt ($index)
  {
    $v = mb_substr ($this->S, $index, 1);
    return $v === false ? '' : $v;
  }

  function charCodeAt ($index)
  {
    $v = mb_substr ($this->S, $index, 1);
    return $v === false ? 0 : mb_ord ($v);
  }

  function concat ()
  {
    $this->S = $this->S . implode ('', func_get_args ());
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
    return mb_strlen ($this->S);
  }

  function endsWith ($search, $pos = 0)
  {
    return mb_substr ($this->S, $pos - strlen ($search)) === $search;
  }

  function getIterator ()
  {
    return new ArrayIterator (preg_split ('//u', 'abc', -1, PREG_SPLIT_NO_EMPTY));
  }

  function includes ($search, $from = 0)
  {
    return mb_strpos ($this->S, $search, $from) !== false;
  }

  function indexOf ($search, $from = 0)
  {
    return mb_strpos ($this->S, $search, $from);
  }

  function lastIndexOf ($search, $from = 0)
  {
    return mb_strrpos ($this->S, $search, $from);
  }

  function length ()
  {
    return mb_strlen ($this->S);
  }

  function match ($pattern, $flags = 0, $ofs = 0)
  {
    $isGlobal = self::toUnicodeRegex ($pattern);
    return $isGlobal
      ? (preg_match_all ($pattern, $this->S, $m, $flags, $ofs) ? $m : false)
      : (preg_match ($pattern, $this->S, $m, $flags, $ofs) ? $m : false);
  }

  function normalize ($form)
  {
    $this->S = Normalizer::normalize ($form);
    return $this;
  }

  function offsetExists ($offset)
  {
    return $offset < mb_strlen ($this->S) && $offset >= 0;
  }

  function offsetGet ($offset)
  {
    return $this->charAt ($offset);
  }

  function offsetSet ($offset, $value)
  {
    $this->S = mb_substr ($this->S, 0, $offset) . $value . mb_substr ($this->S, $offset + 1);
  }

  function offsetUnset ($offset)
  {
    $this->S = mb_substr ($this->S, 0, $offset) . mb_substr ($this->S, $offset + 1);
  }

  function repeat ($count)
  {
    $this->S = str_repeat ($this->S, $count);
    return $this;
  }

  function replace ($pattern, $replace)
  {
    $limit   = self::toUnicodeRegex ($pattern) ? -1 : 1;
    $this->S = is_callable ($replace)
      ? preg_replace_callback ($pattern, $replace, $this->S, $limit)
      : preg_replace ($pattern, $replace, $this->S, $limit);
    return $this;
  }

  function search ($pattern)
  {
    self::toUnicodeRegex ($pattern);
    if (!preg_match ($pattern, $this->S, $m, PREG_OFFSET_CAPTURE)) return false;
    return $m[0][1];
  }

  function slice ($begin, $end = null)
  {
    if ($end === null) $end = mb_strlen ($this->S);
    $this->S = mb_substr ($this->S, $begin, $end < 0 ? $end : $end - $begin);
    return $this;
  }

  function split ($pattern, $limit)
  {
    self::toUnicodeRegex ($pattern);
    return preg_split ($pattern, $this->S, $limit);
  }

  function startsWith ($search, $pos = 0)
  {
    return mb_substr ($this->S, $pos, strlen ($search)) === $search;
  }

  function substr ($start, $length = null)
  {
    $this->S = func_num_args () == 1
      ? mb_substr ($this->S, $start)
      : mb_substr ($this->S, $start, $length);
    return $this;
  }

  function substring ($indexA, $indexB = null)
  {
    $l = mb_strlen ($this->S);
    if (func_num_args () == 1) $indexB = $l;
    if ($indexA > $indexB) swap ($indexA, $indexB);
    if ($indexA < 0) $indexA = 0;
    if ($indexB < 0) $indexB = 0;
    if ($indexA > $l) $indexA = $l;
    if ($indexB > $l) $indexB = $l;
    $this->S = mb_substr ($this->S, $indexA, $indexB - $indexA);
    return $this;
  }

  function toLowerCase ()
  {
    $this->S = mb_strtolower ($this->S);
    return $this;
  }

  function toUpperCase ()
  {
    $this->S = mb_strtoupper ($this->S);
    return $this;
  }

  function trim ()
  {
    $this->S = preg_replace ('/^\s+|\s+$/u', '', $this->S);
    return $this;
  }

  function trimLeft ()
  {
    $this->S = preg_replace ('/^\s+/u', '', $this->S);
    return $this;
  }

  function trimRight ()
  {
    $this->S = preg_replace ('/\s+$/u', '', $this->S);
    return $this;
  }

}
