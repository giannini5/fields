<?php

namespace DAG\Framework;

use DAG\Framework\Exception\Precondition;
use DAG\Services\Memcache;
use DAG\Services\Apc;
use DAG\Services\MySql;

/**
 * Provides access to the services used by the DAG backend.
 * Is a singleton designed to be used statically.
 */
class Services
{
    /** @var Configuration */
    private $config;

    /** @var MySql\Service */
    private $mySqlService;

    /** @var Apc\Service */
    private $apcService;

    /** @var Memcache\Service */
    private $memcacheService;

    /** @var Services */
    protected static $instance;

    /**
     * Sets up the Services singleton instance based on the provided configuration.
     * This can only be done once and must be done before any services are requested.
     * @param Configuration $configuration
     */
    public static function setup(Configuration $configuration)
    {
        assertion(self::$instance === null, 'Services cannot be setup multiple times');
        self::$instance = new Services($configuration);
    }

    /**
     * Provides an instance of the MySql service.
     * @return MySql\Service
     */
    public static function getMySqlService()
    {
        self::verifyInstance();
        return self::$instance->createMySqlService();
    }

    /**
     * Provides an instance of the Apc service.
     * @return Apc\Service
     */
    public static function getApcService()
    {
        self::verifyInstance();
        return self::$instance->createApcService();
    }

    /**
     * Provides an instance of the Memcache service.
     * @return Memcache\Service
     */
    public static function getMemcacheService()
    {
        self::verifyInstance();
        return self::$instance->createMemcacheService();
    }

    /**
     * Creates the singleton instance of Services if it does not exist.
     */
    protected static function verifyInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self(new Configuration());
        }
    }

    /**
     * Creates a Services instance that operates with the specified configuration.
     * The configuration determines which services to instantiate.
     * @param Configuration $config
     */
    private function __construct(Configuration $config)
    {
        $this->config = $config;
    }

    /**
     * Creates a MySql service if needed, then returns it.
     * @return MySql\Service
     */
    private function createMySqlService()
    {
        if (!$this->mySqlService) {
            $this->mySqlService = new MySql\Service($this->config);
        }
        return $this->mySqlService;
    }

    /**
     * Creates a Apc service if needed, then returns it.
     * @return Apc\Service
     */
    private function createApcService()
    {
        if (!$this->apcService) {
            $this->apcService = new Apc\Service();
        }
        return $this->apcService;
    }

    /**
     * Creates a Memcache service if needed, then returns it.
     * @return Memcache\Service
     */
    private function createMemcacheService()
    {
        if (!$this->memcacheService) {
            $this->memcacheService = new Memcache\Service();
        }
        return $this->memcacheService;
    }
}
