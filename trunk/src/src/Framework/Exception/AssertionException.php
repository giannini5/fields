<?php
/**
 * AssertionException is fired from the assertion function.
 *
 * @param string  $message      Message to be included in AssertionException
 * @param string  $fileName     Name of the file where the assertion is being checked
 * @param int     $lineNumber   Line number in file where assertion is being checked
 * @param int     $code         Error code to be included in AssertionException (defaults to -1)
 */
class AssertionException extends DAGException
{
    public function __construct($message, $fileName, $lineNumber, $code = -1) {
        parent::__construct($message, $code, null);
        $this->file = $fileName;
        $this->line = $lineNumber;
    }
}
