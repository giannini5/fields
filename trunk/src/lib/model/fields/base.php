<?php
/**
 * This file contains Model_Fields_Base
 */

/**
 * Model_Fields_Base class is the 'base' model class for the Fields models
 */
abstract class Model_Fields_Base extends Model_Base {

    protected $dbHandle = NULL;
    protected $dbClass = NULL;

    /**
     * @brief: Construct object
     *
     * @param bool $databaseClass - Supporting database class name
     * @param bool $autoDeclarationbOfClassVariables
     */
    public function __construct($databaseClass, $autoDeclarationbOfClassVariables = self::AUTO_DECLARE_CLASS_VARIABLE_OFF) {
        $this->dbClass = $databaseClass;
        parent::__construct($autoDeclarationbOfClassVariables);
    }

    /**
     * _getDBHandle will return the database handle used by this class
     *
     * @return DB class
     */
    public function _getDBHandle() {
        if (is_null($this->dbHandle)) {
            if (0 < strlen(trim($this->dbClass))) {
                $this->dbHandle = new $this->dbClass();
            }
        }

        return $this->dbHandle;
    }

    /**
     * _delete will delete this object from the data storage.
     */
    public function _delete() {
        $dbHandle = $this->_getDBHandle();
        return $dbHandle->delete($this->_getUpdateKeys());
    }

    /**
     * _getSaveAttributes returns an array of attributes for saving into the database
     *
     * @return array of attributes to persist to the database
     */
    public function _getSaveAttributes() {
        $dbHandle = $this->_getDBHandle();
        return array_values($dbHandle->getDatabaseColumns());
    }

    /**
     * _cacheWakeup will do stuff after cache load finishes. Like discarding invalid db handle
     *
     */
    public function _cacheWakeup() {
        $this->dbHandle = NULL;
    }

    /**
     * setIsLoaded sets the isLoaded flag. Used when creating models from db results.
     *
     * @param mixed $isLoaded
     */
    public function setIsLoaded($isLoaded = TRUE) {
        $this->isLoaded = $isLoaded;
    }
}
?>
