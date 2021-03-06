<?php
/**
 * This file contains Model_Fields_SeasonDB
 */

/**
 * Model_Fields_SeasonDB class is the DB class for the Season database table
 */
class Model_Fields_SeasonDB extends Model_Fields_BaseDB {
    // Schema
    const DB_SCHEMA_NAME = DB_FIELDS_RW;

    // Table information
    const DB_TABLE_NAME = 'season';

    // Columns constant
    const DB_COLUMN_ID                      = 'id';
    const DB_COLUMN_LEAGUE_ID               = 'leagueId';
    const DB_COLUMN_NAME                    = 'name';
    const DB_COLUMN_BEGIN_RESERVATION_DATE  = 'beginReservationsDate';
    const DB_COLUMN_ENABLED                 = 'enabled';
    const DB_COLUMN_START_DATE              = 'startDate';
    const DB_COLUMN_END_DATE                = 'endDate';
    const DB_COLUMN_START_TIME              = 'startTime';
    const DB_COLUMN_END_TIME                = 'endTime';
    const DB_COLUMN_DAYS_OF_WEEK            = 'daysOfWeek';
    const DB_COLUMN_LOGIN_ALLOWED           = 'loginAllowed';
    const DB_COLUMN_CREATE_ALLOWED          = 'createAllowed';

    /**
     * @brief: Constructor
     *
     * @return Model_Fields_SeasonDB
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
        precondition(!empty($dataObject->{self::DB_COLUMN_LEAGUE_ID}), "fields.season." . self::DB_COLUMN_LEAGUE_ID . " not set");
        precondition(!empty($dataObject->{self::DB_COLUMN_NAME}), "fields.season." . self::DB_COLUMN_NAME . " not set");
    }

    /**
     * @brief Set default value for the elements that are not set
     *
     * @param DataObject $dataObject
     */
    protected function _setDefaults(DataObject &$dataObject) {
    }

    /**
     * @brief Check to make sure all the data passed for DB update meets a minimum requirement
     *
     * @param DataObject $dataObject
     */
    protected function _checkUpdatePreconditions(DataObject $dataObject) {
    }

    /**
     * @brief _getUpdateKeys will return the update keys
     *
     * @return array primaryKeys K => V
     */
    public function _getUpdateKeys() {
        return array(Model_Fields_SeasonDB::DB_COLUMN_ID => $this->{Model_Fields_SeasonDB::DB_COLUMN_ID});
    }

    /**
     * @brief create a new season
     *
     * @param $league - Model_Fields_League instance
     * @param string $name - name of the season
     * @param string $beginReservationDate - Date reservations selection can begin
     * @param string $startDate - Day season becomes available
     * @param string $endDate - Last day season is available
     * @param string $startTime - Start time during the day that practice can start
     * @param string $endTime - End time during the day that practice must end
     * @param bool $enabled - 1 if season is enabled; 0 otherwise
     * @param string $daysOfWeek - Default practice days of week.  daysOfWeek[0] = Monday
     * @param int $loginAllowed - Default to 1.  Login not allowed if 0
     * @param int $createAllowed - Default to 1. Create account not allowed if 0
     *
     * @return DataObject[]
     */
    public function create($league, $name, $beginReservationDate, $startDate, $endDate, $startTime, $endTime, $enabled, $daysOfWeek = '1111100', $loginAllowed = 1, $createAllowed = 1) {
        $dataObject = new DataObject();
        $dataObject->{self::DB_COLUMN_LEAGUE_ID} = $league->id;
        $dataObject->{self::DB_COLUMN_NAME} = $name;
        $dataObject->{self::DB_COLUMN_BEGIN_RESERVATION_DATE} = $beginReservationDate;
        $dataObject->{self::DB_COLUMN_START_DATE} = $startDate;
        $dataObject->{self::DB_COLUMN_END_DATE} = $endDate;
        $dataObject->{self::DB_COLUMN_START_TIME} = $startTime;
        $dataObject->{self::DB_COLUMN_END_TIME} = $endTime;
        $dataObject->{self::DB_COLUMN_DAYS_OF_WEEK} = $daysOfWeek;
        $dataObject->{self::DB_COLUMN_LOGIN_ALLOWED} = $loginAllowed;
        $dataObject->{self::DB_COLUMN_CREATE_ALLOWED} = $createAllowed;
        $dataObject->{self::DB_COLUMN_ENABLED} = $enabled;

        $this->insert($dataObject);

        return $this->getByName($league, $name);
    }

    /**
     * @brief getById retrieves the season by unique identifier
     *
     * @param int $id - ID of season
     *
     * @return DataObject found or NULL if none found
     */
    public function getById($id) {
        $dataObjectArray = $this->getWhere(self::DB_COLUMN_ID . "=" . $id);
        return (0 < count($dataObjectArray)) ? $dataObjectArray[0] : NULL;
    }

    /**
     * @brief getByName retrieves the season by unique league/email
     *
     * @param $league - The league that the practice field coordinator represents
     * @param $name - Season's name (must be unique in the league)
     * @param $leagueId - Optional league identifier
     *
     * @return DataObject found or NULL if none found
     */
    public function getByName($league, $name, $leagueId = NULL) {
        $leagueId = isset($leagueId) ? $leagueId : $league->id;
        $dataObjectArray = $this->getWhere(self::DB_COLUMN_LEAGUE_ID . " = '" . $leagueId . "' and " . self::DB_COLUMN_NAME . " ='" . $name . "'");
        return (0 < count($dataObjectArray)) ? $dataObjectArray[0] : NULL;
    }

    /**
     * @brief getByLeague retrieves the seasons by league
     *
     * @param $league - The league that the practice field coordinator represents
     *
     * @return array of DataObjects, empty array if none found
     */
    public function getByLeague($league) {
        $dataObjectArray = $this->getWhere(self::DB_COLUMN_LEAGUE_ID . " = " . $league->id . " order by name desc");
        return $dataObjectArray;
    }

    /**
     * @brief Retrieves the season that is enabled for the league or NULL if no season is enabled
     *
     * @param $league - The league that the practice field coordinator represents
     *
     * @return DataObject found or NULL if none found
     */
    public function getEnabledSeason($league) {
        $leagueId = $league->id;
        $dataObjectArray = $this->getWhere(self::DB_COLUMN_LEAGUE_ID . " = '" . $leagueId . "' and " . self::DB_COLUMN_ENABLED . " ='1'");
        assertion(count($dataObjectArray) <= 1, "ERROR: more than one season enabled for league: $league->name");
        return (0 < count($dataObjectArray)) ? $dataObjectArray[0] : NULL;
    }
}