<?php

/**
 * Checks if a string begins with a given substring.
 * @param string $str
 * @param string $substr
 * @return bool
 */
function str_begins_with ($str, $substr)
{
  return substr ($str, 0, strlen ($substr)) == $substr;
}

/**
 * Truncates a string to a certain length and appends ellipsis to it.
 *
 * @param string $text
 * @param int    $limit
 * @param string $ending
 *
 * @return string
 */
function str_truncate ($text, $limit, $ending = '...')
{
  if (strlen ($text) > $limit) {
    $text = strip_tags ($text);
    $text = substr ($text, 0, $limit);
    $text = substr ($text, 0, -(strlen (strrchr ($text, ' '))));
    $text = $text . $ending;
  }

  return $text;
}

/**
 * Limits a string to a certain length by imploding the middle part of it.
 *
 * @param string $text
 * @param int    $limit
 * @param string $more Symbol that represents the removed part of the original string.
 *
 * @return string
 */
function str_cut ($text, $limit, $more = '...')
{
  if (strlen ($text) > $limit) {
    $chars = floor (($limit - strlen ($more)) / 2);
    $p     = strpos ($text, ' ', $chars) + 1;
    $d     = $p < 1 ? 0 : $p - $chars;

    return substr ($text, 0, $chars + $d) . $more . substr ($text, -$chars + $d);
  }

  return $text;
}

/**
 * Pads an unicode string to a certain length with another string.
 * Note: this provides the mb_str_pad that is missing from the mbstring module.
 *
 * @param string $str
 * @param int    $pad_len
 * @param string $pad_str
 * @param int    $dir
 * @param string $encoding
 *
 * @return null|string
 */
function mb_str_pad ($str, $pad_len, $pad_str = ' ', $dir = STR_PAD_RIGHT, $encoding = 'UTF-8')
{
  mb_internal_encoding ($encoding);
  $str_len     = mb_strlen ($str);
  $pad_str_len = mb_strlen ($pad_str);
  if (!$str_len && ($dir == STR_PAD_RIGHT || $dir == STR_PAD_LEFT)) {
    $str_len = 1; // @debug
  }
  if (!$pad_len || !$pad_str_len || $pad_len <= $str_len) {
    return $str;
  }

  $result = null;
  if ($dir == STR_PAD_BOTH) {
    $length = ($pad_len - $str_len) / 2;
    $repeat = ceil ($length / $pad_str_len);
    $result = mb_substr (str_repeat ($pad_str, $repeat), 0, floor ($length))
              . $str
              . mb_substr (str_repeat ($pad_str, $repeat), 0, ceil ($length));
  }
  else {
    $repeat = ceil ($str_len - $pad_str_len + $pad_len);
    if ($dir == STR_PAD_RIGHT) {
      $result = $str . str_repeat ($pad_str, $repeat);
      $result = mb_substr ($result, 0, $pad_len);
    }
    else if ($dir == STR_PAD_LEFT) {
      $result = str_repeat ($pad_str, $repeat);
      $result =
        mb_substr ($result, 0, $pad_len - (($str_len - $pad_str_len) + $pad_str_len)) . $str;
    }
  }

  return $result;
}

/**
 * Encodes a string to be outputted to a javascript block as a delimited string.
 * Newlines and quotes that match the delimiters are escaped.
 *
 * @param string $str   The string to be encoded.
 * @param string $delim The delimiter used to enclose the javascript string (either " or ').
 *
 * @return string
 */
function str_encodeJavasciptStr ($str, $delim = '"')
{
  return $delim . str_replace ($delim, '\\' . $delim, str_replace ("\n", '\\n', $str)) . $delim;
}

/**
 * Converts an hyphenated compound word into a camel-cased form.
 *
 * Ex: `my-long-name => myLongName`
 * @param string $name
 * @param bool   $ucfirst When `true` the first letter is capitalized, otherwhise it is lower cased.
 * @return string
 */
function dehyphenate ($name, $ucfirst = false)
{
  $s = str_replace (' ', '', ucwords (str_replace ('-', ' ', $name)));
  return $ucfirst ? $s : lcfirst ($s);
}

/**
 * Converts a string to camel cased form.
 * @param string $name
 * @param bool   $ucfirst When `true` the first letter is capitalized, otherwhise it is lower cased.
 * @return string
 */
function str_camelize ($name, $ucfirst = false)
{
  $s = str_replace (' ', '', ucwords ($name));
  return $ucfirst ? $s : lcfirst ($s);
}

function trimText ($text, $maxSize)
{
  if (strlen ($text) <= $maxSize)
    return $text;
  $a = explode (' ', substr ($text, 0, $maxSize));
  array_pop ($a);

  return join (' ', $a) . ' (...)';
}

function trimHTMLText ($text, $maxSize)
{
  if (strlen ($text) <= $maxSize)
    return $text;
  $text = substr ($text, 0, $maxSize);
  $a    = strrpos ($text, '>');
  $b    = strrpos ($text, '<');
  if ($b !== false && ($a === false || $a < $b))
    $text = substr ($text, 0, $b);
  $a = explode (' ', $text);
  array_pop ($a);
  $text = join (' ', $a) . ' (...)';
  $tags = [];
  if (preg_match_all ('#<.*?>#', $text, $matches)) {
    foreach ($matches[0] as $match)
      if (substr ($match, 1, 1) == '/')
        array_pop ($tags);
      else if (substr ($match, -2, 1) != '/')
        array_push ($tags, trim (substr ($match, 1, strlen ($match) - 2)));
    $tags = array_reverse ($tags);
    foreach ($tags as $tag) {
      $a = strpos ($tag, ' ');
      if ($a)
        $tag = substr ($tag, 0, $a);
      $text .= "</$tag>";
    }
  }

  return $text;
}

function strJoin ($s1, $s2, $delimiter)
{
  return strlen ($s1) && strlen ($s2) ? $s1 . $delimiter . $s2 : (strlen ($s1) ? $s1 : $s2);
}

/**
 * Performs padding on strings having embedded tags.
 *
 * This is specially useful when used with color-tagged strings meant for terminal output.
 * > Ex: `"<color-name>text</color-name>"`
 * @param string $str
 * @param int    $width The desired minimum width, in characters.
 * @param int    $align One of the STR_PAD_XXX constants.
 * @return string
 */
function taggedStrPad ($str, $width, $align = STR_PAD_RIGHT)
{
  $w    = taggedStrLen ($str);
  $rawW = mb_strlen ($str);
  $d    = $rawW - $w;

  return mb_str_pad ($str, $width + $d, ' ', $align);
}

/**
 * Returns the true length of strings having embedded color tags.
 *
 * This is specially useful when used with color-tagged strings meant for terminal output.
 * > Ex: `"<color-name>text</color-name>"`
 * @param string $str
 * @return int The string's length, in characters.
 */
function taggedStrLen ($str)
{
  return mb_strlen (preg_replace ('/<[^>]*>/u', '', $str));
}

function mb_chr ($ord, $encoding = 'UTF-8')
{
  if ($encoding === 'UCS-4BE') {
    return pack ("N", $ord);
  }
  else {
    return mb_convert_encoding (mb_chr ($ord, 'UCS-4BE'), $encoding, 'UCS-4BE');
  }
}

function mb_ord ($char, $encoding = 'UTF-8')
{
  if ($encoding === 'UCS-4BE') {
    list(, $ord) = (strlen ($char) === 4) ? @unpack ('N', $char) : @unpack ('n', $char);

    return $ord;
  }
  else {
    return mb_ord (mb_convert_encoding ($char, 'UCS-4BE', $encoding), 'UCS-4BE');
  }
}
