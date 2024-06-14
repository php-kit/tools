<?php

/**
 * @param string $label Optional label.
 * @param string $hr    The character sequence to use as an Horizontal Ruler for visually highlighting the profiler's
 *                      messages.
 */
function startProfiling ($label = '', $hr = '')
{
  global $profStart, $profStep, $_PHR;
  $_PHR = $hr ? hr ($hr) : "\n";
  ob_start ();
  echo hsprintf ("<code><p><b>%s %s</b></p>", "$_PHR%s %s$_PHR",
    'Start profiling', $label);
  $profStart = $profStep = microtime (true);
}

function stepProfiling ($label = '')
{
  global $profStart, $profStep, $_PHR;
  if ($label) $label = hsprintf ("<p><b>%s</b></p>", "%s\n", ucfirst ($label));
  if (!$profStep) {
    echo hsprintf ("<p>%s</p>", "$_PHR%s$_PHR",
      'step/stopProfiling() called before startProfiling()');
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
    echo hsprintf ("<ul>%s<p>%s %s ms</p></ul>", "$_PHR%s%s %s ms$_PHR",
      $label, 'Elapsed time:', $diff);
  else echo hsprintf ("<ul>%s<p>%s %s ms, &nbsp; total:  %s ms</p></ul>", "$_PHR%s%s %s ms, total:  %s ms$_PHR",
    $label, 'Elapsed time:', $diff, $diff2);
}

/**
 * Call startProfiling() before the code being measured.
 * Call this function after the code block to display the ellapsed time.
 */
function stopProfiling ($label = '')
{
  global $_PHR;
  $total = round ((microtime (true) - $_SERVER['REQUEST_TIME_FLOAT']) * 1000, 2);
  $total = str_replace (' ', chr (0xC2) . chr (160), str_pad ($total, 7, ' ', STR_PAD_LEFT));
  $label = $label ? " $label" : '.';
  $v     = ob_get_clean ();
  if (ob_get_level ()) ob_end_clean ();
  echo $v;
  echo hsprintf ("<p><b>%s%s</b></p>", "$_PHR%s%s",
    'Stop', $label);
  stepProfiling ();
  echo hsprintf ("<p>%s %s ms</p></code>", "\n%s  %s ms$_PHR", 'Time elapsed since script start:', $total);
}
