<?php
/**
 * This file contains Model_Fields_FieldAvailabilityDB
 */

/**
 * Model_Fields_FieldAvailabilityDB class is the DB class for the FieldAvailability database table
 */
class Model_Fields_FieldAvailabilityDB extends Model_Fields_BaseDB {
    // Schema
    const DB_SCHEMA_NAME = DB_FIELDS_RW;

    // Table information
    const DB_TABLE_NAME = 'fieldAvailability';

    // Columns constant
    const DB_COLUMN_ID            = 'id';
    const DB_COLUMN_FIELD_ID      = 'fieldId';
    const DB_COLUMN_START_DATE    = 'startDate';
    const DB_COLUMN_END_DATE      = 'endDate';
    const DB_COLUMN_START_TIME    = 'startTime';
    const DB_COLUMN_END_TIME      = 'endTime';
    const DB_COLUMN_DAYS_OF_WEEK  = 'daysOfWeek';

    /**
     * @brief: Constructor
     *
     * @return Model_Fields_FieldAvailabilityDB
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
        precondition(!empty($dataObject->{self::DB_COLUMN_FIELD_ID}), "fields.field." . self::DB_COLUMN_FIELD_ID . " not set");
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
        return array(Model_Fields_FieldAvailabilityDB::DB_COLUMN_ID => $this->{Model_Fields_FieldAvailabilityDB::DB_COLUMN_ID});
    }

    /**
     * create a new field
     *
     * @param $field             - Model_Fields_Field instance
     * @param string $startDate  - Day field becomes available
     * @param string $endDate    - Last day field is available
     * @param string $startTime  - Start time during the day that the field is available
     * @param string $endTime    - End time during the day that the field is available
     * @param string $daysOfWeek - Days of week field is available.  daysOfWeek[0] = Sunday
     *
     * @return DataObject[]
     */
    public function create($field, $startDate, $endDate, $startTime, $endTime, $daysOfWeek = '1111100') {
        $dataObject = new DataObject();
        $dataObject->{self::DB_COLUMN_FIELD_ID}     = $field->id;
        $dataObject->{self::DB_COLUMN_START_DATE}   = $startDate;
        $dataObject->{self::DB_COLUMN_END_DATE}     = $endDate;
        $dataObject->{self::DB_COLUMN_START_TIME}   = $startTime;
        $dataObject->{self::DB_COLUMN_END_TIME}     = $endTime;
        $dataObject->{self::DB_COLUMN_DAYS_OF_WEEK} = $daysOfWeek;

        $this->insert($dataObject);

        return $this->getByFieldId($field->id);
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
     * getByFieldId retrieves the field by unique field/name
     *
     * @param $fieldId - Field's unique identifier
     *
     * @return DataObject found or NULL if none found
     */
    public function getByFieldId($fieldId) {
        $dataObjectArray = $this->getWhere(self::DB_COLUMN_FIELD_ID . " = '" . $fieldId . "' and " . self::DB_COLUMN_FIELD_ID . " ='" . $fieldId . "'");
        return (0 < count($dataObjectArray)) ? $dataObjectArray[0] : NULL;
    }
}