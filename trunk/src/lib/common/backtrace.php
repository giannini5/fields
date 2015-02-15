<?php
/**
* DAG_Backtrace.php
* 
* Contains a class tha encapsulates backtraces.
*/
  
  
/**
 * Backtrace encapsulates our handling and output of backtrace information.
 */
class DAG_Backtrace {
    
    public $m_backtraceString;
    
    /**
     * Create a Backtrace object.
     * 
     * @param array $debugBacktrace - The result of a debug_backtrace() call.
     * @return Backtrace
     */
    public function __construct($debugBacktrace) {
        $this->m_backtraceString = $this->_getBacktraceString($debugBacktrace);
    }
    
    /**
     * Outputs the backtrace as a prettily formatted string.
     * 
     * @return string - The backtrace string.
     */
    public function asString($backtracePrefix = "\n  Stack trace:\n") {
        return $backtracePrefix . $this->m_backtraceString;
    }

    /**
     * Outputs the backtrace as a prettily formatted string.
     * 
     * @return string - The backtrace string.
     */
    public function __toString() {
        return $this->asString();
    }
    
    /**
     * Get the stack backtrace as a string.  One call per line.
     *
     * @param array $backtrace - Backtrace returned from a call to debug_backtrace()
     */
    public function _getBacktraceString($backtrace) {
        $backtraceString = "";

        // For each call in the stack (in reverse order) compose the backstrace string for the call
        foreach (array_reverse($backtrace, true) as $lastCall) {
            // Construct the argument string
            $argsString = '';
            if (isset($lastCall['args'])) {
                foreach ($lastCall['args'] as $arg) {
                    $separator = strlen($argsString) > 0 ? ', ' : '';
                    $argString = $this->_getArgString($arg);
                    $argsString = $argsString . $separator . $argString;
                }
            }

            // Construct the lastCallString
            $fileName = $this->_normalizeFileName(isset($lastCall['file']) ? $lastCall['file'] : '');
            $className = isset($lastCall['class']) ? $lastCall['class'] : '';
            $isClassAnException = $this->_isException($className);
            $functionName = isset($lastCall['function']) ? $lastCall['function'] : '';

            $lastCallString = sprintf("%-40s %s",
                $fileName .
                '(' . (isset($lastCall['line']) ? $lastCall['line'] : '') . ')',
                ($isClassAnException ? 'throw ' : '') .
                $className .
                (isset($lastCall['type']) ? $lastCall['type'] : '') .
                $functionName .
                '(' . $argsString . ')');

            // Append to the backtraceString
            $backtraceString .= strlen($backtraceString) > 0 ? "\n" : '';
            $backtraceString .= "  ";
            $backtraceString .= $lastCallString;
        }

        return $backtraceString;
    }
    
    
    /**
     * Strip off the well known path prefix on the fileName
     *
     * @param string - $fileName
     *
     * @return string - Normalized fileName
     */
    public function _normalizeFileName($fileName) {
        // Get the Root directory by removing /api from the end of the API_ROOT directory
        $lastDirectory = strrchr(SRC_ROOT, "/");
        $rootDirectory = substr(SRC_ROOT, 0, strlen(SRC_ROOT) - strlen($lastDirectory));

        // Return the path to the file with the root directory removed since it's redundant
        return substr($fileName, strlen($rootDirectory), strlen($fileName));
    }
    
    /**
     * Get a string representation of an argument
     *
     * @param mixed $arg - Argument to be examined
     *
     * @return string - String representation of passed in argument
     */
    public function _getArgString($arg) {
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

        return "unkownArg";
    }
    
    /**
     * Return true if $class is an Exception
     *
     * @param string $className - Class name tested to see if it's an exception
     *
     * @return bool             - true if $className is an Exception; false otherwise.
     */
    public function _isException($className) {
        if (strlen($className) > 0 && class_exists($className)) {
            return (is_subclass_of($className, 'Exception') || strcmp($className, 'Exception') == 0); 
        }

        return false;
    }
}
?>
