<?php
/**
 * This file contains Model_Fields_Season
 */

/**
 * Model_Fields_Season class
 */
class Model_Fields_Season extends Model_Fields_Base implements SaveModelInterface {

    public $m_league;

    /**
     * @brief: Constructor
     *
     * @param $league - Model_Fields_League instance
     * @param $id - unique identifier
     * @param $leagueId - unique league identifier
     * @param string $name - name of the season
     * @param string $beginReservationDate - Date reservtion selection can begin
     * @param string $startDate - Day season becomes available
     * @param string $endDate - Last day season is available
     * @param string $startTime - Start time during the day that practice can start
     * @param string $endTime - End time during the day that practice must end
     * @param bool $enabled - 1 if season is enabled; 0 otherwise
     * @param string $daysOfWeek - Default practice days of week.  daysOfWeek[0] = Monday
     * @param int $loginAllowed - Default to 1.  Login not allowed if 0
     * @param int $createAllowed - Default to 1.  Create account not allowed if 0
     */
    public function __construct($league = NULL, $id = NULL, $leagueId = NULL, $name = '', $beginReservationDate = '', $startDate = '', $endDate = '', $startTime = '', $endTime = '', $enabled = 0, $daysOfWeek = '1111100', $loginAllowed = 1, $createAllowed = 0) {
        parent::__construct('Model_Fields_SeasonDB', Model_Base::AUTO_DECLARE_CLASS_VARIABLE_ON);

        $this->m_league = $league;
        $this->{Model_Fields_SeasonDB::DB_COLUMN_ID}   = $id;
        $this->{Model_Fields_SeasonDB::DB_COLUMN_LEAGUE_ID}   = $leagueId;
        $this->{Model_Fields_SeasonDB::DB_COLUMN_NAME} = $name;
        $this->{Model_Fields_SeasonDB::DB_COLUMN_BEGIN_RESERVATION_DATE} = $beginReservationDate;
        $this->{Model_Fields_SeasonDB::DB_COLUMN_START_DATE} = $startDate;
        $this->{Model_Fields_SeasonDB::DB_COLUMN_END_DATE} = $endDate;
        $this->{Model_Fields_SeasonDB::DB_COLUMN_START_TIME} = $startTime;
        $this->{Model_Fields_SeasonDB::DB_COLUMN_END_TIME} = $endTime;
        $this->{Model_Fields_SeasonDB::DB_COLUMN_DAYS_OF_WEEK} = $daysOfWeek;
        $this->{Model_Fields_SeasonDB::DB_COLUMN_LOGIN_ALLOWED} = $loginAllowed;
        $this->{Model_Fields_SeasonDB::DB_COLUMN_CREATE_ALLOWED} = $createAllowed;
        $this->{Model_Fields_SeasonDB::DB_COLUMN_ENABLED} = $enabled;
        $this->_setLeague();
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
        /** @var Model_Fields_SeasonDB $dbHandle */
        $dbHandle = $this->_getDBHandle();
        if (!is_null($this->{Model_Fields_SeasonDB::DB_COLUMN_ID})) {
            $dataObj = $dbHandle->getById($this->{Model_Fields_SeasonDB::DB_COLUMN_ID});

        } else if (!is_null($this->{Model_Fields_SeasonDB::DB_COLUMN_NAME})) {
            $dataObj = $dbHandle->getByName(
                NULL,
                $this->{Model_Fields_SeasonDB::DB_COLUMN_NAME},
                TRUE,
                $this->{Model_Fields_SeasonDB::DB_COLUMN_LEAGUE_ID});
        }

        if (!empty($dataObj)) {
            $this->assignModel($dataObj);
            $this->_setLeague();
            return TRUE;
        }

        return FALSE;
    }

    /**
     * Check to see if it is too soon to reserve a field.
     *
     * @return bool - true if reservations are open; false otherwise
     */
    public function okayToReserveField() {
        $beginDateTime = DateTime::createFromFormat('Y-m-d H:i:s', $this->beginReservationsDate);
        $now = new DateTime();

        $nowString = $now->format('Y-m-d H:i:s');
        $beginDateString = $beginDateTime->format('Y-m-d H:i:s');

        return $now >= $beginDateTime;
    }

    /**
     * @brief: Set the league member variable if not already set
     */
    private function _setLeague() {
        if (!isset($this->m_league)) {
            $this->m_league = Model_Fields_League::LookupById($this->{Model_Fields_SeasonDB::DB_COLUMN_LEAGUE_ID});
        }
    }

    /**
     * _getUpdateKeys will return the update keys
     *
     * @return array primaryKeys K => V
     */
    public function _getUpdateKeys() {
        return array(Model_Fields_SeasonDB::DB_COLUMN_ID => $this->{Model_Fields_SeasonDB::DB_COLUMN_ID});
    }

    /**
     * @brief: Get and instance of this object from databaes data.
     *
     * @param $dataObject - data object representing the content of the object
     * @param $league - Model_Fields_League instance
     *
     * @return Model_Fields_Season
     */
    public static function GetInstance($dataObject, $league = NULL) {
        $season = new Model_Fields_Season(
            $league,
            $dataObject->{Model_Fields_SeasonDB::DB_COLUMN_ID},
            $dataObject->{Model_Fields_SeasonDB::DB_COLUMN_LEAGUE_ID},
            $dataObject->{Model_Fields_SeasonDB::DB_COLUMN_NAME},
            $dataObject->{Model_Fields_SeasonDB::DB_COLUMN_BEGIN_RESERVATION_DATE},
            $dataObject->{Model_Fields_SeasonDB::DB_COLUMN_START_DATE},
            $dataObject->{Model_Fields_SeasonDB::DB_COLUMN_END_DATE},
            $dataObject->{Model_Fields_SeasonDB::DB_COLUMN_START_TIME},
            $dataObject->{Model_Fields_SeasonDB::DB_COLUMN_END_TIME},
            $dataObject->{Model_Fields_SeasonDB::DB_COLUMN_ENABLED},
            $dataObject->{Model_Fields_SeasonDB::DB_COLUMN_DAYS_OF_WEEK},
            $dataObject->{Model_Fields_SeasonDB::DB_COLUMN_LOGIN_ALLOWED},
            $dataObject->{Model_Fields_SeasonDB::DB_COLUMN_CREATE_ALLOWED});

        $season->setLoaded();

        return $season;
    }

    /**
     * @brief: Create a new Season
     *
     * @param $league - Model_Fields_League instance
     * @param string $name - name of the season
     * @param string $beginReservationDate - Date reservation selection can begin
     * @param string $startDate - Day season becomes available
     * @param string $endDate - Last day season is available
     * @param string $startTime - Start time during the day that practice can start
     * @param string $endTime - End time during the day that practice must end
     * @param int $enabled - 1 if season is enabled; 0 otherwise
     * @param string $daysOfWeek - Default practice days of week.  daysOfWeek[0] = Monday
     * @param int $loginAllowed - Default to 1.  Login not allowed if 0
     * @param int $createAllowed - Default to 1.  Create account not allowed if 0
     *
     *
     * @return Model_Fields_Season
     * @throws AssertionException
     */
    public static function Create($league, $name, $beginReservationDate, $startDate, $endDate, $startTime, $endTime, $enabled, $daysOfWeek = '1111100', $loginAllowed = 1, $createAllowed = 1) {
        $dbHandle = new Model_Fields_SeasonDB();
        $dataObject = $dbHandle->create($league, $name, $beginReservationDate, $startDate, $endDate, $startTime, $endTime, $enabled, $daysOfWeek, $loginAllowed, $createAllowed);
        assertion(!empty($dataObject), "Unable to create Season with name:'$name'");

        return Model_Fields_Season::GetInstance($dataObject, $league);
    }

    /**
     * @brief: Get Model_Fields_Season instance for the specified Season identifier
     *
     * @param bigint $seasonId: Unique Season identifier
     *
     * @return Model_Fields_Season
     */
    public static function LookupById($seasonId) {
        $dbHandle = new Model_Fields_SeasonDB();
        $dataObject = $dbHandle->getById($seasonId);
        assertion(!empty($dataObject), "Season row for id: '$seasonId' not found");

        return Model_Fields_Season::GetInstance($dataObject);
    }

    /**
     * @brief: Get Model_Fields_Season instance for the specified Season league and name
     *
     * @param $league - Model_Fields_League instance
     * @param $name - Season's name
     * @param $assertIfNotFound - If TRUE then assert object if found.  Otherwise return NULL when object not found
     *
     * @return Model_Fields_Season or NULL if object not found and $assertIfNotFound is FALSE
     * @throws AssertionException
     */
    public static function LookupByName($league, $name, $assertIfNotFound = TRUE, $leagueId = NULL) {
        $dbHandle = new Model_Fields_SeasonDB();
        $dataObject = $dbHandle->getByName($league, $name, $leagueId);

        if ($assertIfNotFound) {
            assertion(!empty($dataObject), "Season row for name: $name not found");
        } else if (empty($dataObject)) {
            return NULL;
        }

        return Model_Fields_Season::GetInstance($dataObject, $league);
    }

    /**
     * @brief: Get Model_Fields_Season instances for the specified league
     *
     * @param $league - Model_Fields_League instance
     *
     * @return array of Model_Fields_Seasons empty array if none found
     */
    public static function LookupByLeague($league) {
        $dbHandle = new Model_Fields_SeasonDB();
        $dataObjects = $dbHandle->getByLeague($league);

        $seasons = array();
        foreach ($dataObjects as $dataObject) {
            $seasons[] = Model_Fields_Season::LookupById($dataObject->{Model_Fields_SeasonDB::DB_COLUMN_ID});
        }

        return $seasons;
    }

    /**
     * @brief: Get the enabled Model_Fields_Season instances for the specified league
     *
     * @param $league - Model_Fields_League instance
     *
     * @param bool $assertIfNotFound - defaults to TRUE
     *
     * @return Model_Fields_Season that is enabled
     * @throws AssertionException
     */
    public static function GetEnabledSeason($league, $assertIfNotFound = TRUE) {
        $dbHandle = new Model_Fields_SeasonDB();
        $dataObject = $dbHandle->getEnabledSeason($league);

        if ($assertIfNotFound) {
            assertion(!empty($dataObject), "Enabled Season for league: $league->name not found");
        }

        if (empty($dataObject)) {
            return NULL;
        }

        return Model_Fields_Season::GetInstance($dataObject, $league);
    }

    /**
     * @brief: Delete if exists
     *
     * @param $league - Model_Fields_League instance
     * @param $name - Season's name
     */
    public static function Delete($league, $name) {
        $season = Model_Fields_Season::LookupByName($league, $name, FALSE);
        if (isset($season)) {
            $season->_delete();
        }
    }
}