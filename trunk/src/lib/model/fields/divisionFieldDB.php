<?php
/**
 * This file contains Model_Fields_DivisionFieldDB
 */

/**
 * Model_Fields_DivisionFieldDB class is the DB class for the DivisionField database table
 */
class Model_Fields_DivisionFieldDB extends Model_Fields_BaseDB {
    // Schema
    const DB_SCHEMA_NAME = DB_FIELDS_RW;

    // Table information
    const DB_TABLE_NAME = 'divisionField';

    // Columns constant
    const DB_COLUMN_ID          = 'id';
    const DB_COLUMN_DIVISION_ID = 'divisionId';
    const DB_COLUMN_FACILITY_ID = 'facilityId';
    const DB_COLUMN_FIELD_ID    = 'fieldId';


    /**
     * @brief: Constructor
     *
     * @return Model_Fields_DivisionFieldDB
     */
    public function __construct() {
        parent::__construct(self::DB_SCHEMA_NAME, self::DB_TABLE_NAME, DAG_Factory::DB_ADAPTER_DEFAULTSQL);
    }

    /**
     * @brief: Check to make sure all the data passed for DB meets a minimum requirement
     *
     * @param: DataObject $dataObject
     */
    protected function _checkPreconditions(DataObject $dataObject) {
        // For an insert the division, facility and field identifiers must be set
        // For a lookup the id or division, facility and field ids must be set
        precondition(!empty($dataObject->{self::DB_COLUMN_DIVISION_ID}), "divisionField.divisionField." . self::DB_COLUMN_DIVISION_ID . " not set");
        precondition(!empty($dataObject->{self::DB_COLUMN_FACILITY_ID}), "divisionField.facilityId." . self::DB_COLUMN_FACILITY_ID . " not set");
        precondition(!empty($dataObject->{self::DB_COLUMN_FIELD_ID}), "divisionField.fieldId." . self::DB_COLUMN_FIELD_ID . " not set");
    }

    /**
     * Set default value for the elements that are not set
     *
     * @param DataObject $dataObject
     */
    protected function _setDefaults(DataObject &$dataObject) {
    }

    /**
     * Check to make sure all the data passed for DB update meets a minimum requirement
     *
     * @param DataObject $dataObject
     */
    protected function _checkUpdatePreconditions(DataObject $dataObject) {
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
     * create a new divisionField
     *
     * @param int $divisionId - Division identifier
     * @param int $facilityId - Facility identifier
     * @param int $fieldId - Field identifier
     *
     * @return dataObject
     */
    public function create($divisionId, $facilityId, $fieldId) {
        $dataObject = new DataObject();
        $dataObject->{self::DB_COLUMN_DIVISION_ID} = $divisionId;
        $dataObject->{self::DB_COLUMN_FACILITY_ID} = $facilityId;
        $dataObject->{self::DB_COLUMN_FIELD_ID} = $fieldId;

        $this->insert($dataObject);

        return $this->getByDivisionField($divisionId, $facilityId, $fieldId);
    }

    /**
     * getById retrieves the divisionField by unique identifier
     *
     * @param int $id - ID of divisionField
     *
     * @return DataObject found or NULL if none found
     */
    public function getById($id) {
        $dataObjectArray = $this->getWhere(self::DB_COLUMN_ID . "=" . $id);
        return (0 < count($dataObjectArray)) ? $dataObjectArray[0] : NULL;
    }

    /**
     * getByDivisionField retrieves the divisionField by unique name
     *
     * @param int $divisionId - Division identifier
     * @param int $facilityId - Facility identifier
     * @param int $fieldId - Field identifier
     *
     * @return DataObject found or NULL if none found
     */
    public function getByDivisionField($divisionId, $facilityId, $fieldId) {
        $dataObjectArray = $this->getWhere(self::DB_COLUMN_DIVISION_ID . " = '" . $divisionId . "' and " . self::DB_COLUMN_FACILITY_ID . " = '" . $facilityId . "' and " . self::DB_COLUMN_FIELD_ID . " = '" . $fieldId . "'");
        return (0 < count($dataObjectArray)) ? $dataObjectArray[0] : NULL;
    }

    /**
     * getByDivisionFacility retrieves the list of field data objects for a division and facility
     *
     * @param int $divisionId - Division identifier
     * @param int $facilityId - Facility identifier
     *
     * @return Array of DataObjects found or empty array if none found
     */
    public function getByDivisionFacility($divisionId, $facilityId) {
        $dataObjectArray = $this->getWhere(self::DB_COLUMN_DIVISION_ID . " = '" . $divisionId . "' and " . self::DB_COLUMN_FACILITY_ID . " = '" . $facilityId . "'");
        return $dataObjectArray;
    }

    /**
     * getByDivisionFacility retrieves the list of field data objects for a division and facility
     *
     * @param int $facilityId - Model_Fields_Facility identifier
     * @param int $fieldId - Model_Fields_Field identifier
     *
     * @return Array of DataObjects found or empty array if none found
     */
    public function getByFacilityField($facilityId, $fieldId) {
        $dataObjectArray = $this->getWhere(self::DB_COLUMN_FACILITY_ID . " = '" . $facilityId . "' and " . self::DB_COLUMN_FIELD_ID . " = '" . $fieldId . "'");
        return $dataObjectArray;
    }
}