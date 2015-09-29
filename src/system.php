<?php

/**
 * Retrieves an environmental variable.
 * @param string $var
 * @param string $default
 * @return string
 */
function env ($var, $default = '')
{
  $v = getenv ($var);
  return $v == '' || $v[0] == '%' ? $default : $v;
}

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

/**
 * Returns an ancestor directory of the given directory, `n` levels above.
 * @param string $path The starting path.
 * @param int $times How many times to travel up the directory hierarchy.
 * @return string The resulting path.
 */
function updir ($path, $times = 1) {
  while ($times--) $path = dirname ($path);
  return $path;
}
