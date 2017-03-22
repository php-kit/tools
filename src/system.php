<?php

if (!function_exists ('env')) {
  /**
   * Retrieves an environmental variable, if it is defined, automatically typecasting its value if possible.
   *
   * <p>It returns the default value if the variable is not defined or the retrieved value is `NULL` or `''`.
   *
   * <p>If a global variable `$__ENV` is defined, values on it will override the real environment variables.
   *
   * @param string $var
   * @param string $default
   * @return string|int|bool
   */
  function env ($var, $default = '')
  {
    global $__ENV;
    static $MAP = [
      'false' => false,
      'off'   => false,
      'no'    => false,
      'none'  => false,
      'true'  => true,
      'on'    => true,
      'yes'   => true,
      'null'  => null,
    ];
    $v = isset($__ENV[$var]) ? $__ENV[$var] : getenv ($var);

    if ($v === false)
      return $default;
    $v = trim ($v);
    if (isset($MAP[$v]))
      $v = $MAP[$v];
    elseif (is_numeric ($v))
      return intval ($v);
    if ($v === '' || is_null ($v))
      return $default;
    if ($v[0] == '[' || $v[0] == '{') {
      $o = json_decode ($v);
      if (is_null ($o))
        throw new RuntimeException("Invalid configuration value: $v");
      return $o;
    }
    return $v;
  }
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
 * Determines if a command exists on the current operating system.
 *
 * @param string $command The command to check
 * @return bool True if the command has been found, false otherwise.
 */
function command_exists ($command)
{
  $where = (PHP_OS == 'WINNT') ? 'where' : 'which';
  return !!`$where $command`;
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
