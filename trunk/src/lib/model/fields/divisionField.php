<?php
/**
 * This file contains Model_Fields_DivisionField
 */

/**
 * Model_Fields_DivisionField class
 */
class Model_Fields_DivisionField extends Model_Fields_Base implements SaveModelInterface {

    /**
     * @brief: Constructor
     *
     * @param int $id - unique identifier
     * @param int $divisionId - Division identifier
     * @param int $facilityId - Facility identifier
     * @param int $fieldId - Field identifier
     *
     * @return Model_Fields_DivisionField
     */
    public function __construct($id = NULL, $divisionId = NULL, $facilityId = NULL, $fieldId = NULL) {
        parent::__construct('Model_Fields_DivisionFieldDB', Model_Base::AUTO_DECLARE_CLASS_VARIABLE_ON);

        $this->{Model_Fields_DivisionFieldDB::DB_COLUMN_ID}   = $id;
        $this->{Model_Fields_DivisionFieldDB::DB_COLUMN_DIVISION_ID} = $divisionId;
        $this->{Model_Fields_DivisionFieldDB::DB_COLUMN_FACILITY_ID} = $facilityId;
        $this->{Model_Fields_DivisionFieldDB::DB_COLUMN_FIELD_ID} = $fieldId;
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
        /** @var Model_Fields_DivisionFieldDB $dbHandle */
        $dbHandle = $this->_getDBHandle();
        $dataObj = $dbHandle->getById($this->{Model_Fields_DivisionFieldDB::DB_COLUMN_ID});

        if (!empty($dataObj)) {
            $this->assignModel($dataObj);
            return TRUE;
        }

        return FALSE;
    }

    /**
     * _getUpdateKeys will return the update keys
     *
     * @return array primaryKeys K => V
     */
    public function _getUpdateKeys() {
        return array(Model_Fields_DivisionFieldDB::DB_COLUMN_ID => $this->{Model_Fields_DivisionFieldDB::DB_COLUMN_ID});
    }

    /**
     * @brief: Get and instance of this object from databaes data.
     *
     * @param DataObject $dataObject - data object representing the content of the object
     *
     * @return Model_Fields_DivisionField
     */
    public static function GetInstance($dataObject) {
        $divisionField = new Model_Fields_DivisionField(
            $dataObject->{Model_Fields_DivisionFieldDB::DB_COLUMN_ID},
            $dataObject->{Model_Fields_DivisionFieldDB::DB_COLUMN_DIVISION_ID},
            $dataObject->{Model_Fields_DivisionFieldDB::DB_COLUMN_FACILITY_ID},
            $dataObject->{Model_Fields_DivisionFieldDB::DB_COLUMN_FIELD_ID});

        return $divisionField;
    }

    /**
     * @brief: Create a new DivisionField
     *
     * @param int $divisionId - Division identifier
     * @param int $facilityId - Facility identifier
     * @param int $fieldId - Field identifier
     *
     * @return Model_Fields_DivisionField
     */
    public static function Create($divisionId, $facilityId, $fieldId) {
        $dbHandle = new Model_Fields_DivisionFieldDB();
        $dataObject = $dbHandle->create($divisionId, $facilityId, $fieldId);
        assertion(!empty($dataObject), "Unable to create divisionField with name '$divisionId, $facilityId, $fieldId''");

        return Model_Fields_DivisionField::GetInstance($dataObject);
    }

    /**
     * @brief: Get Model_Fields_DivisionField instance for the specified divisionField identifier
     *
     * @param int $divisionFieldId: Unique divisionField identifier
     *
     * @return Model_Fields_DivisionField
     */
    public static function LookupById($divisionFieldId) {
        $dbHandle = new Model_Fields_DivisionFieldDB();
        $dataObject = $dbHandle->getById($divisionFieldId);
        assertion(!empty($dataObject), "DivisionField row for id: '$divisionFieldId' not found");

        return Model_Fields_DivisionField::GetInstance($dataObject);
    }

    /**
     * @brief: Get Model_Fields_DivisionField instance for the specified
     *         division, facility and field identifiers
     *
     * @param int $divisionId - Division identifier
     * @param int $facilityId - Facility identifier
     * @param int $fieldId - Field identifier
     *
     * @return Model_Fields_DivisionField or NULL if object not found
     */
    public static function LookupByDivisionField($divisionId, $facilityId, $fieldId) {
        $dbHandle = new Model_Fields_DivisionFieldDB();
        $dataObject = $dbHandle->getByDivisionField($divisionId, $facilityId, $fieldId);

        if (empty($dataObject)) {
            return NULL;
        }

        return Model_Fields_DivisionField::GetInstance($dataObject);
    }

    /**
     * @brief: Get Model_Fields_Field instances for the specified division and facility
     *
     * @param $divisionId : Identifier of the division used to get locations
     * @param $facilityId : Identifier of the facility used to get locations
     * @param $enabledOnly - If TRUE then get enabled fields only; otherwise get all fields
     *
     * @return Array of Model_Fields_Field
     */
    public static function GetFacilityFields($divisionId, $facilityId, $enabledOnly) {
        $dbHandle = new Model_Fields_DivisionFieldDB();
        $dataObjects = $dbHandle->getByDivisionFacility($divisionId, $facilityId);

        $locations = array();
        foreach ($dataObjects as $dataObject) {
            $field = Model_Fields_Field::LookupById($dataObject->{Model_Fields_DivisionFieldDB::DB_COLUMN_FIELD_ID});

            if ($enabledOnly and $field->enabled == 1) {
                $locations[] = $field;
            } else {
                $locations[] = $field;
            }
        }

        return $locations;
    }

    /**
     * @brief: Get Model_Fields_Division instances for the specified facility and field
     *
     * @param $facilityId : Model_Fields_Facility identifier
     * @param $fieldId : Model_Fields_Field identifier
     *
     * @return Array of Model_Fields_Divisions
     */
    public static function GetFacilityFieldDivisions($facilityId, $fieldId) {
        $dbHandle = new Model_Fields_DivisionFieldDB();
        $dataObjects = $dbHandle->getByFacilityField($facilityId, $fieldId);

        $divisions = array();
        foreach ($dataObjects as $dataObject) {
            $divisions[] = Model_Fields_Division::LookupById($dataObject->{Model_Fields_DivisionFieldDB::DB_COLUMN_DIVISION_ID});
        }

        return $divisions;
    }

    /**
     * @brief: Delete if exists
     *
     * @param int $divisionId - Division identifier
     * @param int $facilityId - Facility identifier
     * @param int $fieldId - Field identifier
     */
    public static function Delete($divisionId, $facilityId, $fieldId) {
        $divisionField = Model_Fields_DivisionField::LookupByDivisionField($divisionId, $facilityId, $fieldId);
        if (isset($divisionField)) {
            $divisionField->_delete();
        }
    }
}