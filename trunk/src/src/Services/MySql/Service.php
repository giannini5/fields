<?php

namespace DAG\Services\MySql;

use DAG\Framework\Configuration;
use DAG\Framework\Exception\Precondition;

/**
 * MySQL Service provides a MySQL driver for the requested schema.
 *
 * It is responsible for using the configured schema-to-host/credentials map.
 * It is also responsible for storing a pool of open connections (drivers) in order reuse drivers when possible.
 */
class Service
{
    protected $connectionTimeout;

    protected $defaultPort, $defaultTimeout, $defaultPersistent;

    /** @var Database[] schema-to-Database map  */
    protected $databases = array();

    /**
     * @param Configuration $config
     */
    public function __construct(Configuration $config)
    {
        $this->connectionTimeout = $config->get('DATABASE_CONNECTION_TIMEOUT');

        $this->defaultPort       = $config->get('DB_DEFAULT_PORT');
        $this->defaultTimeout    = $config->get('DB_DEFAULT_TIMEOUT');
        $this->defaultPersistent = $config->get('DB_DEFAULT_PERSISTENT');
    }

    /**
     * Provides a Database.
     *
     * @param string $schema schema name; the schema should exist in config.php
     *
     * @return Database
     */
    public function getDatabase($schema)
    {
        if (!array_key_exists($schema, $this->databases)) {
            $driver = $this->createDriver($schema);
            $this->databases[$schema] = new Database($driver);
        }

        return $this->databases[$schema];
    }

    /**
     * Instantiates and provides a Driver.
     *
     * @param string $schema schema name; the schema should exist in config.php
     *
     * @return Driver
     */
    protected function createDriver($schema)
    {
        global $gDBConnectInfo;

        Precondition::arrayKeyExists($gDBConnectInfo, $schema, 'gDBConnectInfo');

        $info = $gDBConnectInfo[$schema];
        return new Driver(
            $this->connectionTimeout,
            $info['host'],
            isset($info['port'])       ? $info['port']       : $this->defaultPort,
            $info['db'],
            $info['user'],
            $info['pwd'],
            isset($info['timeout'])    ? $info['timeout']    : $this->defaultTimeout,
            isset($info['persistant']) ? $info['persistant'] : $this->defaultPersistent
        );
    }
}
