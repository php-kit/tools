<?php

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
