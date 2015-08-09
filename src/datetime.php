<?php

/**
 * Human-friendly textual descriptions for some dates.
 * For use by humanizeDate().
 */
$HUMANIZE_DATE_STR = [
  'today'     => 'Hoje, às',
  'yesterday' => 'Ontem, às',
];
/**
 * For the specified date, if its today or yesterday, it replaces it with a textual description.
 *
 * @param string $date
 *
 * @return string
 */
function humanizeDate ($date)
{
  global $HUMANIZE_DATE_STR;
  $today     = Date ('Y-m-d');
  $yesterday = Date ('Y-m-d', strtotime ("-1 days"));
  return str_replace ($yesterday, $HUMANIZE_DATE_STR['yesterday'],
    str_replace ($today, $HUMANIZE_DATE_STR['today'], $date));
}
