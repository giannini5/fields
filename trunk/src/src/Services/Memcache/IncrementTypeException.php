<?php

namespace DAG\Services\Memcache;

/**
 * This represents a failed increment due to key-value not int
 */
class IncrementTypeException extends \DAGException
{
    /**
     * Will call the parent with the exception message
     *
     * @param object $previous - the previous exception
     *
     */
    public function __construct($value)
    {
        parent::__construct("Memcache increment value not integer ({$value})", -1);
    }
}
