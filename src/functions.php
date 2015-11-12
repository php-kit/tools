<?php

/**
 * Allows an IDE to recognize a callable reference, so that it can validate it and allow one to refactor it.
 *
 * It is a no-op: it returns the input argument unmodified.
 *
 * @param callable $f A function reference, in the form of:
 *                    <ul>
 *                      <li> a Closure instance,
 *                      <li> a function name string,
 *                      <li> a "class::method" string, or
 *                      <li> an array of (className,methodName).
 *                      <li> an array of (classInstance,methodName).
 *                    </ul>
 * @return callable
 */
function fn (callable $f)
{
  return $f;
}

/**
 * Transforms a callable reference into a closure, with optional pre-bound `$this` and/or prepended arguments.
 * @param callable $fn       A function reference, in the form of:
 *                           <ul>
 *                           <li> a Closure instance,
 *                           <li> a function name string,
 *                           <li> a "class::method" string, or
 *                           <li> an array of (className,methodName).
 *                           <li> an array of (classInstance,methodName).
 *                           </ul>
 * @param mixed    $self     The value of `$this` inside `$fn` (an object).
 * @param mixed    ...$args  Extra arguments to be prepended to `$fn` on each call.
 * @return Closure
 */
function bind (callable $fn, $self = null)
{
  $args = array_slice (func_get_args (), 2);
  return Closure::bind (function () use ($fn, $self, $args) {
    return call_user_func_array ($fn, $args);
  }, $self);
}

/**
 * Transforms a callable reference into a closure, with optional pre-bound `$this` and/or appended arguments.
 * @param callable $fn       A function reference, in the form of:
 *                           <ul>
 *                           <li> a Closure instance,
 *                           <li> a function name string,
 *                           <li> a "class::method" string, or
 *                           <li> an array of (className,methodName).
 *                           <li> an array of (classInstance,methodName).
 *                           </ul>
 * @param mixed    $self     The value of `$this` inside `$fn` (an object).
 * @param mixed    ...$args  Extra arguments to be appended to `$fn` on each call.
 * @return Closure
 */
function bindRight (callable $fn, $self = null)
{
  $args = array_slice (func_get_args (), 2);
  return Closure::bind (function () use ($fn, $self, $args) {
    return call_user_func_array ($fn, $args);
  }, $self);
}

/**
 * Compiles and returns a lambda function defined by the given string expression.
 *
 * The expression is compiled only once, further calls to this function with the same argument will return a cached
 * instance.
 * @param string $exp An expression with the syntax: "$arg1,$arg2,... => php_expression".
 *                    <p>The string must be delimited with single quotes.
 *                    <p>Ex:
 *                    <code>  f ('$x => $x+1')</code>
 *                    <code>  f ('$a, callable $b => $a + $b()')</code>
 * @return Closure
 */
function f ($exp)
{
  static $cache = [];
  if (isset($cache[$exp]))
    return $cache[$exp];
  list ($a, $f) = explode ('=>', $exp, 2);

  return $cache[$exp] = create_function ($a, "return $f;");
}

/**
 * Compiles and returns a lambda function that receives a single argument `$x`, whose body is defined by the given
 * string expression.
 *
 * The expression is compiled only once, further calls to this function with the same argument will return a cached
 * instance.
 * @param string $exp A PHP expression where the token `$x` refers to the function's argument.
 *                    <p>The string must be delimited with single quotes.
 *                    <p>Ex:
 *                    <code>  fx ('$x->id')</code>
 *                    <code>  fx ('ucfirst($x[0]).substr($x,1)')</code>
 * @return Closure
 */
function fx ($exp)
{
  static $cache = [];
  if (isset($cache[$exp]))
    return $cache[$exp];
  return $cache[$exp] = create_function ('$x', "return $exp;");
}

/**
 * Returns a function that, when invoked, returns the given value.
 *
 * @param mixed $i The constant value to be returned by the new function.
 *
 * @return callable
 */
function constantFn ($i)
{
  return function () use ($i) {
    return $i;
  };
}

/**
 * Wraps the given function with a caching decorator.
 * The original function will be invoked only once, on the first call.
 * Subsequent calls return the cached value.
 *
 * @param callable $fn
 *
 * @return callable
 */
function cached ($fn)
{
  $v = null;
  return function () use ($fn, &$v) {
    return isset ($v) ? $v : $v = call_user_func ($fn);
  };
}

/**
 * Returns a function that returns the input argument unmodified.
 * @return Closure
 */
function identity ()
{
  return function ($a) { return $a; };
}

function fref
