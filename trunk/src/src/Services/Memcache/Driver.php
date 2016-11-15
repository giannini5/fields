<?php

namespace DAG\Services\Memcache;

use DAG\Framework\Exception\Precondition;
use DAG\Framework\Services;
use DAG\Services\Interfaces\Cache;
use DAG\Services\Dns\DnsCaching;

/**
 * This class comprises of the Memcache Driver
 */
class Driver implements Cache
{
    // Expiry Times
    const EXPIRE_1_SECONDS  = 1;
    const EXPIRE_5_SECONDS  = 5;
    const EXPIRE_10_SECONDS = 10;
    const EXPIRE_15_SECONDS = 15;
    const EXPIRE_30_SECONDS = 30;
    const EXPIRE_MINUTE     = 60;
    const EXPIRE_30_MINUTES = 1800;
    const EXPIRE_HOUR       = 3600;
    const EXPIRE_DAY        = 86400;
    const EXPIRE_WEEK       = 604800;
    const EXPIRE_MONTH      = 2592000;
    const EXPIRE_NEVER      = 0;
    
    // type of supported memcache farms
    const TYPE_DEFAULT  = MEMCACHE_DEFAULT;
    const TYPE_ADSERVER = MEMCACHE_ADSERVER;
    
    /** @var \MemcachePool */
    private $cache = null;
    
    /** @var array server parameters */
    private $serverInfo = null;
    
    /**
     * @param string type of memcache servers
     * 
     * @throws ClientException
     */
    public function __construct($type)
    {
        try {
            $this->connect($type);

        } catch (\DAGException $e) {
            throw new ClientException($e);
        }

        $this->cache->setFailureCallback(array('DAG\Services\Memcache\Driver', 'errorCallback'));
    }

    /**
     * Connects to the first Memcache server from the pool
     *
     * @throws ConnectionException
     * @throws ConfigurationException
     */
    private function connect($type)
    {
        global $g_MEMCACHED_SERVER_FARMS;
        $serverIndex = MEMCACHE_DEFAULT;

        $this->cache = new \MemcachePool();
        
        if (!array_key_exists($type, $g_MEMCACHED_SERVER_FARMS) || !isset($g_MEMCACHED_SERVER_FARMS[$type][0])) {
            throw new ConfigurationException($type, $g_MEMCACHED_SERVER_FARMS);
        }

        $this->serverInfo = $g_MEMCACHED_SERVER_FARMS[$type][0];
        $hostName         = DnsCaching::getByHostname($this->serverInfo['host']);
        
        if (!$this->cache->addServer(
            $hostName,
            $this->serverInfo['tcp_port'],
            $this->serverInfo['udp_port'],
            $this->serverInfo['persistent'],
            $this->serverInfo['weight'],
            $this->serverInfo['timeout'],
            $this->serverInfo['retry_interval'])) {

            throw new ConnectionException($hostName, $this->serverInfo['tcp_port'], $this->serverInfo['udp_port']);
        }
    }
    
    /**
     * Sets key(S) with the given value
     * 
     * @example single key : store('key', 'value', 10)
     * @example multi  key : store(array('key1' => 'value1', 'key1' => 'value1'), null, 10)
     * 
     * @param string|string[] $key    - the key to store with
     * @param mixed           $value  - the value to store in cache
     * @param int             $expire - the expiry time to set, default: EXPIRE_NEVER
     *
     * @return bool - true if success, else false
     */
    public function store($key, $value, $expire = self::EXPIRE_NEVER)
    {
        return $this->cache->set($key, $value, 0, $expire);
    }

    /**
     * Checks if cache key exists
     * 
     * @param string|string[] $key - name of key, or array of keys to store.
     * 
     * @return bool|bool[] - Returns true if the key exists, otherwise false Or if an array of keys is passed, then an array is 
     *                       returned that contains all existing keys, or an empty array if none exist.
     */
    public function exists($key)
    {
        return $this->fetch($key, $value);
    }
    
    /**
     * Adds new key(s) to the cache, only if it's not already stored.
     *
     * @example single key : add('key', 'value', 10)
     * @example multi  key : add(array('key1' => 'value1', 'key1' => 'value1'), null, 10)
     * 
     * @param string|string[] $key    - the key to store with
     * @param mixed           $value  - the value to store in cache
     * @param int             $expire - the expiry time to set, default: EXPIRE_NEVER
     *
     * @return bool - true if success, else false
     */
    public function add($key, $value, $expire = self::EXPIRE_NEVER)
    {
        return $this->cache->add($key, $value, 0, $expire);
    }
    /**
     * Updates an old integer value with a new integer value.
     * 
     * @param string $key         - name of key the value will be updated under
     * @param int    $oldIntValue - current integer value stored in key
     * @param int    $newIntValue - new integer value to update to
     * @param int             $expire - the expiry time to set, default: EXPIRE_NEVER
     * 
     * @return bool - Returns true on success or false on failure.
     */
    public function cas($key, $oldIntValue, $newIntValue, $expire = self::EXPIRE_NEVER)
    {
        if ($this->fetch($key, $fetchIntValue)) {
            if ($oldIntValue === $fetchIntValue) {
                $this->store($key, $newIntValue, $expire);
                
                return true;
            }
        }
        return false;
    }
    
    /**
     * Gets a value from cache
     *
     * @param string $key        - the key to get
     * @param mixed  $value[out] - value stored in key on success
     *
     * @return bool - true if success, else false
     */
    public function fetch($key, &$value)
    {
        $casToken = 0;
        $flags    = null;
        
        $value = $this->cache->get($key, $flags, $casToken);
        
        return (false === $value) ? false : true;
    }

    /**
     * increments key. if key is not found then add key and set value to the incrementBy. Passing a negative number will 
     * result in decrementing the number.
     * 
     * @param string $key         - Key of the item to increment
     * @param int    $incrementBy - number to increment by
     * @param int    $expire      - the expiry time to set, default: EXPIRE_NEVER
     *
     * @return int - new items value on success.
     * 
     * @throws IncrementTypeException
     */
    public function increment($key, $incrementBy = 1, $expire = self::EXPIRE_NEVER)
    {
        $value = @$this->cache->increment($key, $incrementBy);
        
        if (false === $value) {
            if (false === $this->fetch($key, $getValue)) {
                $this->store($key, $incrementBy, $expire);
                $value = $incrementBy;
                
            } else {
                throw new IncrementTypeException($getValue);
            }
        }
        
        return $value;
    }

    /**
     * delete a value from cache
     *
     * @param string $key - the key to delete
     * 
     * @return bool - true on success or false on failure.
     */
    public function delete($key)
    {
        return $this->cache->delete($key);
    }

    /**
     * Called when an error is encountered during a memcache operation
     *
     * @param string $host     - name of server host
     * @param int    $tcp_port - port number for tcp protocol
     * @param int    $udp_port - port number for upd protocol
     * @param string $error    - description of error
     * @param int    $errnum   - id of error
     *
     * @return bool
     */
    public static function errorCallback($host, $tcp_port, $udp_port, $error, $errnum) {
        return false;
    }
}
