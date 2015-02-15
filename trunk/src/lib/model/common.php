<?php
/**
 * Conatins classes and interface common to both model object class.
 * All model classes should inherit this from class
 */

/**
 * SaveModelInterface interface for the models that require to be saved
 */
interface SaveModelInterface {
    // interface _getSaveAttributes function gets the save attributes
    public function _getSaveAttributes();

    // interface _getDBHandle function gets the db handle
    public function _getDBHandle();
    
    // interface _getUpdateKeys function gets the update keys
    public function _getUpdateKeys();

    // interface _delete function performs a delete
    public function _delete();
}

/**
 * @brief: Model_UpdateKeyException is thrown when an update is attempted with the correct delete key
 */
class Model_UpdateKeyException extends DAG_Exception {
    /**
     * @brief: Constructor
     *
     * @param: string $key is the update key the exception was triggered for
     */
    public function __construct($key){
        parent::__construct("Illegal update operation without $key");
    }
}

/**
 * @brief: Model_DeleteKeyException is thrown when a delete is attempted with the correct delete key
 */
class Model_DeleteKeyException extends DAG_Exception {

    /**
     * @brief: Constructor
     *
     * @param: string $key is the delete key the exception was triggered for
     */
    public function __construct($key){
        parent::__construct("Illegal delete operation without $key");
    }
}
?>
