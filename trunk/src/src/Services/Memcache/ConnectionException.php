<?php

namespace DAG\Services\Memcache;

/**
 * This represents a failed connection to the memcache server
 */
class ConnectionException extends \DAGException
{
    /**
     * Will call the parent with the exception message
     * 
     * @param string $hostname - name of host
     * @param int    $tcpPort  - port used for tcp protocol
     * @param int    $udpPort  - port used for upd protocol
     */
    public function __construct($hostname, $tcpPort, $udpPort)
    {
        parent::__construct("Memcache connection error hostname:$hostname tcpPort:$tcpPort udpPort:$udpPort");
    }
}
