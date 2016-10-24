<?php

/**
 * Retrieves an environmental variable.
 *
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
 * Determine if the script is not running in the context of a web request; for ex. via the command line (CLI).
 *
 * @return bool
 */
function isCLI ()
{
  return defined ('STDIN') && !isset($_SERVER['REQUEST_METHOD']);
}

/**
 * Runs the specified external command with the specified input data or stream and returns the resulting output or
 * sends it to a stream.
 *
 * @param string          $cmd       Command line to be executed by the shell.
 * @param string|resource $input     Data or a stream of data to send to the subprocess' STDIN.
 * @param string          $extraPath Additional search path folders to append to the shell's PATH.
 * @param array           $extraEnv  Additional environment variables to append to the shell's environment.
 * @param resource        $output    A stream for collecting STDOUT data from the subprocess.
 *
 * @throws RuntimeException STDERR (or STDOUT if STDERR is empty) is available via getMessage().
 * Status code -1 = command not found; other status codes = status returned by command execution.
 * @return string|null Data from the command's STDOUT if $output is not specified, null otherwise.
 */
function runExternalCommand ($cmd, $input = '', $extraPath = '', array $extraEnv = null, $output = null)
{
  $descriptorSpec = [
    0 => is_resource ($input)
      ? $input
      : ["pipe", "r"], // stdin is a pipe that the child will read from
    1 => is_resource ($output)
      ? $output
      : ["pipe", "w"], // stdout is a pipe that the child will write to
    2 => ["pipe", "w"] // stderr is a pipe that the child will write to
  ];

  if ($extraPath) {
    $path = $extraPath . PATH_SEPARATOR . array_at ($_SERVER, 'PATH', getenv ('PATH'));
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
    $outData = null;

    if (!is_resource ($input)) {
      fwrite ($pipes[0], $input);
      fclose ($pipes[0]);
    }

    if (!is_resource ($output)) {
      $outData = stream_get_contents ($pipes[1]);
      fclose ($pipes[1]);
    }

    $error = stream_get_contents ($pipes[2]);
    fclose ($pipes[2]);

    $return_value = proc_close ($process);
    if ($return_value)
      throw new RuntimeException ($error ?: $outData, $return_value);

    return $outData;
  }
  throw new RuntimeException ($cmd, -1);
}

/**
 * Runs a process in the background, detached from the owner process.
 *
 * > This only runs on UNIX-compatible systems.
 *
 * @param string $command    An external command, with optional arguments.
 * @param string $outputFile The file where the command's output should be saved to.
 * @return string The PID (process ID).
 */
function runBackgroundCommand ($command, $outputFile = '/dev/null')
{
  $PID = shell_exec ("nohup $command > $outputFile 2>&1 & echo $!");
  return ($PID);
}

/**
 * Checks if a specific process is running.
 *
 * > This only runs on UNIX-compatible systems.
 *
 * @param int $pid The process ID.
 * @return bool true if it's running.
 */
function isRunning ($pid)
{
  try {
    $result = shell_exec (sprintf ("ps %d", $pid));
    if (count (preg_split ("/\n/", $result)) > 2) {
      return true;
    }
  }
  catch (Exception $e) {
  }
  return false;
}

/**
 * Stops the process.
 *
 * > This only runs on UNIX-compatible systems.
 *
 * @param int $pid The process ID.
 * @return bool `true` if the processes was stopped, `false` otherwise.
 */
function stopProcess ($pid)
{
  try {
    $result = shell_exec (sprintf ('kill %d 2>&1', $pid));
    if (!preg_match ('/No such process/', $result)) {
      return true;
    }
  }
  catch (Exception $e) {
  }
  return false;
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
 * Returns an ancestor directory of the given directory, `n` levels above.
 *
 * @param string $path  The starting path.
 * @param int    $times How many times to travel up the directory hierarchy.
 * @return string The resulting path.
 */
function updir ($path, $times = 1)
{
  $path = normalizePath ($path);
  while ($times--) $path = dirname ($path);
  return $path;
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
