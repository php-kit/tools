<?php

/**
 * Runs the specified external command with the specified input data and returns the resulting output.
 *
 * @param string $cmd       Command line to be executed by the shell.
 * @param string $input     Data for STDIN.
 * @param string $extraPath Additional search path folders to append to the shell's PATH.
 * @param array  $extraEnv  Additional environment variables to append to the shell's environment.
 *
 * @throws RuntimeException STDERR (or STDOUT if STDERR is empty) is available via getMessage().
 * Status code -1 = command not found; other status codes = status returned by command execution.
 * @return string Data from the command's STDOUT.
 */
function runExternalCommand ($cmd, $input = '', $extraPath = '', array $extraEnv = null)
{
  $descriptorSpec = [
    0 => ["pipe", "r"], // stdin is a pipe that the child will read from
    1 => ["pipe", "w"], // stdout is a pipe that the child will write to
    2 => ["pipe", "w"] // stderr is a pipe that the child will write to
  ];

  if ($extraPath) {
    $path = $extraPath . PATH_SEPARATOR . $_SERVER['PATH'];
    if (!isset($extraEnv))
      $extraEnv = [];
    $extraEnv['PATH'] = $path;
  }

  if (isset($extraEnv)) {
    $env = $_SERVER;
    unset($env['argv']);
    $env = array_merge ($env, $extraEnv);
  }
  else $env = null;

  $process = proc_open ($cmd, $descriptorSpec, $pipes, null, $env);

  if (is_resource ($process)) {
    fwrite ($pipes[0], $input);
    fclose ($pipes[0]);

    $output = stream_get_contents ($pipes[1]);
    fclose ($pipes[1]);

    $error = stream_get_contents ($pipes[2]);
    fclose ($pipes[2]);

    $return_value = proc_close ($process);
    if ($return_value)
      throw new RuntimeException ($error ?: $output, $return_value);

    return $output;
  }
  throw new RuntimeException ($cmd, -1);
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
  try {
    $d = new DirectoryIterator($path);
  } catch (Exception $e) {
    return false;
  }
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

  return $path;
}


