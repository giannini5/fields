<?php

namespace DAG\Framework\Orm;

use DAG\Framework\Exception\Assertion;
use DAG\Framework\Exception\Precondition;

/**
 * Represents the PersistenceDriver configuration for a particular model.
 */
class PersistenceConfig
{
    // Persistence settings

    const PERSISTENCE_DRIVER  = 'PERSISTENCE_DRIVER';
    const SCHEMA              = 'SCHEMA';
    const TABLE               = 'TABLE';

    const AUTO_INC_FIELD      = 'AUTO_INC_FIELD';
    const PRIMARY_KEYS        = 'PRIMARY_KEYS';

    const CACHE_DRIVER        = 'CACHE_DRIVER';
    const CACHE_NAMESPACE     = 'CACHE_NAMESPACE';
    const CACHE_KEYS          = 'CACHE_KEYS';
    const CACHE_MULTI_KEYS    = 'CACHE_MULTI_KEYS';
    const CACHE_TTL           = 'CACHE_TTL';

    const ALT_CACHE_DRIVER    = 'ALT_CACHE_DRIVER';
    const ALT_CACHE_TTL       = 'ALT_CACHE_TTL';


    // Persistence driver options

    const DRIVER_MYSQL        = 'MYSQL';
    const DRIVER_MEMCACHE     = 'MEMCACHE';
    const DRIVER_APC          = 'APC';

    /** @var array */
    protected $config;

    /** @var string[] */
    protected $caches;

    /**
     * Initializes config based on a config array.
     * Assumes the $config is valid; $config should be validated w/ verifyConfig()
     *
     * @param array $config persistence configuration in hard-coded array form
     */
    public function __construct($config)
    {
        $this->config = $config;

        $this->caches = array();
        if (array_key_exists(self::CACHE_DRIVER, $config)) {
            $this->caches[] = self::CACHE_DRIVER;
        }
        if (array_key_exists(self::ALT_CACHE_DRIVER, $config)) {
            $this->caches[] = self::ALT_CACHE_DRIVER;
        }

        // APC should be first in the situation where you have APC and Memcache
        if (
            count($this->caches) == 2
            && $this->getCacheDriver($this->caches[0]) == self::DRIVER_MEMCACHE
            && $this->getCacheDriver($this->caches[1]) == self::DRIVER_APC
        ) {
            $this->caches = array_reverse($this->caches);
        }
    }

    /**
     * Returns the driver (one of the DRIVER constants) with which to persist the model.
     *
     * @return string
     */
    public function getPersistenceDriver()
    {
        return $this->config[self::PERSISTENCE_DRIVER];
    }

    /**
     * Returns the schema name for the persistence of this model.
     * The meaning of "schema" may vary among drivers.
     *
     * @return string
     */
    public function getPersistenceSchema()
    {
        return $this->config[self::SCHEMA];
    }

    /**
     * Returns the table name for the persistence of this model.
     * The meaning of "table" may vary among drivers.
     *
     * @return string
     */
    public function getPersistenceTable()
    {
        return $this->config[self::TABLE];
    }

    /**
     * Gets the name of the field that should be populated with the last-insert ID.
     * This may not be applicable to all drivers.
     *
     * @return null|string
     */
    public function getAutoIncField()
    {
        if (!array_key_exists(self::AUTO_INC_FIELD, $this->config)) {
            return null;
        }
        return $this->config[self::AUTO_INC_FIELD];
    }

    /**
     * Returns the primary key for the model as an array of field names.
     * The result is an array. The array will only have multiple elements in the case of a compound primary key.
     *
     * @return string[]
     */
    public function getPrimaryKeys()
    {
        return $this->config[self::PRIMARY_KEYS];
    }

    /**
     * Returns a list of cache driver keys (CACHE_DRIVER and ALT_CACHE_DRIVER if provided).
     *
     * The user of this function is encouraged to do something like:
     *     foreach (getCacheDriverKeys() as $driverKey) {
     *         getCacheDriver($driverKey);
     *         getCacheNamespace($driverKey);
     *         // ...
     *     }
     *
     * @return string[]
     */
    public function getCacheDriverKeys()
    {
        return $this->caches;
    }

    /**
     * Get cache driver for the specified cache driver key.
     *
     * @param string $cacheDriverKey driver key from getCacheDriverKeys()
     *
     * @return string
     */
    public function getCacheDriver($cacheDriverKey)
    {
        Precondition::arrayValueExists($this->caches, $cacheDriverKey, 'existingCacheDriverKeys');

        return $this->config[$cacheDriverKey];
    }

    /**
     * Get cache namespace for the specified cache driver or null if no namespace.
     *
     * @param string $cacheDriverKey driver key from getCacheDriverKeys()
     *
     * @return string|null
     */
    public function getCacheNamespace($cacheDriverKey)
    {
        Precondition::arrayValueExists($this->caches, $cacheDriverKey, 'existingCacheDriverKeys');

        if ($cacheDriverKey == self::CACHE_DRIVER) {
            if (!array_key_exists(self::CACHE_NAMESPACE, $this->config)) {
                return null;
            }
            return $this->config[self::CACHE_NAMESPACE];
        } elseif ($cacheDriverKey == self::ALT_CACHE_DRIVER) {
            if (!array_key_exists(self::CACHE_NAMESPACE, $this->config)) {
                return null;
            }
            return $this->config[self::CACHE_NAMESPACE];
        } else {
            Assertion::isTrue(false, 'bad cache driver key: ' . $cacheDriverKey);
        }
    }

    /**
     * Get cache keys for the specified cache driver.
     *
     * If data is missing for any cache keys, those cache keys will be omitted.
     * If this returns an empty array, the caller can safely conclude that the cache cannot be used for the query.
     *
     * @param string $cacheDriverKey driver key from getCacheDriverKeys()
     * @param array  $data           key/value array with the data for the model object to be cached or loaded;
     *                                   the cache keys will be generated based on this data
     *
     * @return string[]
     */
    public function getCacheKeys($cacheDriverKey, $data)
    {
        Precondition::arrayValueExists($this->caches, $cacheDriverKey, 'existingCacheDriverKeys');

        if ($cacheDriverKey == self::CACHE_DRIVER || $cacheDriverKey == self::ALT_CACHE_DRIVER) {
            if (empty($this->config[self::CACHE_KEYS])) {
                $cacheKeySpecs = array();
            } else {
                $cacheKeySpecs = $this->config[self::CACHE_KEYS];
            }
        } else {
            Assertion::isTrue(false, 'bad cache driver key: ' . $cacheDriverKey);
        }

        $result = array();
        foreach ($cacheKeySpecs as $key => $fields) {
            // array_intersect_key() will provide only the values needed for the cache key by intersecting with $fields
            $relevantData = array_values(array_intersect_key($data, array_flip($fields)));
            if (count($relevantData) == count($fields)) {
                $result[] = vsprintf($key, $relevantData);
            }
        }

        return $result;
    }

    /**
     * Get multi-cache keys for the specified cache driver.
     *
     * @see self::getCacheKeys()
     *
     * @param string $cacheDriverKey driver key from getCacheDriverKeys()
     * @param array  $data           key/value array with the data for the model object to be cached or loaded;
     *                                   the cache keys will be generated based on this data
     * @param bool   $strictMode     in strict mode, do not return multi-cache keys that are less specific than $data
     *                               Example: if you delete a model, set $strictMode = false to get the multi-cache key
     *                                   to invalidate.
     *                               Example: if you want to fetch a single model, set $strictMode = true to not
     *                                   get the multi-cache key; you do not want to load 1000 items from cache to
     *                                   return the one requested item.
     *
     * @return string[]
     */
    public function getMultiCacheKeys($cacheDriverKey, $data, $strictMode)
    {
        Precondition::arrayValueExists($this->caches, $cacheDriverKey, 'existingCacheDriverKeys');

        if ($cacheDriverKey == self::CACHE_DRIVER || $cacheDriverKey == self::ALT_CACHE_DRIVER) {
            if (empty($this->config[self::CACHE_MULTI_KEYS])) {
                $cacheKeySpecs = array();
            } else {
                $cacheKeySpecs = $this->config[self::CACHE_MULTI_KEYS];
            }
        } else {
            Assertion::isTrue(false, 'bad cache driver key: ' . $cacheDriverKey);
        }

        $result = array();
        foreach ($cacheKeySpecs as $key => $fields) {
            // array_intersect_key() will provide only the values needed for the cache key by intersecting with $fields
            $relevantData = array_values(array_intersect_key($data, array_flip($fields)));

            if ($strictMode && count($relevantData) != count($data)) {
                continue;
            }

            if (count($relevantData) == count($fields)) {
                $result[] = vsprintf($key, $relevantData);
            }
        }

        return $result;
    }

    /**
     * Get cache ttl for the specified cache driver or null if no namespace.
     *
     * @param string $cacheDriverKey driver key from getCacheDriverKeys()
     *
     * @return string|null
     */
    public function getCacheTtl($cacheDriverKey)
    {
        Precondition::arrayValueExists($this->caches, $cacheDriverKey, 'existingCacheDriverKeys');

        if ($cacheDriverKey == self::CACHE_DRIVER) {
            if (!array_key_exists(self::CACHE_TTL, $this->config)) {
                return null;
            }
            return $this->config[self::CACHE_TTL];
        } elseif ($cacheDriverKey == self::ALT_CACHE_DRIVER) {
            if (!array_key_exists(self::ALT_CACHE_TTL, $this->config)) {
                return null;
            }
            return $this->config[self::ALT_CACHE_TTL];
        } else {
            Assertion::isTrue(false, 'bad cache driver key: ' . $cacheDriverKey);
        }
    }

    /**
     * Provides a list of fields that are referenced by CACHE_KEYS, and PRIMARY_KEYs.
     *
     * @return array where the fields are both the keys and the values
     */
    public function getReferencedFields()
    {
        $result = array();

        foreach ($this->config[self::PRIMARY_KEYS] as $field) {
            $result[$field] = $field;
        }

        if (array_key_exists(self::CACHE_KEYS, $this->config)) {
            foreach ($this->config[self::CACHE_KEYS] as $cacheKeyFormat => $fields) {
                foreach ($fields as $field) {
                    $result[$field] = $field;
                }
            }
        }

        return $result;
    }

    /**
     * Verifies that a configuration is correct via precondition checks.
     * This should NOT be done at runtime but should be done via unit tests for every model's config.
     *
     * @param array  $config     persistence configuration in hard-coded array form
     * @param string $configName name of the config being verified (used for error messages when verification fails)
     */
    public static function verifyConfig($config, $configName = '')
    {
        // For each allowed field in $config, indicate if it is required
        $allFields = array(
            self::PERSISTENCE_DRIVER  => true,
            self::SCHEMA              => true,
            self::TABLE               => true,
            self::AUTO_INC_FIELD      => false,
            self::PRIMARY_KEYS        => true,
            self::CACHE_DRIVER        => false,
            self::CACHE_NAMESPACE     => false,
            self::CACHE_MULTI_KEYS    => false,
            self::CACHE_KEYS          => false,
            self::CACHE_TTL           => false,
            self::ALT_CACHE_DRIVER    => false,
            self::ALT_CACHE_TTL       => false,
        );

        $errPrefix = "persistence config '$configName' err: ";

        // Check for unknown fields
        foreach ($config as $key => $value) {
            Precondition::isTrue(
                array_key_exists($key, $allFields),
                $errPrefix . 'unknown field ' . $key
            );
        }

        // Check for missing fields
        foreach ($allFields as $key => $value) {
            Precondition::isTrue(
                !$value || array_key_exists($key, $config),
                $errPrefix . 'required setting is missing: ' . $key
            );
        }

        // Check persistence settings
        Precondition::isTrue(
            in_array(
                $config[self::PERSISTENCE_DRIVER],
                array(self::DRIVER_MYSQL)
            ),
            $errPrefix . 'persistence driver ' . $config[self::PERSISTENCE_DRIVER] . ' not allowed'
        );
        Precondition::isTrue(
            $config[self::SCHEMA] && is_string($config[self::SCHEMA]),
            $errPrefix . 'schema format is bad'
        );
        Precondition::isTrue(
            $config[self::TABLE] && is_string($config[self::TABLE]),
            $errPrefix . 'table format is bad'
        );
        Precondition::isTrue(
            !array_key_exists(self::AUTO_INC_FIELD, $config) ||
            ($config[self::TABLE] && is_string($config[self::TABLE])),
            $errPrefix . 'auto-inc format is bad'
        );
        Precondition::isTrue(
            $config[self::PRIMARY_KEYS] && is_array($config[self::PRIMARY_KEYS]),
            $errPrefix . 'primary keys format is bad'
        );
        Precondition::isTrue(
            count($config[self::PRIMARY_KEYS]) == count(array_filter($config[self::PRIMARY_KEYS], 'is_string')),
            $errPrefix . 'a primary keys item format is bad'
        );

        // Check cache settings
        if (array_key_exists(self::CACHE_DRIVER, $config)) {
            Precondition::isTrue(
                in_array($config[self::CACHE_DRIVER], array(self::DRIVER_APC, self::DRIVER_MEMCACHE)),
                $errPrefix . 'cache driver ' . $config[self::CACHE_DRIVER] . ' not allowed'
            );
            Precondition::isTrue(
                array_key_exists(self::CACHE_KEYS, $config) || array_key_exists(self::CACHE_MULTI_KEYS, $config),
                $errPrefix . 'cache keys or multi cache keys must be specified'
            );
            Precondition::isTrue(
                !array_key_exists(self::CACHE_NAMESPACE, $config) || is_string($config[self::CACHE_NAMESPACE]),
                $errPrefix . 'cache ns should be a string'
            );
            Precondition::isTrue(
                !array_key_exists(self::CACHE_TTL, $config) || is_numeric($config[self::CACHE_TTL]),
                $errPrefix . 'cache TTL should be numeric'
            );
        } else {
            Precondition::isTrue(
                !array_key_exists(self::CACHE_NAMESPACE, $config),
                $errPrefix . 'cache ns should not be specified if cache disabled'
            );
            Precondition::isTrue(
                !array_key_exists(self::CACHE_KEYS, $config),
                $errPrefix . 'cache keys should not be specified if cache disabled'
            );
            Precondition::isTrue(
                !array_key_exists(self::CACHE_MULTI_KEYS, $config),
                $errPrefix . 'multi cache keys should not be specified if cache disabled'
            );
            Precondition::isTrue(
                !array_key_exists(self::CACHE_TTL, $config),
                $errPrefix . 'cache TTL should not be specified if cache disabled'
            );
        }

        // Check alt cache settings
        if (array_key_exists(self::ALT_CACHE_DRIVER, $config)) {
            Precondition::isTrue(
                array_key_exists(self::CACHE_DRIVER, $config),
                $errPrefix . 'cannot specify alt cache if no main cache'
            );
            Precondition::isTrue(
                in_array($config[self::ALT_CACHE_DRIVER], array(self::DRIVER_APC, self::DRIVER_MEMCACHE)),
                $errPrefix . 'alt cache driver ' . $config[self::ALT_CACHE_DRIVER] . ' not allowed'
            );
            Precondition::isTrue(
                !array_key_exists(self::ALT_CACHE_TTL, $config) || is_numeric($config[self::ALT_CACHE_TTL]),
                $errPrefix . 'alt cache TTL should be numeric'
            );
        } else {
            Precondition::isTrue(
                !array_key_exists(self::ALT_CACHE_TTL, $config),
                $errPrefix . 'alt cache TTL should not be specified if cache disabled'
            );
        }

        // Check cache keys format for both caches
        foreach ($config as $key => $value) {
            if ($key == self::CACHE_KEYS || $key == self::CACHE_MULTI_KEYS) {
                foreach ($value as $cacheKeyFormat => $fields) {
                    Precondition::isTrue(
                        $cacheKeyFormat && is_string($cacheKeyFormat),
                        $errPrefix . 'in ' . $key . ' cache key format must be string'
                    );
                    Precondition::isTrue(
                        $fields && is_array($fields),
                        $errPrefix . 'in ' . $key . ' fields for ' . $cacheKeyFormat . ' must be array'
                    );
                    Precondition::isTrue(
                        count($fields) == count(array_filter(array_filter($fields), 'is_string')),
                        $errPrefix . 'in ' . $key . ' fields for ' . $cacheKeyFormat . ' must be string'
                    );
                }
            }
        }

    }

}
