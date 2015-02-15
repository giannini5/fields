<?php

require_once SRC_LIB . 'common/backtrace.php';

/**
 * This is the base class for all custom DAG exceptions
 * in PHP. Any new exceptions just need to extend this class
 * and provide a constructor which calls this parent constructor.
 */

class DAG_Exception extends Exception
{

    public static $helperFunctions = array("assertion", "precondition");

    protected $logEvent;
    protected $debugBacktrace;

    /**
     * Constructor for creating/extending a DAG_Exception
     *
     * @param string     $message        - the exception message, to be displayed
     * @param integer    $code           - a unique error code for the exception
     * @param Exception  $previous       - the previous exception, for nesting
     */
    public function __construct($message, $code = -1, Exception $previous = null) {
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
     * @return string $string          - String representation of exception
     */
    public function asString($verbose = true, $includeBacktrace = true, $prefixPadding = "  ", $includeFileNameAndLineNumber = true) {
        $fileName = $this->_normalizeFileName($this->file);
        $fileNameAndLineNumberString = $includeFileNameAndLineNumber ? " in {$fileName}({$this->line})" : '';

        $string = $prefixPadding;
        $string .= get_class($this) . ": {$this->message}{$fileNameAndLineNumberString}";

        if (true == $verbose) {
            if (null != $this->getPrevious()) {
                if (true == $this->_isDAG_Exception(get_class($this->getPrevious()))) {
                    $string .= "\n{$this->getPrevious()->asString($verbose, $includeBacktrace, $prefixPadding, true)}";
                }
                else {
                    $exception = $this->getPrevious();
                    $fileName = $this->_normalizeFileName($exception->file);
                    $string .= "\n{$prefixPadding}Exception: {$exception->getMessage()} in {$fileName}({$exception->line})";
                }
            }

            if (true == $includeBacktrace) {
                $string .= $this->_getBacktraceString($prefixPadding);
            }
        }

        return $string;
    }

    /**
     * Log this exception.  Only logging the top most exception.
     *
     * @param  string $message - Message to include with logged exception
     */
    public function log($message = '') {
        printf("%s: %s\n%s\n", get_class($this), $message, $this->asString());
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
     * Get the stack backtrace as a string.  One call per line.
     *
     * @param string $prefixPadding - The prefix padding for the exception.
     */
    public function _getBacktraceString($prefixPadding = ' ') {
        $backtracePrefix = "\n" . $prefixPadding . "Stack trace:\n";

        $pingerBacktrace = new DAG_Backtrace($this->debugBacktrace);

        return $pingerBacktrace->asString($backtracePrefix);
    }

    /**
     * Return true if $class is an Exception
     *
     * @param string $className - Class name tested to see if it's an exception
     *
     * @return bool             - true if $className is an Exception; false otherwise.
     */
    public function _isException($className) {
        if (strlen($className) > 0 and class_exists($className)) {
            return (is_subclass_of($className, 'Exception') or strcmp($className, 'Exception') == 0);
        }

        return false;
    }

    /**
     * Return true if $class is a DAG_Exception
     *
     * @param string $className - Class name tested to see if it's an exception
     *
     * @return bool             - true if $className is a DAG_Exception; false otherwise.
     */
    public function _isDAG_Exception($className) {
        if (strlen($className) > 0 and class_exists($className)) {
            return (is_subclass_of($className, 'DAG_Exception') or strcmp($className, 'DAG_Exception') == 0);
        }

        return false;
    }

    /**
    * Strip off the well known path prefix on the fileName
    *
    * @param string - $fileName
    *
    * @return string - Normalized fileName
    */
    public function _normalizeFileName($fileName) {
        // Get the Root directory by removing the SRC_ROOT from the file path
        $lastDirectory = strrchr(SRC_ROOT, "/");
        $rootDirectory = substr(SRC_ROOT, 0, strlen(SRC_ROOT) - strlen($lastDirectory));

        // Return the path to the file with the root directory removed since it's redundant
        return substr($fileName, strlen($rootDirectory), strlen($fileName));
    }
}


/**
 * PreconditionException is fired from the preconditon function.
 *
 * @param string  - $message      - Message to be included in PreconditionException
 * @param string  - $fileName     - Name of the file where the precondition is being checked
 * @param int     - $lineNumber   - Line number in file where precondition is being checked
 * @param int     - $code         - Error code to be included in PreconditionException (defaults to -1)
 */
class PreconditionException extends DAG_Exception
{
    public function __construct($message, $fileName, $lineNumber, $code = -1) {
        parent::__construct($message, $code, null);
        $this->file = $fileName;
        $this->line = $lineNumber;
    }
}


/**
 * AssertionException is fired from the assertion function.
 *
 * @param string  - $message      - Message to be included in PreconditionException
 * @param string  - $fileName     - Name of the file where the precondition is being checked
 * @param int     - $lineNumber   - Line number in file where precondition is being checked
 * @param int     - $code         - Error code to be included in PreconditionException (defaults to -1)
 */
class AssertionException extends DAG_Exception
{
    public function __construct($message, $fileName, $lineNumber, $code = -1) {
        parent::__construct($message, $code, null);
        $this->file = $fileName;
        $this->line = $lineNumber;
    }
}

/**
 * UnexpectedException is used when cathching an exception that is unexpected.  Consturct this exception and nest
 * the caught exception (if any) for simplified debugging.
 *
 * @param object  - $previous     - Previous excetpion (defauts to null)
 * @param int     - $code         - Error code to be included in PreconditionException (defaults to -1)
 */
class UnexpectedException extends DAG_Exception
{
    public function __construct($previous = null, $code = -1) {
        parent::__construct(null, $code, $previous);
    }
}


/**
 * Preconditions are used to verify input parameter's or other conditions that need to be valid
 * prior to a method's execution.
 *
 * @param boolean - $expression   - Boolean expression.  If false then PreconditionException is thrown
 * @param string  - $message      - Message to be included in PreconditionException
 * @param int     - $code         - Error code to be included in PreconditionException (defaults to -1)
 *
 * @raises object - PreconditionException
 * @throws PreconditionException
 */
function precondition($expression, $message, $code = -1) {
    if (!$expression) {
	    $backtrace = debug_backtrace();
	    $lastCall = $backtrace[0];
        throw new PreconditionException($message, $lastCall['file'], $lastCall['line'], $code);
    }
}


/**
 * Assertions are used to assert correctness after execution of a method has begun.
 *
 * @param boolean - $expression   - Boolean expression.  If false then AssertionException is thrown
 * @param string  - $message      - Message to be included in AssertionException
 * @param int     - $code         - Error code to be included in AssertionException (defaults to -1)
 *
 * @raises object - AssertionException
 * @throws AssertionException
 */
function assertion($expression, $message, $code = -1) {
    if (!$expression) {
	    $backtrace = debug_backtrace();
	    $lastCall = $backtrace[0];
        throw new AssertionException($message, $lastCall['file'], $lastCall['line'], $code);
    }
}

?>
