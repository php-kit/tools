<?php
namespace T;

/**
 * Checks if a given PHP expression is syntactically valid.
 * @param string $exp
 * @return boolean
 */
function validatePHPExpressionSyntax ($exp)
{
  return eval ("return true;return $exp;");
}

function check_syntax ($code, &$output = 0)
{
  $b = 0;
  foreach (token_get_all ($code) as $token)
    if ('{' == $token)
      ++$b;
    elseif ('}' == $token)
      --$b;
  if ($b)
    return false; // Unbalanced braces would break the eval below
  ob_start (); // Catch potential parse error messages
  $code = eval('if(0){' . $code . '}'); // Put $code in a dead code sandbox to prevent its execution
  if ($output != 0)
    $output = ob_get_clean ();
  else
    ob_end_clean ();
  return false !== $code;
}

function evalPHP ($code)
{
  $code = trim ($code, " \n\r\t");
  if (substr ($code, -2) != '?>')
    $code .= '?>';
  if (substr ($code, 0, 5) == '<?php')
    return eval(substr ($code, 5, strlen ($code) - 7));
  return eval($code);
}
