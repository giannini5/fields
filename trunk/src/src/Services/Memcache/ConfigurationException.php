<?php

namespace DAG\Services\Memcache;

/**
 * This represents a bad configuration in config.php
 */
class ConfigurationException extends \DAGException
{
    /**
    * Will call the parent with the exception message
    *
    * @param int   $serverIndex    - the server index for connection details
    * @param array $configurations - the array of configurations
    *
    */
    public function __construct($serverIndex, $configurations)
    {
        parent::__construct("Memcache Invalid configuration serverIndex: $serverIndex configArray:" .
            json_encode($configurations), -1);
    }
}
