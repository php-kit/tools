<?php

/**
 * Converts a callable reference to a {@see Closure} instance.
 *
 * <p>If the argument is already a Closure, it is returned unmodified.
 *
 * @param callable $f A callable reference, in the form of:
 *                    <ul>
 *                    <li> a Closure instance,
 *                    <li> a function name string,
 *                    <li> a "class::method" string, or
 *                    <li> an array of (className,methodName).
 *                    <li> an array of (classInstance,methodName).
 *                    </ul>
 * @return callable
 */
function _fn (callable $f)
{
  if ($f instanceof Closure)
    return $f;
  return PHP_MAJOR_VERSION > 6 && PHP_MINOR_VERSION > 0
    ? Closure::fromCallable ($f)
    : function (...$a) use ($f) {
      return $f (...$a);
    };
}

/**
 * Allows an IDE to recognize a callable reference, so that it can validate it and allow refactoring it.
 *
 * It is a no-op: it returns the input argument unmodified.
 *
 * @param callable $f   A function reference, in the form of:
 *                      <ul>
 *                      <li> a Closure instance,
 *                      <li> a function name string,
 *                      <li> a "class::method" string, or
 *                      <li> an array of (className,methodName).
 *                      <li> an array of (classInstance,methodName).
 *                      </ul>
 * @return callable
 */
function fnRef (callable $f)
{
  return $f;
}

/**
 * Transforms a callable reference into a closure, with optional pre-bound arguments.
 *
 * <p>If you want to preserve `$this` on the called function, you can use the [$this, 'method'] syntax.
 *
 * @param callable $fn       A function reference, in the form of:
 *                           <ul>
 *                           <li> a Closure instance,
 *                           <li> a function name string,
 *                           <li> a "class::method" string, or
 *                           <li> an array of (className,methodName).
 *                           <li> an array of (classInstance,methodName).
 *                           </ul>
 * @param mixed    ...$args  The call's fixed arguments.
 * @return Closure
 */
function bind (callable $fn)
{
  $args = array_slice (func_get_args (), 1);
  return function () use ($fn, $args) {
    return call_user_func_array ($fn, $args);
  };
}

/**
 * Transforms a callable reference into a closure, with optional pre-bound arguments that will be prepended on each
 * call.
 *
 * <p>If you want to preserve `$this` on the called function, you can use the [$this, 'method'] syntax.
 *
 * @param callable $fn       A function reference, in the form of:
 *                           <ul>
 *                           <li> a Closure instance,
 *                           <li> a function name string,
 *                           <li> a "class::method" string, or
 *                           <li> an array of (className,methodName).
 *                           <li> an array of (classInstance,methodName).
 *                           </ul>
 * @param mixed    ...$args  Extra arguments to be <b>prepended</b> to the call's arguments.
 * @return Closure
 */
function bindLeft (callable $fn)
{
  $args = func_get_args ();
  return function () use ($args) {
    $fn = array_shift ($args);
    return call_user_func_array ($fn, array_merge ($args, func_get_args ()));
  };
}

/**
 * Transforms a callable reference into a closure, with optional pre-bound arguments that will be appended on each call.
 *
 * <p>If you want to preserve `$this` on the called function, you can use the [$this, 'method'] syntax for `$fn`.
 *
 * @param callable $fn       A function reference, in the form of:
 *                           <ul>
 *                           <li> a Closure instance,
 *                           <li> a function name string,
 *                           <li> a "class::method" string, or
 *                           <li> an array of (className,methodName).
 *                           <li> an array of (classInstance,methodName).
 *                           </ul>
 * @param mixed    ...$args  Extra arguments to be <b>appended</b> to the call's arguments.
 * @return Closure
 */
function bindRight (callable $fn)
{
  $args = func_get_args ();
  return function () use ($args) {
    $fn = array_shift ($args);
    return call_user_func_array ($fn, array_merge (func_get_args (), $args));
  };
}

/**
 * Returns a lambda function defined by the given string expression.
 *
 * <p>The expression is compiled only once; further calls to this function with the same argument will return a cached
 * instance.
 *
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
  $code = sprintf('return function(%s) { return %s; };', $a, $f);
  return $cache[$exp] = eval($code);
}

/**
 * Returns a lambda function that receives a single argument `$x`, whose body is defined by the given string expression.
 *
 * <p>The expression is compiled only once; further calls to this function with the same argument will return a cached
 * instance.
 *
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
  $code = sprintf('return function($x) { return %s; };', $exp);
  return $cache[$exp] = eval($code);
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
 *
 * @return Closure
 */
function identity ()
{
  return function ($a) { return $a; };
}

/**
 * Returns a function that performs no operation.
 *
 * @return Closure
 */
function nop ()
{
  return function () { };
}

/**
 * Invokes a method of an object/class, optionally on the context of another class.
 *
 * @param callable $ref     A callable reference; either [className,methodName] or [classInstance,methodName].
 * @param null     $self    [optional] The object on which context the call will be performed (this will set $this in
 *                          the method).
 * @param mixed    ...$args Extra arguments to be prepended to `$fn` on each call.
 * @return mixed The method's return value.
 */
function call_method ($ref, $self = null)
{
  if (!is_array ($ref) || count ($ref) != 2)
    throw new InvalidArgumentException("Argument must be an array with 2 elements");
  $args = array_slice (func_get_args (), 2);
  $m    = new \ReflectionMethod ($ref[0], $ref[1]);
  if ($m->isStatic ())
    return call_user_func_array ($ref, $args);
  $f = $m->getClosure ($ref[0]);
  if ($self)
    $f = $f->bindTo ($self, $self);
  return call_user_func_array ($f, $args);
}

/**
 * Returns a function that extracts either an associative array of specific fields from its argument, or a linear array
 * of values from one field of its argument
 *
 * <p>The function argument can be `object|array|null`.
 *
 * ><p>This is meant to be used as a callback for iterable sequences.
 *
 * ><p>**Ex:**
 * ```php
 *   $x = map ($iterable, pluck ('field'))
 * ```
 *
 * @param array|string $flds If a string, it is the name of the field to extract, otherwise it must be a linear array
 *                           of field names.
 * @return Closure
 */
function pluck ($flds)
{
  return is_array ($flds)
    ? function ($e) use ($flds) { return getFields ($e, $flds); }
    : function ($e) use ($flds) { return getField ($e, $flds); };
}

/**
 * Returns reflection information about the given callable.
 *
 * @param callable $fn
 * @return ReflectionFunction|ReflectionMethod
 * @throws ReflectionException
 */
function reflectionOfCallable (callable $fn)
{
  if ($fn instanceof \Closure || is_string ($fn))
    return new \ReflectionFunction ($fn);
  return new \ReflectionMethod (...$fn);
}

/**
 * Determines if a PHP built-in function is available.
 *
 * @param string $fn
 * @return bool
 */
function isFunctionEnabled ($fn)
{
  return is_callable ($fn) && false === stripos (ini_get ('disable_functions'), $fn);
}

