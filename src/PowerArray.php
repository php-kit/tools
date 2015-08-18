<?php

class PowerArray implements ArrayAccess, Countable, IteratorAggregate, Serializable
{
  /**
   * The array representation of this instance.
   *
   * Treat this as read-only - **do not modify** it directly!
   * @var string
   */
  public $A;

  /**
   * Creates an uninitialized instance of `PowerArray` that should not be used until the `data` property is set.
   *
   * Use {@see PowerArray::of()} or {@see PowerArray::on()} for creating instances.
   */
  protected function __construct () { }

  /**
   * Typecasts the given array variable to a `PowerArray` that wraps that array.
   *
   * <p>**Warning:** the variable passed as argument will be converted to an instance of `PowerArray`.
   * @param array $src A variable of type array.
   * @return static The same value of `$src` after the typecast.
   */
  static function cast (array & $src)
  {
    $x    = new static;
    $x->A = $src;
    $src &= $x;
    return $x;
  }

  /**
   * Creates an instance of `PowerArray` that handles a copy (on write) of the given array.
   * @param array $src
   * @return static
   */
  static function of (array $src = [])
  {
    $x = new static;
    $x->A = $src;
    return $x;
  }

  /**
   * Returns a singleton instance of `PowerArray` that modifies the given array.
   * <p>**Warning:** this method returns **always** the same instance. This is meant to be a wrapper for applying
   * extension methods to an existing array variable. You should **not** store the instance anywhere, as it will lead
   * to unexpected problems. If  you need to do that, use {@see PowerArray::of} instead.
   * @param array $src
   * @return static
   */
  static function on (array & $src)
  {
    static $x;
    if (!isset($x)) $x = new static;
    $x->A =& $src;
    return $x;
  }

  /**
   * Reads a value from the given array at the specified index/key.
   * This method implements the [] operator for reading.
   * <br><br>
   * Unlike the usual array access operator [], this method does not generate warnings when
   * the key is not present on the array; instead, it returns null.
   *
   * @param number|string $key The list index or map key.
   *
   * @return mixed
   */
  function __get ($key)
  {
    return isset ($this->A[$key]) ? $this->A[$key] : null;
  }

  /**
   * Implements the [] operator for writing.
   *
   * @param String $key
   * @param mixed  $value
   */
  function __set ($key, $value)
  {
    $this->A[$key] = $value;
  }

  /**
   * Implements the isset operator.
   *
   * @param String $key
   *
   * @return Boolean
   */
  function __isset ($key)
  {
    return isset ($this->A[$key]);
  }

  /**
   * Implements typecasting to string.
   * Outputs a PHP representation of the wrapped array.
   *
   * @return string
   */
  function __toString ()
  {
    return var_export ($this->A, true);
  }

  /**
   * Implements the unset operator.
   *
   * @param String $key
   */
  function __unset ($key)
  {
    unset ($this->A[$key]);
  }

  function all ()
  {
    return $this->A;
  }

  function append ()
  {
    call_user_func_array ('array_push', array_merge ($this->A, func_get_args ()));
    return $this;
  }

  /**
   * Searches for an element on a **sorted** array.
   *
   * @param string   $what       What to search for.
   * @param int      $probe      The position where the element was found, or where it would be if it existed.
   * @param callable $comparator A function that returns zero for equality or a positive or negative number.
   *
   * @return bool True a match was found.
   */
  function binarySearch ($what, &$probe, $comparator)
  {
    return array_binarySearch ($this->A, $what, $probe, $comparator);
  }

  public function count ()
  {
    return count ($this->A);
  }

  /**
   * Generates a new array where each element is a list of values extracted from the corresponding element on the input
   * array.
   * Result: array - An array with the same cardinality as the input array.
   *
   * @param array $keys The keys of the values to be extracted from each $array element.
   *
   * @return $this Self, for chaining.
   */
  function extract (array $keys)
  {
    $this->A = array_extract ($this->A, $keys);
    return $this;
  }

  /**
   * Extracts the values with the given keys from a given array, in the same order as the key list.
   *
   * @param array $keys A list of keys to be extracted.
   * @param mixed $def  An optional default value to be returned for non-existing keys.
   * @return $this Self, for chaining.
   * @see PowerArray::extract
   */
  function fields (array $keys, $def = null)
  {
    $this->A = array_fields ($this->A, $keys, $def);
    return $this;
  }

  /**
   * Calls a filtering function for each element of an array.
   * The function will receive as arguments the array element and its key.
   * It should return a boolean value that indicates whether the element should not be discarded or not.
   *
   * @param callable $fn
   *
   * @return $this Self, for chaining.
   */
  function filter (callable $fn)
  {
    $this->A = array_filter ($this->A, $fn, ARRAY_FILTER_USE_BOTH);
    return $this;
  }

  /**
   * Searches an array for the first element where the specified field matches the given value.
   * Supports arrays of objects or arrays of arrays.
   * Result: array(value,index) The index and value of the first matching element or
   * array (null, false) if none found.
   * <p>Use <code>list ($v,$i) = $array->find()</code> to immediately split the return value into separate variables.
   *
   * @param string $fld
   * @param mixed  $val
   * @param bool   $strict TRUE to perform strict equality testing.
   *
   * @return $this Self, for chaining.
   */
  function find ($fld, $val, $strict = false)
  {
    $this->A = array_find ($fld, $val, $strict);
    return $this;
  }

  /**
   * Estracts from an array all elements where the specified field matches the given value.
   * Supports arrays of objects or arrays of arrays.
   *
   * @param string $fld
   * @param mixed  $val
   * @param bool   $strict TRUE to perform strict equality testing.
   *
   * @return $this Self, for chaining.
   */
  function findAll ($fld, $val, $strict = false)
  {
    $this->A = array_findAll ($this->A, $fld, $val, $strict);
    return $this;
  }

  function first ()
  {
    return $this->A[0];
  }

  /**
   * Returns the values from a single column of the array, identified by the column key.
   * This is a simplified implementation of the native array_column function for PHP < 5.5 but it
   * additionally allows fetching properties from an array of objects.
   * Array elements can be objects or arrays.
   * The first element in the array is used to determine the element type for the whole array.
   *
   * @param int|string $key Null value is not supported.
   *
   * @return $this Self, for chaining.
   */
  function getColumn ($key)
  {
    $this->A = array_getColumn ($this->A, $key);
    return $this;
  }

  /**
   * Returns the values from multiple columns of the array, identified by the given column keys.
   * Array elements can be objects or arrays.
   * The first element in the array is used to determine the element type for the whole array.
   * Result: A list of objects or arrays, each one having the specified keys.
   * If some keys are absent from the input data, they will also be absent from the output.
   *
   * @param array $keys A list of integer or string keys.
   *
   * @return $this Self, for chaining.
   */
  function getColumns (array $keys)
  {
    $this->A = array_getColumns ($this->A, $keys);
    return $this;
  }

  public function getIterator ()
  {
    return $this->A;
  }

  /**
   * Splits the array by one or more field values, generating a tree-like structure.
   * <p>The first argument is the input array.
   * <p>Each subsequent argument can be a field name or a function that returns the value to split on.
   * <p>Array elements can be arrays or objects.
   *
   * Ex:
   * ```
   * $a->group ($data, 'type', 'date', function (v) { return datePart(v['date']); });
   * ```
   * Ex:
   * ```
   * $a = [
   *   [
   *     "type" => "animal",
   *     "color" => "red",
   *   ],
   *   [
   *     "type" => "animal",
   *     "color" => "green",
   *   ],
   *   [
   *     "type" => "robot",
   *     "color" => "red",
   *   ],
   *   [
   *     "type" => "robot",
   *     "color" => "green",
   *   ],
   *   [
   *     "type" => "robot",
   *     "color" => "blue",
   *   ],
   *   [
   *     "type" => "robot",
   *     "color" => "blue",
   *     "name" => "bee",
   *   ],
   * ];
   * A($a)->group ($data, 'type', 'color');
   * ```
   * Generates:
   * ```
   * [
   *   "animal" => [
   *     "red" => [
   *       [
   *         "type" => "animal",
   *         "color" => "red",
   *       ],
   *     ],
   *     "green" => [
   *       [
   *         "type" => "animal",
   *         "color" => "green",
   *       ],
   *     ],
   *   ],
   *   "robot" => [
   *     "red" => [
   *       [
   *         "type" => "robot",
   *         "color" => "red",
   *       ],
   *     ],
   *     "green" => [
   *       [
   *         "type" => "robot",
   *         "color" => "green",
   *       ],
   *     ],
   *     "blue" => [
   *       [
   *         "type" => "robot",
   *         "color" => "blue",
   *       ],
   *       [
   *         "type" => "robot",
   *         "color" => "blue",
   *         "name" => "bee",
   *       ],
   *     ],
   *   ],
   * ]
   * ```
   * @param string ...$args The field names.
   * @return $this Self, for chaining.
   */
  function group ()
  {
    $this->A = call_user_func_array ('array_group', array_merge ([$this->A], func_get_args ()));
    return $this;
  }

  /**
   * Converts a PHP array of maps to an array of instances of the specified class.
   *
   * @param string $className
   *
   * @return $this Self, for chaining.
   */
  function hidrate ($className)
  {
    $this->A = array_hidrate ($this->A, $className);
    return $this;
  }

  /**
   * Reindexes the array using the specified key field.
   * Array items should be arrays or objects.
   *
   * @param string $field The field name.
   * @return $this Self, for chaining.
   */
  function indexBy ($field)
  {
    $this->A = array_indexBy ($this->A, $field);
    return $this;
  }

  /**
   * Gets the key of the first element of the array that matches a given value.
   * @param mixed $value  The value to search for.
   * @param bool  $strict Determines if strict comparison (===) should be used during the search.
   *
   * @return mixed|false The key for needle if it is found in the array, false otherwise.
   *                     If needle is found in haystack more than once, the first matching key is returned. To
   *                     return the keys for all matching values, use array_keys with the optional search_value
   *                     parameter instead.
   */
  function indexOf ($value, $strict = true)
  {
    return array_search ($value, $this->A, $strict);
  }

  /**
   * Calls a function for each element of an array.
   * The function will receive one argument for each specified column.
   *
   * @param array    $cols
   * @param callable $fn
   *
   * @return $this Self, for chaining.
   */
  function iterateColumns (array $cols, callable $fn)
  {
    array_iterateColumns ($this->A, $cols, $fn);
    return $this;
  }

  /**
   * Merges records from two arrays using the specified primary key field.
   * When keys collide, the corresponding values are assumed to be arrays and they are merged.
   *
   * @param array|PowerArray $array
   * @param string           $field
   *
   * @return $this Self, for chaining.
   */
  function join ($array, $field)
  {
    // NOT IMPLEMENTED!!! DUMMY CODE!
    $this->A = array_join ($this->A, $array instanceof self ? $array->A : $array, $field);
    return $this;
  }

  /**
   * Gets all the keys of the array.
   * @return $this Self, for chaining.
   */
  function keys ()
  {
    $this->A = array_keys ($this->A);
    return $this;
  }

  /**
   * Gets all the keys of the array that match a given value.
   * @param mixed      $value  Only keys containing these values are returned.
   * @param bool|false $strict Determines if strict comparison (===) should be used during the search.
   * @return $this
   */
  function keysOf ($value, $strict = true)
  {
    $this->A = array_keys ($this->A, $value, $strict);
    return $this;
  }

  function last ()
  {
    return array_slice ($this->A, -1);
  }

  /**
   * Calls a transformation function for each element of the array.
   *
   * The function will receive a value and a key for each array element and it should return a value that will replace
   * the original array element.
   *
   * Unlike array_map, the original keys will be preserved, unless the callback defines the
   * key parameter as a reference and modifies the key.
   *
   * @param callable $fn The callback.
   * @return $this Self, for chaining.
   */
  function map (callable $fn)
  {
    $this->A = map ($this->A, $fn);
    return $this;
  }

  /**
   * Calls a transformation function for each element of the array.
   * The function will receive one argument for each specified column.
   * It should return an array/object that will replace the original array element.
   * Unlike array_map, the original keys will be preserved.
   *
   * @param array    $cols
   * @param callable $fn
   *
   * @return $this Self, for chaining.
   */
  function mapColumns (array $cols, callable $fn)
  {
    $this->A = array_mapColumns ($this->A, $cols, $fn);
    return $this;
  }

  /**
   * Merges another array or instance of this class with this one.
   * @param array|PowerArray $v
   */
  function merge ($v)
  {
    if (is_array ($v)) array_concat ($this->A, $v);
    else if ($v instanceof static)
      array_concat ($this->A, $v->A);
    else throw new InvalidArgumentException;
  }

  /**
   * Checks if either the specified key is missing from the array or it's corresponding value in the array is
   * empty.
   *
   * @param string|int $key An array key / offset.
   *
   * @return bool True if the key is missing or the corresponding value in the array is empty (null or empty string).
   * @see is_empty()
   */
  function missing ($key)
  {
    return missing ($this->A, $key);
  }

  public function offsetExists ($offset)
  {
    return isset ($this->A[$offset]);
  }

  public function offsetGet ($offset)
  {
    return isset ($this->A[$offset]) ? $this->A[$offset] : null;
  }

  public function offsetSet ($offset, $value)
  {
    $this->A[$offset] = $value;
  }

  public function offsetUnset ($offset)
  {
    unset ($this->A[$offset]);
  }

  /**
   * Sorts the array by one or more field values.
   * Ex: orderBy ($data, 'volume', SORT_DESC, 'edition', SORT_ASC);
   *
   * @return $this Self, for chaining.
   */
  function orderBy ()
  {
    $this->A = call_user_func_array ('array_orderBy', array_merge ([$this->A], func_get_args ()));
    return $this;
  }

  function pop ()
  {
    return array_pop ($this->A);
  }

  function prepend ()
  {
    call_user_func_array ('array_unshift', array_merge ($this->A, func_get_args ()));
    return $this;
  }

  /**
   * Returns the input array stripped of empty elements (those that are either `null` or empty strings).
   * @return $this Self, for chaining.
   */
  function prune ()
  {
    $this->A = array_prune ($this->A);
    return $this;
  }

  /**
   * Iteratively reduce the array to a single value using a callback function.
   * @param callable $fn      The callback function.
   * @param mixed    $initial If the optional initial is available, it will be used at the beginning of the process, or
   *                          as a final result in case the array is empty.
   * @return mixed The resulting value. If the array is empty and initial is not passed, it returns `null`.
   */
  function reduce (callable $fn, $initial = null)
  {
    return array_reduce ($this->A, $fn, $initial);
  }

  /**
   * Reindexes the current data into a series of sequential integer keys.
   *
   * This is useful to extract the data as a linear array with no discontinuous keys.
   *
   * @return $this Self, for chaining.
   */
  function reindex ()
  {
    $this->A = array_values ($this->A);
    return $this;
  }

  public function serialize ()
  {
    return serialize ($this->A);
  }

  public function unserialize ($serialized)
  {
    $this->A = unserialize ($serialized);
  }

  function shift ()
  {
    return array_shift ($this->A);
  }

  /**
   * Extract a slice of the array.
   * @param int  $start        If offset is non-negative, the sequence will start at that offset in the array. If
   *                           offset is negative, the sequence will start that far from the end of the array.
   * @param int  $len          If length is given and is positive, then the sequence will have that many elements
   *                           in it. If length is given and is negative then the sequence will stop that many
   *                           elements from the end of the array. If it is omitted, then the sequence will have
   *                           everything from offset up until the end of the array.
   * @param bool $preserveKeys Note that `slice()` will reorder and reset the array indices by default. You can
   *                           change this behaviour by setting `$preserveKeys` to true.
   * @return array
   */
  function slice ($start, $len, $preserveKeys = false)
  {
    return array_slice ($this->A, $start, $len, $preserveKeys);
  }

  /**
   * Remove a portion of the array and replace it with something else.
   * @param int        $offset      If offset is positive then the start of removed portion is at that offset from the
   *                                beginning of the input array. If offset is negative then it starts that far from
   *                                the end of the input array.
   * @param int|null   $length      If length is omitted, removes everything from offset to the end of the array. If
   *                                length is specified and is positive, then that many elements will be removed. If
   *                                length is specified and is negative then the end of the removed portion will be
   *                                that many elements from the end of the array. Tip: to remove everything from offset
   *                                to the end of the array when replacement is also specified, use count($input) for
   *                                length.
   * @param array|null $replacement If replacement array is specified, then the removed elements are replaced with
   *                                elements from this array. If offset and length are such that nothing is removed,
   *                                then the elements from the replacement array are inserted in the place specified by
   *                                the offset. Note that keys in replacement array are not preserved. If replacement
   *                                is just one element it is not necessary to put array() around it, unless the
   *                                element is an array itself.
   * @return $this Self, for chaining.
   */
  function splice ($offset, $length = null, array $replacement = null)
  {
    array_splice ($this->A, $offset, $length, $replacement);
    return $this;
  }

  /**
   * Converts a PHP array map to an instance of the specified class.
   *
   * @param string $className
   *
   * @return mixed An instance of the specified class.
   */
  function toClass ($className)
  {
    return array_toClass ($this->A, $className);
  }
}
