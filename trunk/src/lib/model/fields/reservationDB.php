<?php
/**
 * This file contains Model_Fields_ReservationDB
 */

/**
 * Model_Fields_ReservationDB class is the DB class for the Field database table
 */
class Model_Fields_ReservationDB extends Model_Fields_BaseDB {
    // Schema
    const DB_SCHEMA_NAME = DB_FIELDS_RW;

    // Table information
    const DB_TABLE_NAME = 'reservation';

    // Columns constant
    const DB_COLUMN_ID         = 'id';
    const DB_COLUMN_SEASON_ID  = 'seasonId';
    const DB_COLUMN_FIELD_ID   = 'fieldId';
    const DB_COLUMN_TEAM_ID    = 'teamId';
    const DB_COLUMN_START_TIME = 'startTime';
    const DB_COLUMN_END_TIME   = 'endTime';

    /**
     * @brief: Constructor
     *
     * @return Model_Fields_ReservationDB
     */
    public function __construct() {
        parent::__construct(self::DB_SCHEMA_NAME, self::DB_TABLE_NAME, DAG_Factory::DB_ADAPTER_DEFAULTSQL);
    }

    /**
     * @brief: Check to make sure all the data passed for DB meets a minimum requirement
     *
     * @param DataObject $dataObject
     *
     * @throws PreconditionException
     */
    protected function _checkPreconditions(DataObject $dataObject) {
        precondition(!empty($dataObject->{self::DB_COLUMN_SEASON_ID}), "fields.reservation." . self::DB_COLUMN_SEASON_ID . " not set");
        precondition(!empty($dataObject->{self::DB_COLUMN_FIELD_ID}), "fields.reservation." . self::DB_COLUMN_FIELD_ID . " not set");
        precondition(!empty($dataObject->{self::DB_COLUMN_TEAM_ID}), "fields.reservation." . self::DB_COLUMN_TEAM_ID . " not set");
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
        return array(Model_Fields_ReservationDB::DB_COLUMN_ID => $this->{Model_Fields_ReservationDB::DB_COLUMN_ID});
    }

    /**
     * create a new reservation
     *
     * @param $season - Model_Fields_Season instance
     * @param $field - Model_Fields_Field instance
     * @param $team - Model_Fields_Team instance
     * @param $startTime - Start time of reservation
     * @param $endTime - End time of reservation
     *
     * @return DataObject[]
     */
    public function create($season, $field, $team, $startTime, $endTime) {
        $dataObject = new DataObject();
        $dataObject->{self::DB_COLUMN_SEASON_ID} = $season->id;
        $dataObject->{self::DB_COLUMN_FIELD_ID} = $field->id;
        $dataObject->{self::DB_COLUMN_TEAM_ID} = $team->id;
        $dataObject->{self::DB_COLUMN_START_TIME} = $startTime;
        $dataObject->{self::DB_COLUMN_END_TIME} = $endTime;

        $id = $this->insert($dataObject);

        return $this->getById($id);
    }

    /**
     * getById retrieves the reservation by unique identifier
     *
     * @param int $id - ID of reservation
     *
     * @return DataObject found or NULL if none found
     */
    public function getById($id) {
        $dataObjectArray = $this->getWhere(self::DB_COLUMN_ID . "=" . $id);
        return (0 < count($dataObjectArray)) ? $dataObjectArray[0] : NULL;
    }

    /**
     * getByTeam retrieves the reservations by unique season and team combination
     *
     * @param $season - Season associated with the reservation
     * @param $team - Team associated with the reservation
     * @param $seasonId - Optional season identifier (used as override)
     * @param $teamId - Optional team identifier (used as override)
     *
     * @return DataObject array found or NULL if none found
     */
    public function getByTeam($season, $team, $seasonId = NULL, $teamId = NULL) {
        $seasonId = isset($seasonId) ? $seasonId : $season->id;
        $teamId = isset($teamId) ? $teamId : $team->id;

        $dataObjectArray = $this->getWhere(
            self::DB_COLUMN_SEASON_ID . " = " . $seasonId
            . " and " . self::DB_COLUMN_TEAM_ID . " = " . $teamId);

        return (0 < count($dataObjectArray)) ? $dataObjectArray : NULL;
    }

    /**
     * getByField retrieves the reservations by unique season and field combination
     *
     * @param $season - Season associated with the reservation
     * @param $field - Field associated with the reservation
     * @param $seasonId - Optional season identifier (used as override)
     * @param $fieldId - Optional field identifier (used as override)
     *
     * @return DataObject array found or NULL if none found
     */
    public function getByField($season, $field, $seasonId = NULL, $fieldId = NULL) {
        $seasonId = isset($seasonId) ? $seasonId : $season->id;
        $fieldId = isset($fieldId) ? $fieldId : $field->id;

        $dataObjectArray = $this->getWhere(
            self::DB_COLUMN_SEASON_ID . " = " . $seasonId
            . " and " . self::DB_COLUMN_FIELD_ID . " = " . $fieldId);

        return (0 < count($dataObjectArray)) ? $dataObjectArray : NULL;
    }
}