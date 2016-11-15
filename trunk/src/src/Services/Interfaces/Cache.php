<?php
  
namespace DAG\Services\Interfaces;

interface Cache
{
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
    public function add($key, $value, $expiry);

    /**
     * Compare and swap (cas)- compares the current integer value with what's passed in and swaps with new integer value
     * if the current integer value is the same as the passed in value
     * 
     * @param string $key         - name of key the value will be updated under
     * @param int    $oldIntValue - current integer value stored in key
     * @param int    $newIntValue - new integer value to update to
     * 
     * @return bool - Returns true on success or false on failure.
     */
    public function cas($key, $oldIntValue, $newIntValue, $expiry);

    /**
     * Removes a stored variable from the cache
     * 
     * @param string $key - name of key to be deleted from cache
     * 
     * @return bool - true on success or false on failure.
     */
    public function delete($key);
    
    /**
     * Checks if cache key exists
     * 
     * @param string|string[] $key - name of key, or array of keys to store.
     * 
     * @return bool|bool[] - Returns true if the key exists, otherwise false Or if an array of keys is passed, then an array is 
     *                       returned that contains all existing keys, or an empty array if none exist.
     */
    public function exists($key);
    
    /**
     * fetch a stored variable from the cache
     * 
     * @param string|string[] $key        - name of key the value will be retrieved from, or array of key-values to 
     *                                      retrieve from.
     * @param mixed|mixed[]   $value[out] - values associated with key(s).
     * 
     * @return bool|bool[] - true on success, else false.
     */
    public function fetch($key, &$value);
    
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
    public function increment($key, $incrementBy, $expiry);
    
    /**
     * Cache a variable in the data store
     * 
     * @param string|string[] $key    - name of key the value will be stored under, or array of key-values to store.
     * @param mixed           $value  - the value to be stored
     * @param int             $expiry - time to live in seconds
     * 
     * @return bool|bool[] - Returns true on success or false on failure. Second syntax returns array with error keys.
     */
    public function store($key, $value, $expiry);
}
