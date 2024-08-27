<?php

/**
 * PreconditionException is fired from the precondition function.
 *
 * @param string  $message      Message to be included in PreconditionException
 * @param string  $fileName     Name of the file where the precondition is being checked
 * @param int     $lineNumber   Line number in file where precondition is being checked
 * @param int     $code         Error code to be included in PreconditionException (defaults to -1)
 */
class PreconditionException extends \DAGException
{
    public function __construct($message, $fileName, $lineNumber, $code = -1)
    {
        parent::__construct($message, $code, null);
        $this->file = $fileName;
        $this->line = $lineNumber;
    }
}
