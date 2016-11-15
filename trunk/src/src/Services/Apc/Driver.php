<?php

namespace DAG\Services\Apc;

use DAG\Services\Interfaces\Cache;
use DAG\Framework\Exception\Precondition;

/**
 * This class comprises of the APC Driver
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
    const EXPIRE_HOUR       = 3600;
    const MAXIMUM_EXPIRE    = 3600;

    // type of sapi procees we are running as
    const TYPE_APACHE = 1;
    const TYPE_QUEUE  = 2;

    // names for the queue value elements
    const QUEUE_ELEMENT_VALUE   = 'value';
    const QUEUE_ELEMENT_EXPIRES = 'expires';

    /** @var int $sapiType - whether we are runing as an apache process or a queue process */
    private $sapiType = null;

    /**
     * 
     * @param int type of sapi process
     */
    public function __construct($type = null) {
        if (is_null($type)) {
            $this->sapiType = (0 === strcasecmp('apache', substr(php_sapi_name(), 0, 6))) ?
                self::TYPE_APACHE :
                self::TYPE_QUEUE;
                
        } else {
            $this->sapiType = $type;
        }
    }

    /**
     * Cache a new variable in the data store. If the key already exists, the value will NOT be overwritten.
     * 
     * @param string|string[] $key    - name of key the value will be stored under, or array of key-values to store.
     * @param mixed           $value  - the value to be stored
     * @param int             $expiry - time to live in seconds
     * 
     * @return bool - Returns true if something has effectively been added into the cache, false otherwise. 
     *                Second syntax returns array with error keys.
     */
    public function add($key, $value, $expiry = self::EXPIRE_1_SECONDS)
    {
        $expiry = (self::MAXIMUM_EXPIRE < $expiry) ? self::MAXIMUM_EXPIRE : $expiry;
        
        return (is_array($key)) ?
            apc_add($this->addExpiryToMultiIfNeeded($key, $expiry), null, $expiry) :
            apc_add($key, $this->addExpiryIfNeeded($value, $expiry), $expiry);
    }
    
    /**
     * Updates an old integer value with a new integer value.
     * 
     * @param string $key         - name of key the value will be updated under
     * @param int    $oldIntValue - current integer value stored in key
     * @param int    $newIntValue - new integer value to update to
     * 
     * @return bool - Returns true on success or false on failure.
     */
    public function cas($key, $oldIntValue, $newIntValue, $expiry = self::EXPIRE_1_SECONDS)
    {
        if ($this->fetch($key, $fetchIntValue)) {
            if ($oldIntValue === $fetchIntValue) {
                $this->store($key, $newIntValue, $expiry);
                
                return true;
            }
        }
        return false;
    }
    
    /**
     * Removes a stored variable from the cache
     * 
     * @param string $key - name of key to be deleted from cache
     * 
     * @return bool - true on success or false on failure.
     */
    public function delete($key)
    {
        return apc_delete($key);
    }
    
    /**
     * Checks if APC key exists
     * 
     * @param string|string[] $key - name of key, or array of keys to store.
     * 
     * @return bool|bool[] - Returns true if the key exists, otherwise false Or if an array of keys is passed, then an array is 
     *                       returned that contains all existing keys, or an empty array if none exist.
     */
    public function exists($key)
    {
        return apc_exists($key);
    }
    
    /**
     * fetch a stored variable from the cache
     * 
     * @param string|string[] $key        - name of key the value will be retrieved from, or array of key-values to 
     *                                      retrieve from.
     * @param mixed|mixed[]   $value[out] - values associated with key(s).
     * 
     * @return bool|bool[] - true on success, else false.
     */
    public function fetch($key, &$value)
    {
        $value = apc_fetch($key, $isSuccess);
        
        if (is_array($key)) {
            $isSuccess = $this->checkValuesIfExpired($value);
            
        } else {
            $isSuccess = $this->checkValueIfExpired($value);
        }
        
        return $isSuccess;
    }
    
    /**
     * increments key. if key is not found then add key and set value to the incrementBy. Passing a negative number will 
     * result in decrementing the number.
     * 
     * @param string $key         - The key of the value being increased.
     * @param int    $incrementBy - The step, or value to increase. (negative number will decrease)
     * @param int    $expire      - time to live in seconds
     * 
     * @return int - Returns the current value of key's value on success
     */
    public function increment($key, $incrementBy, $expire = self::EXPIRE_1_SECONDS)
    {
        return (self::TYPE_QUEUE === $this->sapiType) ?
            $this->incForQueueProcess($key, $incrementBy, $expire)  :
            $this->incForApacheProcess($key, $incrementBy, $expire);
    }
    
    /**
     * Cache a variable in the data store
     * 
     * @param string|string[] $key    - name of key the value will be stored under, or array of key-values to store.
     * @param mixed           $value  - the value to be stored
     * @param int             $expiry - time to live in seconds
     * 
     * @return bool|bool[] - Returns true on success or false on failure. Second syntax returns array with error keys.
     */
    public function store($key, $value, $expiry = self::EXPIRE_1_SECONDS)
    {
        $expiry = (self::MAXIMUM_EXPIRE < $expiry) ? self::MAXIMUM_EXPIRE : $expiry;

        return (is_array($key)) ?
            apc_store($this->addExpiryToMultiIfNeeded($key, $expiry), null, $expiry) :
            apc_store($key, $this->addExpiryIfNeeded($value, $expiry), $expiry);
    }
    
    /**
     * Creates an array of stdclass object consisting on the original value along with an expiry time, key by the keys
     * 
     * @param mixed[] $values - associative array of keys and values
     * @param int     $expiry - time to live in seconds
     * 
     * @return mixed[]
     */
    private function addExpiryToMultiIfNeeded($values, $expiry = self::EXPIRE_1_SECONDS)
    {
        foreach ($values as $key => $value) {
            $values[$key] = $this->addExpiryIfNeeded($value, $expiry);
        }
        
        return $values;
    }
    
    /**
     * Creates a stdclass object consisting on the original value along with an expiry time
     * 
     * @param mixed $value  - mixed value to store in APC
     * @param int   $expiry - time to live in seconds. Default is EXPIRE_1_SECONDS
     * 
     * @return mixed
     */
    private function addExpiryIfNeeded($value, $expiry = self::EXPIRE_1_SECONDS)
    {
        if (self::TYPE_QUEUE === $this->sapiType) {
            $queueValue = new \stdClass();
            $queueValue->{self::QUEUE_ELEMENT_VALUE}   = $value;
            $queueValue->{self::QUEUE_ELEMENT_EXPIRES} = time() + $expiry;
            
            $value = @serialize($queueValue);
        }
        
        return $value;
    }
    
    /**
     * Checks the expiry time on an array of values to make sure value isn't stale.
     * 
     * @param mixed[] $values[out] - value stored in the keys
     * 
     * @return bool[] - true if not expired for key, else false
     */
    private function checkValuesIfExpired(&$values)
    {
        foreach ($values as $key => $value) {
            $isSuccess[$key] = $this->checkValueIfExpired($value);
            $values[$key]    = $value;
        }
        
        return $isSuccess;
    }
    
    /**
    * Checks the expiry time to make sure value isn't stale.
    * 
    * @param mixed $value[out] - value stored for a key
    * 
    * @return bool - true if not expired, else false
    */
    private function checkValueIfExpired(&$value)
    {
        if (self::TYPE_QUEUE === $this->sapiType) {
            $queueValue = @unserialize($value);
            
            if (!is_bool($queueValue) && (isset($queueValue->{self::QUEUE_ELEMENT_VALUE}))) {
                $value = ($queueValue->{self::QUEUE_ELEMENT_EXPIRES} < time()) ?
                    false :
                    $queueValue->{self::QUEUE_ELEMENT_VALUE};
                    
            } else {
                $value = false;
            }
        }

        return (false === $value) ? false : true;
    }
    
    /**
     * Increase a stored number by X. Passing a negative number will result in decrementing the number.
     * 
     * @param string $key         - The key of the value being increased.
     * @param int    $incrementBy - The step, or value to increase. (negative number will decrease)
     * @param int    $expire      - time to live in seconds
     * 
     * @return int - Returns the current value of key's value on success
     * 
     * @throws IncrementTypeException
     */
    private function incForApacheProcess($key, $incrementBy, $expire)
    {
        $value = apc_inc($key, $incrementBy, $isSuccess);
        
        if (false == $isSuccess) {
            if (false === apc_fetch($key, $fetchValue)) {
                $this->store($key, $incrementBy, $expire);
                $value = $incrementBy;
            
            } else {
                throw new IncrementTypeException($fetchValue);
            }
        }
        
        return $value;
    }
    
    /**
     * Increase a stored number by X. Passing a negative number will result in decrementing the number.
     * 
     * @param string $key         - The key of the value being increased.
     * @param int    $incrementBy - The step, or value to increase. (negative number will decrease)
     * @param int    $expire      - time to live in seconds
     * 
     * @return int - Returns the current value of key's value on success
     */
    private function incForQueueProcess($key, $incrementBy, $expire)
    {
        $isSuccess = $this->fetch($key, $fetchValue);
        
        if (true === $isSuccess) {
            if (is_int($fetchValue)) {
                $fetchValue += $incrementBy;
                $this->store($key, $fetchValue, $expire);
            
            } else {
                throw new IncrementTypeException($fetchValue);
            }

        } else {
            $this->store($key, $incrementBy, $expire);
            $fetchValue = $incrementBy;
        }
        
        return $fetchValue;
    }
}
