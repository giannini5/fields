<?php

namespace DAG\Services\Apc;


use DAG\Framework\Exception\Precondition;

/**
 * This class comprises of the APC Service which returns the driver for APC
 */
class Service
{
    /** @var Driver[] instances of Apc\Driver by type */ 
    private static $drivers = array();
    
    public function __construct()
    {
        self::$drivers[Driver::TYPE_APACHE] = null;
        self::$drivers[Driver::TYPE_QUEUE]  = null;
    }
    
    /**
     * Gets a driver
     * 
     * @param int $type - indicates which process we're running in, 1 for apache, 2 if running as a queue. If null is
     *                    passed, the service will figure out the process using php_sapi_name()
     * 
     * @return Driver
     */
    public function getDriver($type = null)
    {
        if (is_null($type)) {
            $type = (0 === strcasecmp('apache', substr(php_sapi_name(), 0, 6))) ? Driver::TYPE_APACHE : Driver::TYPE_QUEUE; 
        }

        if (is_null(self::$drivers[$type])) {
            self::$drivers[$type] = new Driver($type);
        }
        
        return self::$drivers[$type];
    }
}
