<?php
/**
 * This file has generic data object
 */

/**
 * Data_Object is the generic object for the data.
 */
#[AllowDynamicProperties]
class DataObject extends DAG_Object
{
    /**
     * __set magic function (if called will throw an error)
     *
     * @param string $key is the key of the variable to set
     * @param string $value is the value to set
     *
     * @return NULL
     */
    public function __set($key, $value)
    {
        $this->$key = $value;
        return NULL;
    }

    /**
     * __get magic function when called will check the objectData array to return
     *   the key's value or NULL if key's value not set.  False is returned if the
     *   key is not found.
     *
     * @param string $key is the key of the object variable to return
     *
     * @return bool|void
     */
    public function __get($key)
    {
        if (property_exists($this, $key)) {
            return $this->$key;
        }

        return FALSE;
    }
}
?>
