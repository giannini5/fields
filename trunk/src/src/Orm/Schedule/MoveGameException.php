<?php

namespace DAG\Orm\Schedule;

/**
 * This represents a failed game move
 */
class MoveGameException extends \DAGException
{
    /**
     * @param string        $message
     * @param \Exception    $e
     *
     */
    public function __construct($message, $e = null)
    {
        parent::__construct($message, -1, $e);
    }
}
