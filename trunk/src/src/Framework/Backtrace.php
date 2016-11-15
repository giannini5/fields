<?php

namespace DAG\Framework;

/**
 * Encapsulates our handling and output of backtrace information
 */
class Backtrace
{
    protected $backtraceString;

    /**
     * Creates an instance for a given backtrace
     *
     * @see debug_backtrace()
     * @param array $debugBacktrace the result of a debug_backtrace() call
     */
    public function __construct($debugBacktrace)
    {
        $this->backtraceString = $this->getBacktraceString($debugBacktrace);
    }

    /**
     * Outputs the backtrace as a custom formatted string
     *
     * @param string $backtracePrefix arbitrary string to prefix to the result
     * @return string
     */
    public function asString($backtracePrefix = "\n  Stack trace:\n")
    {
        return $backtracePrefix . $this->backtraceString;
    }

    /**
     * Outputs the backtrace as a custom formatted string
     *
     * @return string
     */
    public function __toString()
    {
        return $this->asString();
    }

    /**
     * Get the stack backtrace as a string.  One call per line
     *
     * @param array $backtrace - Backtrace returned from a call to debug_backtrace()
     * @return string
     */
    protected function getBacktraceString($backtrace)
    {
        $backtraceString = '';

        // For each call in the stack (in reverse order) compose the backstrace string for the call
        foreach (array_reverse($backtrace, true) as $lastCall) {
            // Construct the argument string
            $argsString = '';
            if (isset($lastCall['args'])) {
                foreach ($lastCall['args'] as $arg) {
                    $separator = strlen($argsString) > 0 ? ', ' : '';
                    $argString = $this->getArgString($arg);
                    $argsString = $argsString . $separator . $argString;
                }
            }

            // Construct the lastCallString
            $fileName = $this->normalizeFileName(isset($lastCall['file']) ? $lastCall['file'] : '');
            $className = isset($lastCall['class']) ? $lastCall['class'] : '';
            $isClassAnException = $this->isException($className);
            $functionName = isset($lastCall['function']) ? $lastCall['function'] : '';

            $lastCallString = sprintf(
                "%-40s %s",
                $fileName . '(' . (isset($lastCall['line']) ? $lastCall['line'] : '') . ')',
                ($isClassAnException ? 'throw ' : '') .
                    $className .
                    (isset($lastCall['type']) ? $lastCall['type'] : '') .
                    $functionName .
                    '(' . $argsString . ')'
            );

            // Append to the backtraceString
            $backtraceString .= strlen($backtraceString) > 0 ? "\n" : '';
            $backtraceString .= "  ";
            $backtraceString .= $lastCallString;
        }

        return $backtraceString;
    }


    /**
     * Strip off the well known path prefix on the fileName and return the result
     *
     * @param string $fileName
     * @return string
     */
    protected function normalizeFileName($fileName)
    {
        // Get the Root directory by removing /api from the end of the API_ROOT directory
        $rootDirectory = dirname($_SERVER['API_ROOT']);

        if ($rootDirectory != substr($fileName, 0, strlen($rootDirectory))) {
            return $fileName;
        }

        // Return the path to the file with the root directory removed since it's redundant
        return substr($fileName, strlen($rootDirectory));
    }

    /**
     * Get a string representation of an argument
     *
     * @param mixed $arg argument to be examined
     * @return string
     */
    protected function getArgString($arg)
    {
        if (is_bool($arg)) {
            return ($arg ? 'true' : 'false');
        }

        if (is_string($arg)) {
            return "'{$arg}'";
        }

        if (is_array($arg)) {
            return 'array';
        }

        if (is_object($arg)) {
            return get_class($arg);
        }

        if (is_numeric($arg)) {
            return "{$arg}";
        }

        if (is_null($arg)) {
            return "null";
        }

        return "unknownArg";
    }

    /**
     * Return true if $className is an Exception
     *
     * @param string $className class name tested to see if it's an exception
     * @return bool
     */
    protected function isException($className)
    {
        if (strlen($className) > 0 && class_exists($className)) {
            return (is_subclass_of($className, 'Exception') || strcmp($className, 'Exception') == 0);
        }

        return false;
    }
}
