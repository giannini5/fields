<?php

namespace DAG\Services\Memcache;

/**
 * This represents a failed connection to the memcache client
 */
class ClientException extends \DAGException
{
    /**
     * Will call the parent with the exception message
     *
     * @param object $previous - the previous exception
     *
     */
    public function __construct($previous = NULL)
    {
        parent::__construct("Memcache client error", -1, $previous);
    }
}
