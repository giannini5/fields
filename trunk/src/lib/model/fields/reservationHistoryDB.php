<?php
/**
 * This file contains Model_Fields_ReservationHistoryDB
 */

/**
 * Model_Fields_ReservationHistoryDB class is the DB class for the ReservationHistory database table
 */
class Model_Fields_ReservationHistoryDB extends Model_Fields_BaseDB {
    // Schema
    const DB_SCHEMA_NAME = DB_FIELDS_RW;

    // Table information
    const DB_TABLE_NAME = 'reservationHistory';

    // Columns constant
    const DB_COLUMN_ID            = 'id';
    const DB_COLUMN_SEASON_ID     = 'seasonId';
    const DB_COLUMN_FIELD_ID      = 'fieldId';
    const DB_COLUMN_TEAM_ID       = 'teamId';
    const DB_COLUMN_COACH_ID      = 'coachId';
    const DB_COLUMN_START_TIME    = 'startTime';
    const DB_COLUMN_END_TIME      = 'endTime';
    const DB_COLUMN_DAYS_OF_WEEK  = 'daysOfWeek';
    const DB_COLUMN_CREATION_DATE = 'creationDate';
    const DB_COLUMN_TYPE          = 'type';

    /**
     * @brief: Constructor
     *
     * @return Model_Fields_ReservationHistoryDB
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
        precondition(!empty($dataObject->{self::DB_COLUMN_SEASON_ID}), "fields.reservationHistory." . self::DB_COLUMN_SEASON_ID . " not set");
        precondition(!empty($dataObject->{self::DB_COLUMN_FIELD_ID}), "fields.reservationHistory." . self::DB_COLUMN_FIELD_ID . " not set");
        precondition(!empty($dataObject->{self::DB_COLUMN_TEAM_ID}), "fields.reservationHistory." . self::DB_COLUMN_TEAM_ID . " not set");
        precondition(!empty($dataObject->{self::DB_COLUMN_COACH_ID}), "fields.reservationHistory." . self::DB_COLUMN_COACH_ID . " not set");
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
        return array(Model_Fields_ReservationHistoryDB::DB_COLUMN_ID => $this->{Model_Fields_ReservationHistoryDB::DB_COLUMN_ID});
    }

    /**
     * create a new reservation
     *
     * @param $season - Model_Fields_Season instance
     * @param $field - Model_Fields_Field instance
     * @param $team - Model_Fields_Team instance
     * @param $coach - Model_Fields_Coach instance
     * @param $startTime - Start time of reservation
     * @param $endTime - End time of reservation
     * @param $daysOfWeek - Bit map of days that field is reserved
     * @param $type - A for Add, D for delete
     *
     * @return DataObject[]
     */
    public function create($season, $field, $team, $coach, $startTime, $endTime, $daysOfWeek, $type) {
        $dataObject = new DataObject();
        $dataObject->{self::DB_COLUMN_SEASON_ID} = $season->id;
        $dataObject->{self::DB_COLUMN_FIELD_ID} = $field->id;
        $dataObject->{self::DB_COLUMN_TEAM_ID} = $team->id;
        $dataObject->{self::DB_COLUMN_COACH_ID} = $coach->id;
        $dataObject->{self::DB_COLUMN_START_TIME} = $startTime;
        $dataObject->{self::DB_COLUMN_END_TIME} = $endTime;
        $dataObject->{self::DB_COLUMN_DAYS_OF_WEEK} = $daysOfWeek;
        $dataObject->{self::DB_COLUMN_TYPE} = $type;

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
     * getBySeason retrieves the reservations by unique season combination
     *
     * @param $season - Season associated with the reservation
     *
     * @return DataObject array found or NULL if none found
     */
    public function getBySeason($season) {
        $seasonId = $season->id;

        $dataObjectArray = $this->getWhere(
            self::DB_COLUMN_SEASON_ID . " = " . $seasonId . " order by " . self::DB_COLUMN_CREATION_DATE . " desc");

        return $dataObjectArray;
    }

    /**
     * getByTeam retrieves the reservations by unique season and team combination
     *
     * @param $season - Season associated with the reservation
     * @param $team - Team associated with the reservation
     *
     * @return DataObject array found or NULL if none found
     */
    public function getByTeam($season, $team) {
        $seasonId = $season->id;
        $teamId = $team->id;

        $dataObjectArray = $this->getWhere(
            self::DB_COLUMN_SEASON_ID . " = " . $seasonId
            . " and " . self::DB_COLUMN_TEAM_ID . " = " . $teamId);

        return $dataObjectArray;
    }

    /**
     * getByField retrieves the reservations by unique season and field combination
     *
     * @param $season - Season associated with the reservation
     * @param $field - Field associated with the reservation
     *
     * @return DataObject array found or NULL if none found
     */
    public function getByField($season, $field) {
        $seasonId = $season->id;
        $fieldId = $field->id;

        $dataObjectArray = $this->getWhere(
            self::DB_COLUMN_SEASON_ID . " = " . $seasonId
            . " and " . self::DB_COLUMN_FIELD_ID . " = " . $fieldId);

        return $dataObjectArray;
    }

    /**
     * getByCoach retrieves the reservations by unique season and coach combination
     *
     * @param $season - Season associated with the reservation
     * @param $coach - Field associated with the reservation
     *
     * @return DataObject array found or NULL if none found
     */
    public function getByCoach($season, $coach) {
        $seasonId = $season->id;
        $coachId = $coach->id;

        $dataObjectArray = $this->getWhere(
            self::DB_COLUMN_SEASON_ID . " = " . $seasonId
            . " and " . self::DB_COLUMN_COACH_ID . " = " . $coachId);

        return $dataObjectArray;
    }
}