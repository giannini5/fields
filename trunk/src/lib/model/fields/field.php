<?php
/**
 * This file contains Model_Fields_Field
 */

/**
 * Model_Fields_Field class
 */
class Model_Fields_Field extends Model_Fields_Base implements SaveModelInterface {

    public $m_facility;

    /**
     * @brief: Constructor
     *
     * @param $facility - Model_Fields_Facility instance
     * @param $id - unique identifier
     * @param $facilityId - unique facility identifier
     * @param string $name - name of the field
     * @param bool|int $enabled - 1 if field is enabled; 0 otherwise
     */
    public function __construct($facility = NULL, $id = NULL, $facilityId = NULL, $name = '', $enabled = 0) {
        parent::__construct('Model_Fields_FieldDB', Model_Base::AUTO_DECLARE_CLASS_VARIABLE_ON);

        $this->m_facility = $facility;
        $this->{Model_Fields_FieldDB::DB_COLUMN_ID}   = $id;
        $this->{Model_Fields_FieldDB::DB_COLUMN_FACILITY_ID}   = $facilityId;
        $this->{Model_Fields_FieldDB::DB_COLUMN_NAME} = $name;
        $this->{Model_Fields_FieldDB::DB_COLUMN_ENABLED} = $enabled;
        $this->_setFacility();
    }

    /**
     * @brief: destructor
     */
    public function __destruct() {
    }

    /**
     * @brief: _load will load the object from the data storage.
     *
     * @return bool - TRUE if successfully loaded model, else FALSE
     */
    public function _load() {
        /** @var Model_Fields_FieldDB $dbHandle */
        $dbHandle = $this->_getDBHandle();
        if (!is_null($this->{Model_Fields_FieldDB::DB_COLUMN_ID})) {
            $dataObj = $dbHandle->getById($this->{Model_Fields_FieldDB::DB_COLUMN_ID});

        } else if (!is_null($this->{Model_Fields_FieldDB::DB_COLUMN_NAME})) {
            $dataObj = $dbHandle->getByName(
                NULL,
                $this->{Model_Fields_FieldDB::DB_COLUMN_NAME},
                TRUE,
                $this->{Model_Fields_FieldDB::DB_COLUMN_FACILITY_ID});
        }

        if (!empty($dataObj)) {
            $this->assignModel($dataObj);
            $this->_setFacility();
            return TRUE;
        }

        return FALSE;
    }

    /**
     * @brief: Set the facility member variable if not already set
     */
    private function _setFacility() {
        if (!isset($this->m_facility)) {
            $this->m_facility = Model_Fields_Facility::LookupById($this->{Model_Fields_FieldDB::DB_COLUMN_FACILITY_ID});
        }
    }

    /**
     * _getUpdateKeys will return the update keys
     *
     * @return array primaryKeys K => V
     */
    public function _getUpdateKeys() {
        return array(Model_Fields_FieldDB::DB_COLUMN_ID => $this->{Model_Fields_FieldDB::DB_COLUMN_ID});
    }

    /**
     * @brief: Get and instance of this object from databaes data.
     *
     * @param $dataObject - data object representing the content of the object
     * @param $facility - Model_Fields_Facility instance
     *
     * @return Model_Fields_Field
     */
    public static function GetInstance($dataObject, $facility = NULL) {
        $field = new Model_Fields_Field(
            $facility,
            $dataObject->{Model_Fields_FieldDB::DB_COLUMN_ID},
            $dataObject->{Model_Fields_FieldDB::DB_COLUMN_FACILITY_ID},
            $dataObject->{Model_Fields_FieldDB::DB_COLUMN_NAME},
            $dataObject->{Model_Fields_FieldDB::DB_COLUMN_ENABLED});

        $field->setLoaded();

        return $field;
    }

    /**
     * @brief: Create a new Field
     *
     * @param $facility - Model_Fields_Facility instance
     * @param string $name - name of the field
     * @param bool $enabled - 1 if field is enabled; 0 otherwise
     *
     * @return Model_Fields_Field
     * @throws AssertionException
     */
    public static function Create($facility, $name, $enabled) {
        $dbHandle = new Model_Fields_FieldDB();
        $dataObject = $dbHandle->create($facility, $name, $enabled);
        assertion(!empty($dataObject), "Unable to create Field with name:'$name'");

        return Model_Fields_Field::GetInstance($dataObject, $facility);
    }

    /**
     * @brief: Get Model_Fields_Field instance for the specified Field identifier
     *
     * @param bigint $fieldId: Unique Field identifier
     *
     * @return Model_Fields_Field
     */
    public static function LookupById($fieldId) {
        $dbHandle = new Model_Fields_FieldDB();
        $dataObject = $dbHandle->getById($fieldId);
        assertion(!empty($dataObject), "Field row for id: '$fieldId' not found");

        return Model_Fields_Field::GetInstance($dataObject);
    }

    /**
     * @brief: Get Model_Fields_Field instance for the specified Field facility and name
     *
     * @param $facility - Model_Fields_Facility instance
     * @param $name - Field's name
     * @param $assertIfNotFound - If TRUE then assert object if found.  Otherwise return NULL when object not found
     *
     * @return Model_Fields_Field or NULL if object not found and $assertIfNotFound is FALSE
     * @throws AssertionException
     */
    public static function LookupByName($facility, $name, $assertIfNotFound = TRUE, $facilityId = NULL) {
        $dbHandle = new Model_Fields_FieldDB();
        $dataObject = $dbHandle->getByName($facility, $name, $facilityId);

        if ($assertIfNotFound) {
            assertion(!empty($dataObject), "Field row for name: $name not found");
        } else if (empty($dataObject)) {
            return NULL;
        }

        return Model_Fields_Field::GetInstance($dataObject, $facility);
    }

    /**
     * @brief: Delete if exists
     *
     * @param $facility - Model_Fields_Facility instance
     * @param $name - Field's name
     */
    public static function Delete($facility, $name) {
        $field = Model_Fields_Field::LookupByName($facility, $name, FALSE);
        if (isset($field)) {
            $field->_delete();
        }
    }
}