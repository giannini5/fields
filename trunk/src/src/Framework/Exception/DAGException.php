<?php

use DAG\Framework\Backtrace;
use DAG\Framework\Services;

/**
 * This is the base class for all DAG custom exceptions
 * in PHP. Any new exceptions just need to extend this class
 * and provide a constructor which calls this parent constructor.
 *
 */
class DAGException extends \Exception
{
    protected $debugBacktrace;

    /**
     * Constructor for creating/extending a DAGException
     *
     * @param string     $message        - the exception message, to be displayed
     * @param integer    $code           - a unique error code for the exception
     * @param \Exception  $previous       - the previous exception, for nesting
     */
    public function __construct($message = '', $code = -1, \Exception $previous = null) {
        parent::__construct($message, $code, $previous);
        $this->debugBacktrace = debug_backtrace();
    }

    /**
     * Create a pretty print string version of the exception.
     *
     * NOTE: This function should be overridden by any
     *       exception which needs a custom string output.
     *
     * @param bool   $verbose          - If true then nested exceptions area included - each on their own line
     * @param bool   $includeBacktrace - If true then append stack backtrace that lead to the exception (verbose must also be true)
     * @param string $prefixPadding    - Spaces to add prior to serializing exception to string to help with readability
     * @param bool   $includeFileNameAndLineNumber
     *
     * @return string $string                  - String representation of exception
     */
    public function asString($verbose = true, $includeBacktrace = true, $prefixPadding = "  ", $includeFileNameAndLineNumber = true) {
        $fileName = $this->normalizeFileName($this->file);
        $fileNameAndLineNumberString = $includeFileNameAndLineNumber ? " in {$fileName}({$this->line})" : '';

        $string = $prefixPadding;
        $string .= get_class($this) . ": {$this->message}{$fileNameAndLineNumberString}";

        if (true == $verbose) {
            if (null != $this->getPrevious()) {
                if ($this->getPrevious() instanceof DAGException) {
                    $string .= "\n" . $this->getPrevious()->asString($verbose, $includeBacktrace, $prefixPadding, true);
                }
                else {
                    $exception = $this->getPrevious();
                    $fileName = $this->normalizeFileName($exception->file);
                    $string .= "\n{$prefixPadding}Exception: {$exception->getMessage()} in {$fileName}({$exception->line})";
                }
            }

            if (true == $includeBacktrace) {
                $string .= new Backtrace($this->debugBacktrace);
            }
        }

        return $string;
    }

    /**
     * Log this exception to the trace log file as an error.  Only logging the top most exception.
     *
     * @param  object   $logger  - Deprecated.
     * @param  string   $message - Message to include with logged exception
     * @param  constant $event   - Deprecated.
     * @param  constant $level   - Deprecated.
     * @deprecated
     */
    public function log($logger = NULL, $message = '', $event = null, $level = null) {
    }

    /**
     * Log this exception to the trace log file as a trace event.
     *
     * @param  object   $logger           - Deprecated.
     * @param  string   $traceEvent       - The trace event name to use.
     * @param  string   $message          - Message to include with logged exception
     * @param  bool     $includeBacktrace - If true, a backtrace will be included with the log line.
     * @deprecated
     */
    public function logAsTrace($logger, $traceEvent, $message, $includeBacktrace = false) {
        if(!empty($message) && (strcmp($message, $this->message ) != 0)) {
            $this->message = "$message\n  {$this->message}";
        }
    }

    /**
     * Create a pretty print string version of the exception.
     *
     * NOTE: This function should be overridden by any
     *       exception which needs a custom string output.
     *
     * @return string - String representation of exception
     */
    final public function __toString() {
        return $this->asString();
    }

    /**
     * Strip off the well known path prefix on the fileName
     *
     * @param string - $fileName
     *
     * @return string - Normalized fileName
     */
    protected function normalizeFileName($fileName) {
        // Get the Root directory by removing /api from the end of the API_ROOT directory
        $rootDirectory = dirname($_SERVER['API_ROOT']);

        if ($rootDirectory != substr($fileName, 0, strlen($rootDirectory))) {
            return $fileName;
        }

        // Return the path to the file with the root directory removed since it's redundant
        return substr($fileName, strlen($rootDirectory));
    }
}
