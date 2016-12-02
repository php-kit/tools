<?php

/**
 * For use with {@see color()}.
 */
const TERMINAL_COLORS = [
  'standout'    => ['smso', 'rmso'],
  'bold'        => ['bold', 'sgr0'],
  'underline'   => ['smul', 'rmul'],
  'black'       => ['setaf 0', 'sgr0'],
  'dark red'    => ['setaf 1', 'sgr0'],
  'dark green'  => ['setaf 2', 'sgr0'],
  'dark yellow' => ['setaf 3', 'sgr0'],
  'dark blue'   => ['setaf 4', 'sgr0'],
  'dark purple' => ['setaf 5', 'sgr0'],
  'dark cyan'   => ['setaf 6', 'sgr0'],
  'grey'        => ['setaf 7', 'sgr0'],
  'dark grey'   => ['setaf 8', 'sgr0'],
  'red'         => ['setaf 9', 'sgr0'],
  'green'       => ['setaf 10', 'sgr0'],
  'yellow'      => ['setaf 11', 'sgr0'],
  'blue'        => ['setaf 12', 'sgr0'],
  'purple'      => ['setaf 13', 'sgr0'],
  'cyan'        => ['setaf 14', 'sgr0'],
  'white'       => ['setaf 15', 'sgr0'],
];

/**
 * Hybrid sprintf.
 * Formats a message with or without HTML formatting, depending on whether the script is running on the CLI or not.
 *
 * @param string $webFormat HTML-formatted text with sprintf-compatible placeholders.
 * @param string $cliFormat unformatted text with sprintf-compatible placeholders.
 * @param mixed  ...$args   Values for each of the placeholders.
 * @return string
 */
function hsprintf ($webFormat, $cliFormat)
{
  $args = array_slice (func_get_args (), 2);
  return call_user_func_array ('sprintf', array_merge ([isCLI () ? $cliFormat : $webFormat], $args));
}

/**
 * Returns an Horiontal Rule comprised of the specified character pattern, with the same size as the terminal width.
 *
 * @param string $char
 * @return string
 */
function hr ($char)
{
  return str_repeat ($char, intval (`tput cols`));
}

function json_save ($path, $data, $pretty = true)
{
  $json = json_encode ($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | ($pretty ? JSON_PRETTY_PRINT : 0));
  $json = preg_replace_callback ('/^ +/m', function ($m) {
    return str_repeat (' ', strlen ($m[0]) / 2);
  }, $json);
  file_put_contents ($path, $json);
}

function json_load ($path, $assoc = false)
{
  return json_decode (file_get_contents ($path), $assoc);
}

/**
 * Removes a directory recursively.
 *
 * <p>Even if it fails because some file or subdirectory couldn't be removed, it still tries to delete as much as
 * possible.
 *
 * @param string $dir path.
 * @return bool FALSE if any file or subdirectory failed being removed.
 */
function rrmdir ($dir)
{
  if (strtoupper (substr (PHP_OS, 0, 3)) === 'WIN') {
    if (is_symlink ($dir)) {
      $target = symlink_target ($dir);
      if (is_dir ($target))
        return rmdir ($dir);
      else
        return unlink ($dir);
    }
    else if (is_dir ($dir)) {
      $o = true;
      foreach (scandir ($dir) as $subdir) {
        if ($subdir == "." || $subdir == "..")
          continue;
        $o = $o && rrmdir ($dir . "/" . $subdir);
      }
      if ($o)
        $o = $o && rmdir ($dir);
      return $o;
    }
    else return unlink ($dir);
  }
  else {
    if (is_dir ($dir)) {
      $objects = scandir ($dir);
      $o       = true;
      foreach ($objects as $object) {
        if ($object != "." && $object != "..") {
          if (filetype ($dir . "/" . $object) == "dir")
            $o = $o && rrmdir ($dir . "/" . $object);
          else $o = $o && unlink ($dir . "/" . $object);
        }
      }
      reset ($objects);
      if ($o)
        $o = $o && rmdir ($dir);
      return $o;
    }
  }
}

/**
 * returns if target path is of a symlink
 *
 * @param $target
 * @return bool
 */
function is_symlink ($target)
{
  $target   = strtr ($target, '\\', '/');
  $realpath = strtr (realpath ($target), '\\', '/');

  if ($realpath !== $target) // it is a symlink
    return true;

  // fallback for broken symlinks
  $link = strtr (readlink ($target), '\\', '/');

  if ($link && $link !== $target)// it is a broken symlink
    return true;

  return false;
}


/**
 * returns target of a symlink
 *
 * @param $path
 * @return string
 */
function symlink_target ($path)
{
  $path     = strtr ($path, '\\', '/');
  $realpath = strtr (realpath ($path), '\\', '/');

  if ($realpath !== $path) // it is a symlink
    return $realpath;

  // fallback for broken symlinks
  $link = strtr (readlink ($path), '\\', '/');

  if ($link && $link !== $path)// it is a broken symlink
    return $link;

  return $path;
}


define ('DIR_LIST_ALL', 0);
define ('DIR_LIST_FILES', 1);
define ('DIR_LIST_DIRECTORIES', 2);

/**
 * List files and/or directories inside the specified path.
 *
 * Note: the `.` and `..` directories are not returned.
 *
 * @param string   $path
 * @param int      $type      One of the DIR_LIST_XXX constants.
 * @param bool     $fullPaths When `true` returns the full path name of each file, otherwise returns the file name only.
 * @param int|bool $sortOrder Either `false` (no sort), SORT_ASC or SORT_DESC.
 * @return false|string[] `false` if not a valid directory.
 */
function dirList ($path, $type = 0, $fullPaths = false, $sortOrder = false)
{
  if (!file_exists ($path))
    return false;
  $d = new DirectoryIterator($path);
  $o = [];
  foreach ($d as $file) {
    /** @var DirectoryIterator $file */
    if ($file->isDot ()) continue;
    if ($type == 1 && !$file->isFile ())
      continue;
    if ($type == 2 && !$file->isDir ())
      continue;
    $o[] = $fullPaths ? $file->getPathname () : $file->getFilename ();
  }
  if ($sortOrder)
    sort ($o, $sortOrder);
  return $o;
}

/**
 * Returns the target path as relative reference from the base path.
 *
 * Only the URIs path component (no schema, host etc.) is relevant and must be given, starting with a slash.
 * Both paths must be absolute and not contain relative parts.
 * Relative URLs from one resource to another are useful when generating self-contained downloadable document archives.
 * Furthermore, they can be used to reduce the link size in documents.
 *
 * Example target paths, given a base path of "/a/b/c/d":
 * - "/a/b/c/d"     -> ""
 * - "/a/b/c/"      -> "./"
 * - "/a/b/"        -> "../"
 * - "/a/b/c/other" -> "other"
 * - "/a/x/y"       -> "../../x/y"
 *
 * @param string $basePath   The base path
 * @param string $targetPath The target path
 *
 * @return string The relative target path
 *
 * @copyright Fabien Potencier <fabien@symfony.com>
 */
function getRelativePath ($basePath, $targetPath)
{
  if ($basePath === $targetPath) {
    return '';
  }

  $sourceDirs = explode ('/', isset($basePath[0]) && '/' === $basePath[0] ? substr ($basePath, 1) : $basePath);
  $targetDirs = explode ('/', isset($targetPath[0]) && '/' === $targetPath[0] ? substr ($targetPath, 1) : $targetPath);
  array_pop ($sourceDirs);
  $targetFile = array_pop ($targetDirs);

  foreach ($sourceDirs as $i => $dir) {
    if (isset($targetDirs[$i]) && $dir === $targetDirs[$i]) {
      unset($sourceDirs[$i], $targetDirs[$i]);
    }
    else {
      break;
    }
  }

  $targetDirs[] = $targetFile;
  $path         = str_repeat ('../', count ($sourceDirs)) . implode ('/', $targetDirs);

  // A reference to the same base directory or an empty subdirectory must be prefixed with "./".
  // This also applies to a segment with a colon character (e.g., "file:colon") that cannot be used
  // as the first segment of a relative-path reference, as it would be mistaken for a scheme name
  // (see http://tools.ietf.org/html/rfc3986#section-4.2).
  return '' === $path || '/' === $path[0]
         || false !== ($colonPos = strpos ($path, ':')) &&
            ($colonPos < ($slashPos = strpos ($path, '/')) || false === $slashPos)
    ? "./$path" : $path;
}

/**
 * Strips the base path from the given absolute path if it is a subdirectory of that base path.
 * Otherwise, it returns the given path unmodified.
 *
 * ><p>**Note:** this supports mixed Windows/Unix file paths.
 *
 * @param string $base An absolute path.
 * @param string $path An absolute path.
 * @return string
 */
function getRelativePathIfSubpath ($base, $path)
{
  if ($path) {
    $path = normalizePath ($path);
    // Check if it's an absolute path
    if ($path[0] == DIRECTORY_SEPARATOR || $path[1] == ':' /* ex: C:\windows-path */) {
      $l = strlen ($base);
      if (substr ($path, 0, $l) == $base)
        return substr ($path, $l + 1);
    }
  }
  return $path;
}

/**
 * Creates a temporary directory.
 *
 * @param        $dir
 * @param string $prefix
 * @param int    $mode
 *
 * @return string
 */
function tempdir ($dir, $prefix = '', $mode = 0700)
{
  if (substr ($dir, -1) != '/') $dir .= '/';
  do {
    $path = $dir . $prefix . mt_rand (0, 9999999);
  } while (!mkdir ($path, $mode));

  return normalizePath ($path);
}

/**
 * Normalizes a filesystem path, converting Windows directory separators to UNIX-compatible forward slashes.
 *
 * @param string $path
 * @return string
 */
function normalizePath ($path)
{
  return str_replace ('\\', '/', $path);
}

/**
 * Returns an ancestor directory of the given directory, `n` levels above.
 *
 * @param string $path   The starting path.
 * @param int    $levels How many times to travel up the directory hierarchy.
 * @return string The resulting path.
 */
function updir ($path, $levels = 1)
{
  if (PHP_MAJOR_VERSION >= 7)
    return dirname ($path, $levels);
  while ($levels--) $path = dirname ($path);
  return $path;
}

/**
 * Similar to {@see updir}, but it returns an empty string if `$path` is already at the root level.
 *
 * @param string $path   The starting path.
 * @param int    $levels How many times to travel up the directory hierarchy.
 * @return string The resulting path.
 */
function dirnameEx ($path, $levels = 1)
{
  $path = updir ($path, $levels);
  return $path == DIRECTORY_SEPARATOR ? '' : $path;
}

/**
 * Enhanced version of {@see file_exists()} that is able to search for a file on PHP's include path.
 *
 * @param string $filename
 * @param bool   $useIncludePath
 * @return bool
 */
function fileExists ($filename, $useIncludePath = true)
{
  return $useIncludePath ? boolval (stream_resolve_include_path ($filename)) : file_exists ($filename);
}

/**
 * Loads and executes the specified PHP file, searching for it on the 'include path'.
 *
 * @param string $filename
 * @return bool|mixed
 */
function includeFile ($filename)
{
  $path = stream_resolve_include_path ($filename);
  return $path ? require $path : false;
}

/**
 * Loads the specified **text** file.
 *
 * <p>It also performs the following tasks:
 * - optionally searches the include path;
 * - skips the Unicode Byte Order Mark (BOM), if one is present;
 * - convertes line endings to the format expected by the operating system.
 *
 * ><p>**WARNING:** do NOT use this function to load binary files.
 *
 * @param string    $filename
 * @param bool|true $useIncludePath
 * @return false|string `false` if the file is not found.
 */
function loadFile ($filename, $useIncludePath = false)
{
  if ($useIncludePath) {
    if (!($filename = stream_resolve_include_path ($filename))) return false;;
  }
  else if (!file_exists ($filename)) return false;

  //ini_set("auto_detect_line_endings", true);  //TODO: is this required?
  $f = fopen ($filename, 'rt', false);
  try {
    // Read the first 3 bytes to determine if there is a BOM
    $m = fread ($f, 3);
    if ($m != pack ('CCC', 0xef, 0xbb, 0xbf))
      // No BOM, so read from the beginning
      fseek ($f, 0);
    // Otherwise, read from this point on, skipping the BOM (this is faster than doing a substr() and copying strings around)
    return stream_get_contents ($f);
  }
  finally {
    fclose ($f);
  }
}

/**
 * Removes the Unicode Byte Order Mark for the beginning of a string, if it is present there.
 *
 * @param $string
 * @return string
 */
function removeBOM ($string)
{
  if (substr ($string, 0, 3) == pack ('CCC', 0xef, 0xbb, 0xbf))
    $string = substr ($string, 3);
  return $string;
}

/**
 * Checks if STDOUT is being redirected.
 *
 * ><p>**Note:** if it is, text formatting is not possible.
 *
 * @return bool
 */
function stdoutIsRedirected ()
{
  return !isCLI () || !stream_get_meta_data (STDOUT)['seekable'];
}

/**
 * Runs the `tput` command (if available) for controlling the terminal.
 *
 * @param string $s tput command argument.
 * @return string The output from the tput program or an empty string if tput is not available.
 */
function tput ($s)
{
  static $available = null;
  if (is_null ($available))
    $available = command_exists ('tput') && `tput colors` > 8 && !stdoutIsRedirected ();
  return $available ? `tput $s` : '';
}

/**
 * Returns a text message formatted with a color/style for terminal output.
 *
 * <p>The formatted text restores the previous color at the end of the message.
 *
 * <p>If the terminal does not support color or if not running on the CLI, the input text is returned unaltered.
 *
 * @param string $color One of the {@see TERMINAL_COLORS} array keys.
 * @param string $msg   The message to format.
 * @return string The formatted text.
 */
function color ($color, $msg)
{
  static $cache = [];
  if (isset ($cache[$color]))
    list ($begin, $end) = $cache[$color];
  else {
    $c = get (TERMINAL_COLORS, $color);
    if (!$c)
      throw new InvalidArgumentException("Invalid color name: $color");
    $begin         = tput ($c[0]);
    $end           = tput ($c[1]);
    $cache[$color] = [$begin, $end];
  }
  return "$begin{$msg}$end";
}
