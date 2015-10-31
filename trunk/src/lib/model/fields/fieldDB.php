<?php
/**
 * This file contains Model_Fields_FieldDB
 */

/**
 * Model_Fields_FieldDB class is the DB class for the Field database table
 */
class Model_Fields_FieldDB extends Model_Fields_BaseDB {
    // Schema
    const DB_SCHEMA_NAME = DB_FIELDS_RW;

    // Table information
    const DB_TABLE_NAME = 'field';

    // Columns constant
    const DB_COLUMN_ID            = 'id';
    const DB_COLUMN_FACILITY_ID   = 'facilityId';
    const DB_COLUMN_NAME          = 'name';
    const DB_COLUMN_ENABLED       = 'enabled';

    /**
     * @brief: Constructor
     *
     * @return Model_Fields_FieldDB
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
        precondition(!empty($dataObject->{self::DB_COLUMN_FACILITY_ID}), "fields.field." . self::DB_COLUMN_FACILITY_ID . " not set");
        precondition(!empty($dataObject->{self::DB_COLUMN_NAME}), "fields.field." . self::DB_COLUMN_NAME . " not set");
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
        return array(Model_Fields_FieldDB::DB_COLUMN_ID => $this->{Model_Fields_FieldDB::DB_COLUMN_ID});
    }

    /**
     * create a new field
     *
     * @param $facility - Model_Fields_Facility instance
     * @param string $name - name of the field
     * @param bool $enabled - 1 if field is enabled; 0 otherwise
     *
     * @return DataObject[]
     */
    public function create($facility, $name, $enabled) {
        $dataObject = new DataObject();
        $dataObject->{self::DB_COLUMN_FACILITY_ID} = $facility->id;
        $dataObject->{self::DB_COLUMN_NAME} = $name;
        $dataObject->{self::DB_COLUMN_ENABLED} = $enabled;

        $this->insert($dataObject);

        return $this->getByName($facility, $name);
    }

    /**
     * getById retrieves the field by unique identifier
     *
     * @param int $id - ID of field
     *
     * @return DataObject found or NULL if none found
     */
    public function getById($id) {
        $dataObjectArray = $this->getWhere(self::DB_COLUMN_ID . "=" . $id);
        return (0 < count($dataObjectArray)) ? $dataObjectArray[0] : NULL;
    }

    /**
     * getByName retrieves the field by unique facility/name
     *
     * @param $facility - The facility that the practice field coordinator represents
     * @param $name - Field's name (must be unique in the facility)
     * @param $facilityId - Optional facility identifier
     *
     * @return DataObject found or NULL if none found
     */
    public function getByName($facility, $name, $facilityId = NULL) {
        $facilityId = isset($facilityId) ? $facilityId : $facility->id;
        $dataObjectArray = $this->getWhere(self::DB_COLUMN_FACILITY_ID . " = '" . $facilityId . "' and " . self::DB_COLUMN_NAME . " ='" . $name . "'");
        return (0 < count($dataObjectArray)) ? $dataObjectArray[0] : NULL;
    }

    /**
     * getByFacility retrieves a list of fields by facility
     *
     * @param $facility - The facility that the practice field coordinator represents
     *
     * @return Array of DataObjects found empty array if none found
     */
    public function getByFacility($facility) {
        $facilityId = $facility->id;
        $dataObjectArray = $this->getWhere(self::DB_COLUMN_FACILITY_ID . " = '" . $facilityId . "'");
        return $dataObjectArray;
    }
}