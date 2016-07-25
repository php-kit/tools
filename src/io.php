<?php

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
 * @param string $dir path.
 */
function rrmdir ($dir)
{
  if (is_dir ($dir)) {
    $objects = scandir ($dir);
    foreach ($objects as $object) {
      if ($object != "." && $object != "..") {
        if (filetype ($dir . "/" . $object) == "dir")
          rrmdir ($dir . "/" . $object);
        else unlink ($dir . "/" . $object);
      }
    }
    reset ($objects);
    rmdir ($dir);
  }
}

define ('DIR_LIST_ALL', 0);
define ('DIR_LIST_FILES', 1);
define ('DIR_LIST_DIRECTORIES', 2);

/**
 * List files and/or directories inside the specified path.
 *
 * Note: the `.` and `..` directories are not returned.
 * @param string   $path
 * @param int      $type      One of the DIR_LIST_XXX constants.
 * @param bool     $fullPaths When `true` returns the full path name of each file, otherwise returns the file name only.
 * @param int|bool $sortOrder Either `false` (no sort), SORT_ASC or SORT_DESC.
 * @return false|string[] `false` if not a valid directory.
 */
function dirList ($path, $type = 0, $fullPaths = false, $sortOrder = false)
{
  if (!file_exists($path))
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
function getRelativePath($basePath, $targetPath)
{
  if ($basePath === $targetPath) {
    return '';
  }

  $sourceDirs = explode('/', isset($basePath[0]) && '/' === $basePath[0] ? substr($basePath, 1) : $basePath);
  $targetDirs = explode('/', isset($targetPath[0]) && '/' === $targetPath[0] ? substr($targetPath, 1) : $targetPath);
  array_pop($sourceDirs);
  $targetFile = array_pop($targetDirs);

  foreach ($sourceDirs as $i => $dir) {
    if (isset($targetDirs[$i]) && $dir === $targetDirs[$i]) {
      unset($sourceDirs[$i], $targetDirs[$i]);
    } else {
      break;
    }
  }

  $targetDirs[] = $targetFile;
  $path = str_repeat('../', count($sourceDirs)).implode('/', $targetDirs);

  // A reference to the same base directory or an empty subdirectory must be prefixed with "./".
  // This also applies to a segment with a colon character (e.g., "file:colon") that cannot be used
  // as the first segment of a relative-path reference, as it would be mistaken for a scheme name
  // (see http://tools.ietf.org/html/rfc3986#section-4.2).
  return '' === $path || '/' === $path[0]
         || false !== ($colonPos = strpos($path, ':')) && ($colonPos < ($slashPos = strpos($path, '/')) || false === $slashPos)
    ? "./$path" : $path;
}

function dirnameEx ($path)
{
  $path = dirname ($path);
  return $path == '/' ? '' : $path;
}

function fileExists ($filename, $useIncludePath = true)
{
  return $useIncludePath ? boolval (stream_resolve_include_path ($filename)) : file_exists ($filename);
}

/**
 * Loads and executes the specified PHP file, searching for it on the 'include path'.
 * @param string $filename
 * @return bool|mixed
 */
function includeFile ($filename)
{
  $path = stream_resolve_include_path ($filename);
  return $path ? require $path : false;
}

/**
 * Loads the specified file, optionally searching the include path, and stripping the Unicode Byte Order Mark (BOM), if
 * one is present.
 * @param string    $filename
 * @param bool|true $useIncludePath
 * @return false|string `false` if the file is not found.
 */
function loadFile ($filename, $useIncludePath = true)
{
  if ($useIncludePath) {
    if (!($filename = stream_resolve_include_path ($filename))) return false;;
  }
  else if (!file_exists ($filename)) return false;
  return removeBOM (file_get_contents ($filename));
}

function removeBOM ($string)
{
  if (substr ($string, 0, 3) == pack ('CCC', 0xef, 0xbb, 0xbf))
    $string = substr ($string, 3);
  return $string;
}
