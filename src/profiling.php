<?php

function startProfiling ($label = '')
{
  global $profStart, $profStep;
  if ($label) $label = "at $label";
  ob_start ();
  echo "
<code><p><b>Start profiling $label</b></p>
";
  $profStart = $profStep = microtime (true);
}

function stepProfiling ($label = '')
{
  global $profStart, $profStep;
  if ($label) echo "<p><b>$label</b></p>";
  if (!$profStep) {
    echo "
<p>step/stopProfiling() called before startProfiling()</p>
";
    ob_start ();
    return;
  }
  $profEnd  = microtime (true);
  $d1       = round (($profEnd - $profStep) * 1000, 2) - 0.01;
  $d2       = round (($profEnd - $profStart) * 1000, 2) - 0.01;
  $diff     = str_replace (' ', chr (0xC2) . chr (160), str_pad ($d1, 7, ' ', STR_PAD_LEFT));
  $diff2    = str_replace (' ', chr (0xC2) . chr (160), str_pad ($d2, 7, ' ', STR_PAD_LEFT));
  $profStep = $profEnd;
  if ($d1 == $d2)
    echo "
<ul><p>Elapsed time: $diff ms</p></ul>
";
  else echo "
<ul><p>Elapsed time: $diff ms, &nbsp; total: $diff2 ms</p></ul>
";
}

/**
 * Call startProfiling() before the code being measured.
 * Call this function after the code block to display the ellapsed time.
 */
function stopProfiling ($label = '')
{
  if ($label) $label = "at $label";
  $v = ob_get_clean ();
  if (ob_get_level ()) ob_end_clean ();
  echo $v;
  echo "
<p><b>Stop $label</b></p>
";
  stepProfiling ();
  echo "</code>";
  exit;
}
