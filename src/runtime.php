<?php

/**
 * Run the provided code intercepting PHP errors.
 *
 * If error-handling code is not supplied, an ErrorException will be thrown in the caller's context.
 *
 * @param callable $wrappedCode  Code to be executed wrapped by error catching code.
 * @param callable $errorHandler Optional error-handling code.
 * @param bool     $reset        True if the error status should be cleared so that Laravel does not intercept the
 *                               previous error.
 * @return mixed   The return value from the callable argument.
 *
 * @throws ErrorException
 * @throws Exception
 */
function catchErrorsOn ($wrappedCode, $errorHandler = null, $reset = true)
{
  $prevHandler = set_error_handler (function ($errno, $errstr, $errfile, $errline) {
    if (!error_reporting ())
      return false;
    throw new ErrorException ($errstr, $errno, 0, $errfile, $errline);
  });

  try {
    // Run the caller-supplied code.
    $r = $wrappedCode();
    // Restore the previous error handler.
    set_error_handler ($prevHandler);

    return $r;
  } catch (Exception $e) {
    // Intercept the error that will be triggered below.
    set_error_handler (function () {
      // Force error_get_last() to be set.
      return false;
    });
    // Clear the current error message so that the framework will not intercept the previous error.
    if ($reset)
      trigger_error ("");
    // Restore the previous error handler.
    set_error_handler ($prevHandler);

    // Handle the error.
    if (isset($errorHandler))
      return $errorHandler($e);

    throw $e;
  }
}

function startProfiling ()
{
  global $profStart;
  $profStart = microtime (true);
}

function endProfiling ()
{
  global $profStart;
  $profEnd = microtime (true);
  $diff    = round (($profEnd - $profStart) * 1000, 2) - 0.01;
  echo "Elapsed: $diff miliseconds.";
  exit;
}

/**
 * Call startProfiling() (native function) before the code being measured.
 * Call this function after the code block to display the ellapsed time.
 */
function stopProfiling ()
{
  ob_clean ();
  endProfiling ();
  exit;
}

