<?php

namespace DAG\Framework\Orm;

use DAG\Framework\Exception\Assertion;
use DAG\Framework\Exception\Precondition;
use DAG\Framework\Orm\FieldValidator as FV;
use DAG\Framework\Orm\PersistenceConfig as PC;
use DAG\Framework\Services;
use DAG\Services\Apc;
use DAG\Services\Memcache;
use DAG\Services\MySql\DuplicateKeyException;

/**
 * Utilizes services (MySqlService, MemcacheService, etc) to persist and load models according to their configuration.
 */
class PersistenceDriver
{
    // Get-from-cache results

    const CACHE_RESULT_NEGATIVELY_CACHED = '__n'; // The cache indicates that the object does not exist
    const CACHE_RESULT_CACHE_MISS        = '__m'; // The object was not in cache
    const CACHE_RESULT_CANNOT_CACHE      = '__c'; // The search criteria was insufficient to generate any cache keys

    /** @var PersistenceConfig */
    protected $persistenceConfig;

    /** @var array[] */
    protected $fields;

    /**
     * Initializes driver for a specific persistence model.
     *
     * @param PersistenceConfig $config persistence configuration
     * @param array[]           $fields a key/value array: key is column name, value is ruleSet
     */
    public function __construct(PersistenceConfig $config, $fields)
    {
        $referencedFields = $config->getReferencedFields();
        Precondition::isTrue(!array_diff_key($referencedFields, $fields), 'Config references non-existent fields');

        $this->persistenceConfig = $config;
        $this->fields            = $fields;
    }

    /**
     * Creates (insert) an object.
     *
     * @param array $objectInArrayForm the data of the object to create
     *
     * @throws DuplicateEntryException if the object already exists
     * @return array the created object in array form
     */
    public function create($objectInArrayForm)
    {
        FV::multiValidateValues($objectInArrayForm, $this->fields);
        $data = FV::applyDefaults($objectInArrayForm, $this->fields);

        // Do persistence

        if ($this->persistenceConfig->getPersistenceDriver() == PC::DRIVER_MYSQL) {
            $mysqlDb = Services::getMySqlService()->getDatabase($this->persistenceConfig->getPersistenceSchema());
            try {
                $mysqlDb->insert($this->persistenceConfig->getPersistenceTable(), array($data));
            } catch (DuplicateKeyException $e) {
                throw new DuplicateEntryException(
                    $this->persistenceConfig->getPersistenceTable() . ' object already exists',
                    -1,
                    $e
                );
            }

            $autoIncField = $this->persistenceConfig->getAutoIncField();
            if ($autoIncField && !isset($data[$autoIncField])) {
                $data[$autoIncField] = $mysqlDb->getLastInsertId();
            }
        } else {
            Precondition::isTrue(
                false,
                'Unrecognized persistence driver: ' . $this->persistenceConfig->getPersistenceDriver()
            );
        }

        // Invalidate multi-cache key -- we are not going to recompute the multi-cache key at this point
        $this->invalidateCache($objectInArrayForm, true);

        // Cache new item
        $this->storeToCache($data, $data);

        return $data;
    }

    /**
     * Saves (update) changes to an existing object.
     *
     * A list of changed fields should be provided so that - when applicable to the persistence driver -
     *     only the changed fields need to be modified in the storage engine.
     *     This avoids race conditions where different processes are updating different fields of the same object.
     *
     * If $dirtyFields, no action is taken; neither the DB nor the cache will be updated.
     *
     * @param array $objectInArrayForm the data of the object to update
     * @param array $dirtyFields       array where the keys are the names of the fields that have changed,
     *                                 and the values are the currently-persisted values for each field
     *
     * @throws MissingEntryException if the object to be saved does not exist
     */
    public function save($objectInArrayForm, $dirtyFields)
    {
        if (!$dirtyFields) {
            return;
        }
        Precondition::isArray($dirtyFields, 'dirtyFields');

        // Do persistence

        $criteria = $this->getPrimaryKeyData($objectInArrayForm, $dirtyFields);

        $changesToApply = array_intersect_key($objectInArrayForm, $dirtyFields);

        if ($this->persistenceConfig->getPersistenceDriver() == PC::DRIVER_MYSQL) {
            $mysqlDb = Services::getMySqlService()->getDatabase($this->persistenceConfig->getPersistenceSchema());
            $mysqlDb->update($this->persistenceConfig->getPersistenceTable(), $changesToApply, $criteria);
            /*
            if (!$mysqlDb->countAffectedRows()) {
                throw new MissingEntryException(
                    $this->persistenceConfig->getPersistenceTable() . ' object not found for save : ' .
                    $mysqlDb->getLastRunQuery()
                );
            }
            */
        } else {
            Precondition::isTrue(
                false,
                'Unrecognized persistence driver: ' . $this->persistenceConfig->getPersistenceDriver()
            );
        }

        // Do caching

        $this->invalidateCache($objectInArrayForm, true);

        $this->storeToCache($objectInArrayForm, $objectInArrayForm);
    }

    /**
     * Batch save a set of objects, updating the multi-cache key if applicable.
     *
     * @param array $objectsInArrayForm array where each element is an object to be saved (in array form)
     * @param array $dirtyFieldsArray   array where each element is the dirtyFields for the corresponding object in arg1
     * @param array|null $batchContext  array describing the set being saved, or null if the objects do not form a
     *                                  complete set.
     *                                  For example, this would be array('accountId' => X) if the set being saved
     *                                      constituted the complete set of all objects for that account ID.
     *                                  If the objects being saved have nothing in common, then this parameter should be
     *                                      null.
     *                                  If the objects being saved here have something in common (for example, again
     *                                      use the accountId field), but do not constitute a complete set of objects
     *                                      for that accountId, then do not specify the accountId in the batchContext.
     */
    public function saveMulti($objectsInArrayForm, $dirtyFieldsArray, $batchContext = null)
    {
        // Note: this is a bad implementation because there is no batch update command for MySQL, etc.
        //       However, it performs well if only a few objects have been updated;
        //       objects in the multi array that have NOT changed are basically no-ops.
        //       (ie, such would be the case for account attributes.)
        //       This should be updated when performance becomes a concern.
        foreach ($objectsInArrayForm as $index => $objectInArrayForm) {
            $this->save($objectInArrayForm, $dirtyFieldsArray[$index]);
        }

        if ($batchContext) {
            $this->storeMultiToCache($batchContext, $objectsInArrayForm);
        }
    }

    /**
     * Deletes one object from persistence and invalidates the cache(s).
     *
     * @param array $objectInArrayForm the data of the object to delete; note: only the primary key fields are needed
     *
     * @throws MissingEntryException if the object to be deleted does not exist
     */
    public function deleteOne($objectInArrayForm)
    {
        // Determine primary key for the delete

        $criteria = $this->getPrimaryKeyData($objectInArrayForm);

        // Delete from persistence

        if ($this->persistenceConfig->getPersistenceDriver() == PC::DRIVER_MYSQL) {
            $mysqlDb = Services::getMySqlService()->getDatabase($this->persistenceConfig->getPersistenceSchema());
            $mysqlDb->delete($this->persistenceConfig->getPersistenceTable(), $criteria);
            if (!$mysqlDb->countAffectedRows()) {
                throw new MissingEntryException(
                    $this->persistenceConfig->getPersistenceTable() . ' object not found for delete'
                );
            }
        } else {
            Precondition::isTrue(
                false,
                'Unrecognized persistence driver: ' . $this->persistenceConfig->getPersistenceDriver()
            );
        }

        // Delete from cache
        $this->invalidateCache($objectInArrayForm);
    }

    /**
     * Gets objects that match the specified criteria.
     *
     * @param array $searchBy key/value array (field name -> search value)
     *
     * @return array[] matching objects in array form
     */
    public function getMany($searchBy)
    {
        // Try by cache

        $cacheResult = $this->getFromCache($searchBy);
        if (is_array($cacheResult)) {
            return $cacheResult;
        } elseif ($cacheResult == self::CACHE_RESULT_NEGATIVELY_CACHED) {
            return array();
        } else {
            Assertion::isTrue(
                $cacheResult == self::CACHE_RESULT_CACHE_MISS || $cacheResult == self::CACHE_RESULT_CANNOT_CACHE,
                'bad cache result'
            );
        }
        $couldCache = ($cacheResult == self::CACHE_RESULT_CACHE_MISS);

        // Try persistence driver

        if ($this->persistenceConfig->getPersistenceDriver() == PC::DRIVER_MYSQL) {
            // load $searchBy from mysql
            $mysqlDb = Services::getMySqlService()->getDatabase($this->persistenceConfig->getPersistenceSchema());
            $results = $mysqlDb->get($this->persistenceConfig->getPersistenceTable(), $searchBy);
        } else {
            Precondition::isTrue(
                false,
                'Unrecognized persistence driver: ' . $this->persistenceConfig->getPersistenceDriver()
            );
        }

        // Cache the result after cache miss

        if ($couldCache) {
            // Single cache
            // If we have multiple results, $searchBy will not be specific enough to generate any cache keys,
            //     so there is no reason to call this
            if (count($results) <= 1) {
                $this->storeToCache($searchBy, $results ? $results[0] : null);
            }

            // Multi-cache
            $this->storeMultiToCache($searchBy, $results);
        }

        return $results;
    }

    /**
     * Gets one object that matches the specified criteria. Typically the criteria should be a unique key.
     *
     * @param array $searchBy key/value array (field name -> search value)
     *
     * @throws NoResultsException if 0 results are found
     * @throws MultipleResultsException if multiple results are found
     * @return array object in array form; this is the first matching object in case multiple results are found
     */
    public function getOne($searchBy)
    {
        $results = $this->getMany($searchBy);

        if (!$results) {
            throw new NoResultsException('object not found: ' . json_encode($searchBy));
        } elseif (count($results) > 1) {
            throw new MultipleResultsException('multiple objects found: ' . json_encode($searchBy));
        }

        return $results[0];
    }

    /**
     * Gets objects that match the specified criteria similar to getMany(); allows custom WHERE / LIMIT / sort clauses.
     *
     * Differences from getMany():
     *     - This method requires that the persistence driver is MySQL because the sql fragment only applies there
     *     - No caches are read or updated at all, including multi-cache keys
     *
     * @param array  $searchBy         key/value array (field name -> search value);
     *                                     set to [] if $queryEndFragment includes a WHERE clause
     * @param string $queryEndFragment query fragment to put at end of query (ie. for LIMIT)
     *
     * @return array[] matching objects in array form
     */
    public function getManyFromCustomMySqlQuery($searchBy, $queryEndFragment)
    {
        Precondition::isTrue($this->persistenceConfig->getPersistenceDriver() == PC::DRIVER_MYSQL, 'needs MySQL driver');
        Precondition::isNonEmptyString($queryEndFragment, 'queryEndFragment');

        $mysqlDb = Services::getMySqlService()->getDatabase($this->persistenceConfig->getPersistenceSchema());
        $results = $mysqlDb->get($this->persistenceConfig->getPersistenceTable(), $searchBy, $queryEndFragment);
        $this->translateValuesFromMySql($results);

        return $results;
    }

    /**
     * When we load data from MySQL, all of the fields are strings.
     * But for columns we have designated as bool or int, we want the data type to be just that, rather than string.
     *
     * This function accepts an array of results and corrects its values per the column data types.
     *
     * @param array[] $results
     */
    protected function translateValuesFromMySql(& $results)
    {
        foreach ($this->fields as $fieldName => $fieldValue) {
            if ($fieldValue[FV::RULESET_INDEX_DATATYPE] == FV::INT) {
                foreach ($results as $resultNumber => $value) {
                    $results[$resultNumber][$fieldName] = (int) $value[$fieldName];
                }
            } elseif ($fieldValue[FV::RULESET_INDEX_DATATYPE] == FV::BOOL) {
                foreach ($results as $resultNumber => $value) {
                    $results[$resultNumber][$fieldName] = (bool) $value[$fieldName];
                }
            }
        }
    }

    /**
     * Gets at most one object from cache.
     *
     * @param array $searchBy key/value array (field name -> search value)
     *
     * @return array|string array of found objects (each in array form), or a CACHE_RESULT constant if none found
     */
    protected function getFromCache($searchBy)
    {
        $REGULAR_CACHE_KEY = 'KEY';
        $MULTI_CACHE_KEY   = 'MCC';

        $usesApc = $cacheWasChecked = false;
        
        $apcDriverKey    = null;
        $cacheDrivers    = $this->setupCacheDrivers();
        $cacheDriverKeys = $this->persistenceConfig->getCacheDriverKeys();

        // Loop over cache and alt-cache configs
        foreach ($cacheDriverKeys as $driverKey) {
            $cacheKeysMap = array(
                $REGULAR_CACHE_KEY => $this->persistenceConfig->getCacheKeys($driverKey,      $searchBy),
                $MULTI_CACHE_KEY   => $this->persistenceConfig->getMultiCacheKeys($driverKey, $searchBy, true),
            );

            // Loop over regular and multi-cache keys
            foreach ($cacheKeysMap as $cacheKeyType => $cacheKeys) {

                // Loop over each cache key
                foreach ($cacheKeys as $key) {
                    $cacheWasChecked = true;

                    $key = $this->getCacheNamespaceValue($this->persistenceConfig->getCacheNamespace($driverKey), $key);

                    if ($this->persistenceConfig->getCacheDriver($driverKey) == PC::DRIVER_APC) {
                        $apcDriverKey = $driverKey;
                        $usesApc = true;
                        $value = null;

                        if ($this->fetchFromCache($cacheDrivers, $driverKey, $key, $value)) {

                            if (is_array($value) && $cacheKeyType == $REGULAR_CACHE_KEY) {
                                // return type must be an array of results
                                return array($value);
                            }
                            return $value;
                        }

                    } elseif ($this->persistenceConfig->getCacheDriver($driverKey) == PC::DRIVER_MEMCACHE) {
                        $value = null;

                        if ($this->fetchFromCache($cacheDrivers, $driverKey, $key, $value)) {
                            if ($usesApc) {
                                $cacheDrivers[$apcDriverKey]->store(
                                    $key,
                                    $value,
                                    $this->persistenceConfig->getCacheTtl($driverKey)
                                );
                            }

                            if (is_array($value) && $cacheKeyType == $REGULAR_CACHE_KEY) {
                                // return type must be an array of results
                                return array($value);
                            }
                            return $value;
                        }
                    } else {
                        Assertion::isTrue(false, 'bad cache driver');
                    }
                }
            }
        }

        if ($cacheWasChecked) {
            return self::CACHE_RESULT_CACHE_MISS;
        } else {
            return self::CACHE_RESULT_CANNOT_CACHE;
        }
    }

    /**
     * Stores one object to cache per the cache configuration
     *
     * @param array      $cacheKeyData      data with which to generate the cache key(s)
     * @param array|null $objectInArrayForm objects to store in array form, or null
     */
    protected function storeToCache($cacheKeyData, $objectInArrayForm)
    {
        $cacheDrivers = $this->setupCacheDrivers();

        foreach ($this->persistenceConfig->getCacheDriverKeys() as $driverKey) {
            $cacheKeys = $this->persistenceConfig->getCacheKeys($driverKey, $cacheKeyData);

            foreach ($cacheKeys as $key) {
                $cacheDrivers[$driverKey]->store(
                    $this->getCacheNamespaceValue($this->persistenceConfig->getCacheNamespace($driverKey), $key),
                    $objectInArrayForm,
                    $this->persistenceConfig->getCacheTtl($driverKey)
                );
            }
        }
    }

    /**
     * Stores multiple objects to the multi-cache key per the cache configuration
     *
     * @param array      $cacheKeyData       data with which to generate the multi-cache key(s)
     * @param array|null $objectsInArrayForm objects to store in array form, or null
     */
    protected function storeMultiToCache($cacheKeyData, $objectsInArrayForm)
    {
        $cacheDrivers = $this->setupCacheDrivers();
        foreach ($this->persistenceConfig->getCacheDriverKeys() as $driverKey) {
            $cacheKeys = $this->persistenceConfig->getMultiCacheKeys($driverKey, $cacheKeyData, true);

            foreach ($cacheKeys as $key) {
                $cacheDrivers[$driverKey]->store(
                    $this->getCacheNamespaceValue($this->persistenceConfig->getCacheNamespace($driverKey), $key),
                    $objectsInArrayForm,
                    $this->persistenceConfig->getCacheTtl($driverKey)
                );
            }
        }
    }

    /**
     * Removes one object from cache per the cache configuration
     *
     * @param array $objectInArrayForm object to store; only the fields used to build cache keys are needed
     * @param bool  $onlyInvalidateMultiCacheKeys if true, invalidate MCC; if false invalidate MCC & regular keys
     */
    protected function invalidateCache($objectInArrayForm, $onlyInvalidateMultiCacheKeys = false)
    {
        $cacheDrivers = $this->setupCacheDrivers();
        foreach ($this->persistenceConfig->getCacheDriverKeys() as $driverKey) {
            $cacheKeys = $this->persistenceConfig->getMultiCacheKeys($driverKey, $objectInArrayForm, false);
            if (!$onlyInvalidateMultiCacheKeys) {
                $cacheKeys = array_merge(
                    $this->persistenceConfig->getCacheKeys($driverKey, $objectInArrayForm),
                    $cacheKeys
                );
            }

            foreach ($cacheKeys as $key) {
                $cacheDrivers[$driverKey]->delete(
                    $this->getCacheNamespaceValue($this->persistenceConfig->getCacheNamespace($driverKey), $key)
                );
            }
        }
    }

    /**
     * For a given object and its previousValues, return data for its primary keys (to find the object in DB).
     *
     * The object may be newer than what is persisted; in this case we rely on previousValues to generate the result.
     *
     * Example:
     *     Input: ['id' => 1, 'info' => 'ab'] (new data) + [] (previous values)
     *     Primary key: id
     *     Output: ['id' => 1]
     *
     * If the primary key(s) of the object have changed, we need to update based on the old primary keys.
     * Example:
     *     Input: ['id' => 5, 'info' => 'ab'] (new data) + ['id' => 1] (previous values)
     *     Primary key: id
     *     Output: ['id' => 1]
     *
     * @param array $objectInArrayForm object in question in key/value array form
     * @param array $previousValues    array where the keys are the names of the fields that have changed,
     *                                 and the values are the currently-persisted values for each field
     *
     * @return array where keys are the field(s) of the primary key,
     *               and the values are the values for the respective fields
     */
    protected function getPrimaryKeyData($objectInArrayForm, $previousValues = array())
    {
        $primaryKeys = array_flip($this->persistenceConfig->getPrimaryKeys());

        $criteria1 = array_intersect_key($objectInArrayForm, $primaryKeys);
        $criteria2 = array_intersect_key($previousValues, $primaryKeys);
        $criteria  = array_merge($criteria1, $criteria2);

        Assertion::isTrue(count($criteria) == count($primaryKeys), 'primary key data not provided');
        return $criteria;
    }

    /**
    * Retrieves the cache namespace value if it exists. It will create one if it doesn't. If a key is passed in, it will
    * return a key with the namespace value prepended to it.
    * 
    * @param string $namespaceKey   - key representing the cache namespace
    * @param string $key (optional) - key used to store a value
    * 
    * @return string
    */
    private function getCacheNamespaceValue($namespaceKey, $key = '')
    {
        // if there's no namespace key, just return the key passed in
        if (empty($namespaceKey)) {
            return $key;
        }
        
        // Check APC first
        $apcDriver = Services::getApcService()->getDriver();
        
        if (false === $apcDriver->fetch($namespaceKey, $salt)) {
            // Check Memcache since its not in APC
            $memcacheDriver  = Services::getMemcacheService()->getDriver();
            
            if (false === $memcacheDriver->fetch($namespaceKey, $salt)) {
                // namespace value does not exists so let's create it
                $salt = time();
                $memcacheDriver->store($namespaceKey, $salt, Memcache\Driver::EXPIRE_NEVER);
            }
            
            // add to APC since it wasn't there
            $apcDriver->store($namespaceKey, $salt, Apc\Driver::EXPIRE_10_SECONDS);
        }
        
        return (0 < strlen(trim($key))) ? "{$salt}_{$key}" : "{$salt}";
    }
    
    /**
     * fetches the value for a key from a specific driver
     * 
     * @param Memcache\Driver[]|Apc\Driver[] $cacheDrivers
     * @param string                         $driverKey    - driver key from PersistenceConfig->getCacheDriverKeys()
     * @param string                         $key          - cache key
     * @param mixed                          $value[out]   - value stored in the cache key
     * 
     * @return bool - true on success, else false.
     */
    private function fetchFromCache($cacheDrivers, $driverKey, $key, &$value)
    {
        $value  = null;
        $result = $cacheDrivers[$driverKey]->fetch($key, $value);
        
        if ($result) {
            if ($value === null) {
                $value = self::CACHE_RESULT_NEGATIVELY_CACHED;
            }
        }
        
        return $result;
    }

    /**
     * sets up the cache drivers that will be used
     *
     * @return Memcache\Driver[]|Apc\Driver[] $cacheDrivers
     */
    private function setupCacheDrivers()
    {
        $cacheDrivers = array();

        foreach ($this->persistenceConfig->getCacheDriverKeys() as $driverKey) {
            if  ($this->persistenceConfig->getCacheDriver($driverKey) == PC::DRIVER_MEMCACHE) {
                $cacheDrivers[$driverKey] = Services::getMemcacheService()->getDriver();
                
            } elseif ($this->persistenceConfig->getCacheDriver($driverKey) == PC::DRIVER_APC) {
                $cacheDrivers[$driverKey] = Services::getApcService()->getDriver();
                
            } else {
                Precondition::isTrue(false, 'unknown cache driver');
            }
        }
        
        return $cacheDrivers;
    }
}
