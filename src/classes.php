<?php

/**
 * Represents text that should not be transformed when output.
 *
 * <p>Common use cases for this class are text that should not be HTML-escaped by templating engines or values that
 * should not be JSON-encoded when serializing data structures.
 */
class RawText
{
  private $s;

  function __construct ($s)
  {
    $this->s = (string)$s;
  }

  /**
   * @return string
   */
  function __toString ()
  {
    return $this->s;
  }
}

/**
 * A shortcut function to create a {@see RawText} instance from the given value.
 *
 * @param mixed $s
 * @return RawText
 */
function raw ($s)
{
  return new RawText($s);
}
