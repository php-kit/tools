<?php

class A
{
  public $data;

  /**
   * Creates an uninitialized instance of A that should not be used until the `data` property is set.
   *
   * Use {@see A::of()} or {@see A::on()} for creating instances.
   */
  protected function __construct () { }

  /**
   * Creates an instance of A that handles a copy of the given array.
   * @param array $src
   * @return static
   */
  static function of (array $src = [])
  {
    return new static ($src);
  }

  /**
   * Creates an instance of A that modifies the given array.
   * @param array $src
   * @return static
   */
  static function on (array & $src)
  {
    $x       = new static;
    $x->data =& $src;
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
    return isset ($this->data[$key]) ? $this->data[$key] : null;
  }

  /**
   * Implements the [] operator for writing.
   *
   * @param String $key
   * @param mixed  $value
   */
  function __set ($key, $value)
  {
    $this->data[$key] = $value;
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
    return isset ($this->data[$key]);
  }

  /**
   * Implements typecasting to string.
   * Outputs a PHP representation of the wrapped array.
   *
   * @return string
   */
  function __toString ()
  {
    return var_export ($this->data, true);
  }

  /**
   * Implements the unset operator.
   *
   * @param String $key
   */
  function __unset ($key)
  {
    unset ($this->data[$key]);
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
  function array_binarySearch ($what, &$probe, $comparator)
  {
    return array_binarySearch ($this->data, $what, $probe, $comparator);
  }

  /**
   * Merges another array or instance of this class with this one.
   * @param array|A $v
   */
  function concat ($v)
  {
    if (is_array ($v)) array_concat ($this->data, $v);
    else if ($v instanceof static)
      array_concat ($this->data, $v->data);
    else throw new InvalidArgumentException;
  }

  /**
   * Generates a new array where each element is a list of values extracted from the corresponding element on the input
   * array.
   * Result: array - An array with the same cardinality as the input array.
   *
   * @param array $keys The keys of the values to be extracted from each $array element.
   *
   * @return A Self, for chaining.
   */
  function extract (array $keys)
  {
    $this->data = array_extract ($this->data, $keys);
    return $this;
  }

  /**
   * Extracts the values with the given keys from a given array, in the same order as the key list.
   *
   * @param array $keys A list of keys to be extracted.
   * @param mixed $def  An optional default value to be returned for non-existing keys.
   * @return A Self, for chaining.
   * @see A::extract
   */
  function fields (array $keys, $def = null)
  {
    $this->data = array_fields ($this->data, $keys, $def);
    return $this;
  }

  /**
   * Calls a filtering function for each element of an array.
   * The function will receive as arguments the array element and its index.
   * It should return boolean value that indicates if the element should not be discarded.
   *
   * @param callable $fn
   *
   * @return A Self, for chaining.
   */
  function filter (callable $fn)
  {
    $this->data = array_filter ($this->data, $fn);
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
   * @return A Self, for chaining.
   */
  function find ($fld, $val, $strict = false)
  {
    $this->data = array_find ($fld, $val, $strict);
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
   * @return A Self, for chaining.
   */
  function findAll ($fld, $val, $strict = false)
  {
    $this->data = array_findAll ($this->data, $fld, $val, $strict);
    return $this;
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
   * @return A Self, for chaining.
   */
  function getColumn ($key)
  {
    $this->data = array_getColumn ($this->data, $key);
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
   * @return A Self, for chaining.
   */
  function getColumns (array $keys)
  {
    $this->data = array_getColumns ($this->data, $keys);
    return $this;
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
   * @return A Self, for chaining.
   */
  function group ()
  {
    $this->data = call_user_func_array ('array_group', array_merge ([$this->data], func_get_args ()));
    return $this;
  }

  /**
   * Converts a PHP array of maps to an array if instances of the specified class.
   *
   * @param string $className
   *
   * @return A Self, for chaining.
   */
  function hidrate ($className)
  {
    $this->data = array_hidrate ($this->data, $className);
    return $this;
  }

  /**
   * Reindexes the array using the specified key field.
   * Array items should be arrays or objects.
   *
   * @param string $field The field name.
   * @return A Self, for chaining.
   */
  function indexBy ($field)
  {
    $this->data = array_indexBy ($this->data, $field);
    return $this;
  }

  /**
   * Calls a function for each element of an array.
   * The function will receive one argument for each specified column.
   *
   * @param array    $cols
   * @param callable $fn
   *
   * @return A Self, for chaining.
   */
  function iterateColumns (array $cols, callable $fn)
  {
    array_iterateColumns ($this->data, $cols, $fn);
    return $this;
  }

  /**
   * Merges records from two arrays using the specified primary key field.
   * When keys collide, the corresponding values are assumed to be arrays and they are merged.
   *
   * @param array|A $array
   * @param string  $field
   *
   * @return A Self, for chaining.
   */
  function join ($array, $field)
  {
    // NOT IMPLEMENTED!!! DUMMY CODE!
    $this->data = array_join ($this->data, $array instanceof self ? $array->data : $array, $field);
    return $this;
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
   * @return A Self, for chaining.
   */
  function map (callable $fn)
  {
    $this->data = map ($this->data, $fn);
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
   * @return A Self, for chaining.
   */
  function mapColumns (array $cols, callable $fn)
  {
    $this->data = array_mapColumns ($this->data, $cols, $fn);
    return $this;
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
    return missing ($this->data, $key);
  }

  /**
   * Sorts the array by one or more field values.
   * Ex: orderBy ($data, 'volume', SORT_DESC, 'edition', SORT_ASC);
   *
   * @return A Self, for chaining.
   */
  function orderBy ()
  {
    $this->data = call_user_func_array ('array_orderBy', array_merge ([$this->data], func_get_args ()));
    return $this;
  }

  /**
   * Returns the input array stripped of empty elements (those that are either `null` or empty strings).
   * @return A Self, for chaining.
   */
  function prune ()
  {
    $this->data = array_prune ($this->data);
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
    return array_toClass ($this->data, $className);
  }

}
