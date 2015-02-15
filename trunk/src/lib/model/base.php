<?php
/**
 * Model_Base is the generic model object class.
 * All model classes should inherit this from class
 */
require_once SRC_LIB . 'common/object.php';
require_once SRC_LIB . 'model/common.php';

/**
 * Model_Base class is the abstract base class for all the models
 */
abstract class Model_Base extends DAG_Object
{
    // Save operations
    const SAVE_INSERT = 0;
    const SAVE_UPDATE = 1;
    const SAVE_INSERT_OR_UPDATE = 2;
    const SAVE_DELETE = 3;

    // Options to auto declare class variables based on db table column names
    const AUTO_DECLARE_CLASS_VARIABLE_ON = TRUE;
    const AUTO_DECLARE_CLASS_VARIABLE_OFF = FALSE;

    const USE_INSERT_IGNORE = TRUE;

    /**
     * isLoaded - flag for if the object is in a loaded state
     *
     * @access protected
     * @var bool
     */
    protected $isLoaded;

    /**
     * saveableAttributes - array of DB columns
     *
     * @access protected
     * @var array
     */
    protected $saveableAttributes;

    /**
     * isModified - flag for if the object is modified
     *
     * @access protected
     * @var bool
     */
    protected $isModified;

    /**
     * __construct setup the model object
     *
     * @param bool $autoDeclarationbOfClassVariables - flag to auto generate class variables
     */
    public function __construct($autoDeclarationbOfClassVariables = self::AUTO_DECLARE_CLASS_VARIABLE_OFF) {
        $this->isLoaded  = FALSE;

        if ($this->isSaveModel() && self::AUTO_DECLARE_CLASS_VARIABLE_ON === $autoDeclarationbOfClassVariables) {
            $dbHandle = $this->_getDBHandle();

            $dbAttributes = $dbHandle->getDatabaseColumns();

            $saveableAttributes = array();

            foreach ($dbAttributes as $classAttribute) {
                $this->set($classAttribute, NULL);
                $saveableAttributes[] = $classAttribute;
            }

            $this->set('saveableAttributes', $saveableAttributes);
        }

        parent::__construct();
    }

    /**
     * @brief: loadModel loads the model
     *
     * @return bool TRUE or FALSE for success
     */
    public function loadModel() {
        $this->isLoaded = $this->_load();
        return $this->isLoaded;
    }

    /**
     * Save the current object model. No-op if SaveModelInterface is not implemented
     *
     * @param BOOL $useInsertIgnore (FALSE[default], TRUE)
     *
     * @return bool
     */
    public function saveModel($useInsertIgnore = FALSE) {

        if ($this->isSaveModel()) {
            // @var $dbHandleDB DAG_Database
            $dbHandleDB = $this->_getDBHandle();
            $dataObject = new DataObject();
            $dataObject->setProperties($this->getProperties($this->_getSaveAttributes()));

            // Database saving
            if (!$this->isLoaded) {
                $lastId = ($useInsertIgnore) ? $dbHandleDB->insertIgnore($dataObject) : $dbHandleDB->insert($dataObject);
            } else {
                $dbHandleDB->insertOrUpdate($dataObject);
            }
        }

        return TRUE;
    }

    /**
     * @brief: deleteModel will delete the model from the db
     *         Model deleted from database if SaveModelInterface implemented
     */
    public function deleteModel() {
        if ($this->isSaveModel()) {
            $this->_delete();
        }
    }

    /**
     * assignModel will assign the object properties to the current object
     *
     * @param DAG_Object $model is the object to set the properties from
     *
     * @return bool TRUE
     */
    public function assignModel($model) {
        precondition(!empty($model), "Attempting to assign an empty model");

        $dataObjectProperties = $model->getProperties();
        foreach ($dataObjectProperties as $key => $property) {
            $key = lcfirst($key);
            if (property_exists($this, $key)) {
                $this->set($key, $property);
            }
        }

        return TRUE;
    }

    /**
     * isSaveModel returns if the model implements the saveable interface
     *
     * @return bool TRUE or FALSE if the model is saveable
     */
    public function isSaveModel() {
        return ($this instanceof SaveModelInterface);
    }

    /**
     * isLoaded returns if the object has been loaded
     *
     * @return bool - TRUE or FALSE if the object is in a loaded state
     */
    public function isLoaded() {
        return $this->isLoaded;
    }

    /**
     * isModified returns if the object has been modified
     *
     * @return bool TRUE or FALSE if the object is in a modified state
     */
    public function isModified() {
        return $this->isModified;
    }

    /**
     * setModified marks the object as modified
     *
     * @param bool $modified - flag to set for modified
     */
    public function setModified($modified = TRUE) {
        $this->isModified = $modified;
    }

    /**
     * setLoaded marks the object as loaded
     */
    public function setLoaded() {
        $this->isLoaded = TRUE;
    }

    /**
     * _load Load this instance with DB information
     *
     * @return bool
     */
    abstract public function _load();
}
