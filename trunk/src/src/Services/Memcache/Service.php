<?php

namespace DAG\Services\Memcache;

use DAG\Framework\Exception\Precondition;

/**
 * This class comprises of the Memcache Service
 */
class Service
{
    /** @var Driver[] array of of memcache drivers by type */ 
    private static $memcacheDrivers = array();
    
    /**
     * Create the Service
     *
     */
    public function __construct()
    {
        self::$memcacheDrivers[Driver::TYPE_ADSERVER] = null;
        self::$memcacheDrivers[Driver::TYPE_DEFAULT]  = null;
    }
    
    /**
     * Gets a driver of a specific type
     * 
     * @param string $type - id of type of driver
     * 
     * @return Driver
     */
    public function getDriver($type = Driver::TYPE_DEFAULT)
    {
        Precondition::arrayKeyExists(self::$memcacheDrivers, $type, 'type');
        
        if (is_null(self::$memcacheDrivers[$type])) {
            self::$memcacheDrivers[$type] = new Driver($type);
        }
        
        return self::$memcacheDrivers[$type];
    }
}
