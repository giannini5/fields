<?php
/**
 * This file contains Model_Fields_FacilityLocationDB
 */

/**
 * Model_Fields_FacilityLocationDB class is the DB class for the FacilityLocation database table
 */
class Model_Fields_FacilityLocationDB extends Model_Fields_BaseDB {
    // Schema
    const DB_SCHEMA_NAME = DB_FIELDS_RW;

    // Table information
    const DB_TABLE_NAME = 'facilityLocation';

    // Columns constant
    const DB_COLUMN_ID          = 'id';
    const DB_COLUMN_FACILITY_ID = 'facilityId';
    const DB_COLUMN_LOCATION_ID = 'locationId';

    /**
     * @brief: Constructor
     *
     * @return Model_Fields_FacilityLocationDB
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
        // For an insert the name must be set
        // For a lookup the id or name must be set
        // Not sure if this precondition makes sense
        // precondition(!empty($dataObject->{self::DB_COLUMN_ID}), "fields.facilityLocation." . self::DB_COLUMN_ID . " not set");
        precondition(!empty($dataObject->{self::DB_COLUMN_FACILITY_ID}), "fields.facilityLocation." . self::DB_COLUMN_FACILITY_ID . " not set");
        precondition(!empty($dataObject->{self::DB_COLUMN_LOCATION_ID}), "fields.facilityLocation." . self::DB_COLUMN_LOCATION_ID . " not set");
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
        return array(Model_Fields_FacilityLocationDB::DB_COLUMN_ID => $this->{Model_Fields_FacilityLocationDB::DB_COLUMN_ID});
    }

    /**
     * create a new facilityLocation
     *
     * @param int $facilityId - Facility identifier
     * @param int $locationId - Location identifier
     *
     * @return DataObject[]
     */
    public function create($facilityId, $locationId) {
        $dataObject = new DataObject();
        $dataObject->{self::DB_COLUMN_FACILITY_ID} = $facilityId;
        $dataObject->{self::DB_COLUMN_LOCATION_ID} = $locationId;

        $this->insert($dataObject);

        return $this->getByFacilityLocation($facilityId, $locationId);
    }

    /**
     * getById retrieves the facilityLocation by unique identifier
     *
     * @param int $id - ID of facilityLocation
     *
     * @return DataObject found or NULL if none found
     */
    public function getById($id) {
        $dataObjectArray = $this->getWhere(self::DB_COLUMN_ID . "=" . $id);
        return (0 < count($dataObjectArray)) ? $dataObjectArray[0] : NULL;
    }

    /**
     * getByFacilityLocation retrieves the facilityLocation by unique name
     *
     * @param int $facilityId - Facility identifier
     * @param int $locationId - Location identifier
     *
     * @return DataObject found or NULL if none found
     */
    public function getByFacilityLocation($facilityId, $locationId) {
        $dataObjectArray = $this->getWhere(self::DB_COLUMN_FACILITY_ID . " = '" . $facilityId . "' and " . self::DB_COLUMN_LOCATION_ID . " = '" . $locationId . "'");
        return (0 < count($dataObjectArray)) ? $dataObjectArray[0] : NULL;
    }

    /**
     * getByFacility retrieves the list of location data objects
     *
     * @param int $facilityId - Facility identifier
     *
     * @return DataObject found or NULL if none found
     */
    public function getByFacility($facilityId) {
        $dataObjectArray = $this->getWhere(self::DB_COLUMN_FACILITY_ID . " = '" . $facilityId . "'");
        return $dataObjectArray;
    }
}