<?php
/**
 * object.php is the top most level object for the DAG hierarchy
 */

/**
 * DAG_Object is the generic object base class
 *
 * All class that requires setter/getters should inherit from this class
 */
abstract class DAG_Object
{
    /**
     * __construct will set the basic data for the object
     *
     * @return DAG_Object
     */
    public function __construct()
    {
    }

    /**
     * set will set the basic detail for the object
     *
     * @param mixed $key is the key to set
     * @param mixed $value is the value to set
     *
     * @return NULL
     */
    public function set($key, $value)
    {
        $this->$key = $value;
        return NULL;
    }

    /**
     * setProperties will set the basic properties for the object through an array
     *
     * @param array $properties is the details of the object as key => value pairing
     *
     * @return NULL
     */
    public function setProperties($properties)
    {
        if( empty($properties) ){
            return NULL;
        }

        // Set the properties passed
        foreach($properties as $key => $value){
            $this->$key = $value;
        }

        return NULL;
    }

    /**
     * get will get the basic details for the object
     *
     * @param string $key is the key to get from the object
     *
     * @return mixed the value of the key specified
     */
    public function get($key)
    {
        return $this->$key;
    }

    /**
     * getProperties will get the basic properties for the object through an array
     *
     * @param array $properties is the array for values to get
     * if $properties is empty function will return all properties that were set
     *
     * @return array with object data
     */
    public function getProperties($properties = NULL)
    {
        $values = array();

        // Return all properties if requested properties is empty
        if (empty($properties)) {
            $values = get_object_vars($this);
        }
        else {
            foreach($properties as $key) {
                $values[$key] = $this->$key;
            }
        }

        return $values;
    }

    /**
     * unsetProperty will will unset a m_objectData object of the class
     *
     * @param string $key is the key to unset from the object
     *
     * @return NULL
     */
    public function unsetProperty($key)
    {
        if( property_exists($this, $key) ){
            unset($this->$key);
        }

        return NULL;
    }

    /**
     * __set magic function (if called will throw an error)
     *
     * @param string $key   is the key of the variable to set
     * @param string $value is the value to set
     *
     * @throws DAG_ObjectKeyException
     * @return NULL
     */
    public function __set($key, $value)
    {
        if ($this instanceof SaveModelInterface) {
            $this->$key = $value;
        } else {
            throw new DAG_ObjectKeyException($key, 'set');
        }
    }

    /**
     * __get magic function will throw an error for the attempt that does not exist
     *
     * @param string $key is the key of the object variable to return
     *
     * return mixed with general details or NULL
     *
     * @throws DAG_ObjectKeyException
     */
    public function __get($key)
    {
        throw new DAG_ObjectKeyException($key, 'get');
    }
}

/**
 * DAG_ObjectKeyException is the exception class for the object keys
 */
class DAG_ObjectKeyException extends DAG_Exception {

    /**
     * __construct calls the parent to setup the exception
     *
     * @param string $key - the key that is invalid
     * @param string $operation - the operation that was performed
     */
    public function __construct($key, $operation) {
        parent::__construct("$operation on an unknown key: $key");
    }
}
?>
