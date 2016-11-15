<?php

namespace DAG\Services\Dns;

use DAG\Framework\Exception\Precondition;

/**
 * This class comprises of dns lookups using APC cache
 * 
 * @package Pinger\Services\Dns
 */
class DnsCaching
{
    /**
    * finds and returns the IP address for a hostname
    * 
    * @param string $hostname - hostname of server
    * 
    * @return string - IP address
    */
    public static function getByHostname($hostname)
    {
        Precondition::isNonEmptyString($hostname, 'hostname');
        
        $pieces = explode(':',$hostname);
        $port   = null;
        
        if (is_array($pieces)) {
            $hostname = "";

            foreach ($pieces as $piece) {
                if (0 !== strcasecmp($piece, strval((int)$piece))) {
                    $hostname .= (0 === strlen($hostname)) ? $piece : (":" . $piece);
                } else {
                    $port = $piece;
                    break;
                }
            }
        }
        
        unset($pieces);
        
        $ipAddress = gethostbyname($hostname);

        return (is_null($port)) ? $ipAddress : ($ipAddress . ':' . $port);
    }
}
