<?php
/**
 * This file contains Model_Fields_ReservationHistory
 */

/**
 * Model_Fields_ReservationHistory class
 */
class Model_Fields_ReservationHistory extends Model_Fields_Base implements SaveModelInterface {

    const ADD    = 'A';
    const DELETE = 'D';

    public $m_season;
    public $m_field;
    public $m_team;
    public $m_coach;

    /**
     * @brief: Constructor
     *
     * @param $season - Model_Fields_Season instance
     * @param $field - Model_Fields_Field instance
     * @param $team - Model_Fields_Team instance
     * @param $coach - Model_Fields_Coach instance
     * @param $id - unique identifier
     * @param $seasonId - unique season identifier
     * @param $fieldId - unique field identifier
     * @param $teamId - unique team identifier
     * @param $coachId - unique coach identifier
     * @param string $startTime - start time of reservation
     * @param string $endTime - end time of reservation
     * @param string $daysOfWeek - 7 character string
     *                             daysOfWeek[0] == 1 then Sunday selected
     *                             ...
     *                             daysOfWeek[6] == 1 then Saturday selected
     * @param char $type - A for Add, D for delete
     * @param dateTime $creationDate - Creation date of history event
     */
    public function __construct($season = NULL, $field = NULL, $team = NULL, $coach = NULL, $id = NULL, $seasonId = NULL, $fieldId = NULL, $teamId = NULL, $coachId = NULL, $startTime = '', $endTime = '', $daysOfWeek = '0000000', $type = NULL, $creationDate = NULL) {
        precondition(strlen($daysOfWeek) == 7, "daysOfWeek length of " . strlen($daysOfWeek) . " is invalid.  Must be 7");

        parent::__construct('Model_Fields_ReservationHistoryDB', Model_Base::AUTO_DECLARE_CLASS_VARIABLE_ON);

        $this->m_season = $season;
        $this->m_field = $field;
        $this->m_team = $team;
        $this->m_coach = $coach;
        $this->{Model_Fields_ReservationHistoryDB::DB_COLUMN_ID}   = $id;
        $this->{Model_Fields_ReservationHistoryDB::DB_COLUMN_SEASON_ID} = $seasonId;
        $this->{Model_Fields_ReservationHistoryDB::DB_COLUMN_FIELD_ID} = $fieldId;
        $this->{Model_Fields_ReservationHistoryDB::DB_COLUMN_TEAM_ID} = $teamId;
        $this->{Model_Fields_ReservationHistoryDB::DB_COLUMN_COACH_ID} = $coachId;
        $this->{Model_Fields_ReservationHistoryDB::DB_COLUMN_START_TIME} = $startTime;
        $this->{Model_Fields_ReservationHistoryDB::DB_COLUMN_END_TIME} = $endTime;
        $this->{Model_Fields_ReservationHistoryDB::DB_COLUMN_DAYS_OF_WEEK} = $daysOfWeek;
        $this->{Model_Fields_ReservationHistoryDB::DB_COLUMN_TYPE} = $type;
        $this->{Model_Fields_ReservationHistoryDB::DB_COLUMN_CREATION_DATE} = $creationDate;

        $this->_setSeason();
        $this->_setField();
        $this->_setTeam();
        $this->_setCoach();
    }

    /**
     * @brief Check to see if the day is selected in the reservation
     *
     * @param int $day - Number from 0 to 6.  0 is Monday, 6 is Sunday
     *
     * @return bool - Return TRUE if the days is selected; FALSE otherwise
     */
    public function isDaySelected($day) {
        return $this->{Model_Fields_ReservationHistoryDB::DB_COLUMN_DAYS_OF_WEEK}[$day] == '1';
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
        /** @var Model_Fields_ReservationHistoryDB $dbHandle */
        $dbHandle = $this->_getDBHandle();
        if (!is_null($this->{Model_Fields_ReservationHistoryDB::DB_COLUMN_ID})) {
            $dataObj = $dbHandle->getById($this->{Model_Fields_ReservationHistoryDB::DB_COLUMN_ID});
        }

        if (!empty($dataObj)) {
            $this->assignModel($dataObj);
            $this->_setSeason();
            $this->_setField();
            $this->_setTeam();
            $this->_setCoach();
            return TRUE;
        }

        return FALSE;
    }

    /**
     * @brief: Set the season member variable if not already set
     */
    private function _setSeason() {
        if (!isset($this->m_season)) {
            $this->m_season = Model_Fields_Season::LookupById($this->{Model_Fields_ReservationHistoryDB::DB_COLUMN_SEASON_ID});
        }
    }

    /**
     * @brief: Set the field member variable if not already set
     */
    private function _setField() {
        if (!isset($this->m_field)) {
            $this->m_field = Model_Fields_Field::LookupById($this->{Model_Fields_ReservationHistoryDB::DB_COLUMN_FIELD_ID});
        }
    }

    /**
     * @brief: Set the team member variable if not already set
     */
    private function _setTeam() {
        if (!isset($this->m_team)) {
            $this->m_team = Model_Fields_Team::LookupById($this->{Model_Fields_ReservationHistoryDB::DB_COLUMN_TEAM_ID});
        }
    }

    /**
     * @brief: Set the coach member variable if not already set
     */
    private function _setCoach() {
        if (!isset($this->m_coach)) {
            $this->m_coach = Model_Fields_Coach::LookupById($this->{Model_Fields_ReservationHistoryDB::DB_COLUMN_COACH_ID});
        }
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
     * @brief: Get and instance of this object from databaes data.
     *
     * @param $dataObject - data object representing the content of the object
     * @param $season - Model_Fields_Season instance
     * @param $field - Model_Fields_Field instance
     * @param $team - Model_Fields_Team instance
     *
     * @return Model_Fields_ReservationHistory
     */
    public static function GetInstance($dataObject, $season = NULL, $field = NULL, $team = NULL, $coach = NULL) {
        $reservation = new Model_Fields_ReservationHistory(
            $season,
            $field,
            $team,
            $coach,
            $dataObject->{Model_Fields_ReservationHistoryDB::DB_COLUMN_ID},
            $dataObject->{Model_Fields_ReservationHistoryDB::DB_COLUMN_SEASON_ID},
            $dataObject->{Model_Fields_ReservationHistoryDB::DB_COLUMN_FIELD_ID},
            $dataObject->{Model_Fields_ReservationHistoryDB::DB_COLUMN_TEAM_ID},
            $dataObject->{Model_Fields_ReservationHistoryDB::DB_COLUMN_COACH_ID},
            $dataObject->{Model_Fields_ReservationHistoryDB::DB_COLUMN_START_TIME},
            $dataObject->{Model_Fields_ReservationHistoryDB::DB_COLUMN_END_TIME},
            $dataObject->{Model_Fields_ReservationHistoryDB::DB_COLUMN_DAYS_OF_WEEK},
            $dataObject->{Model_Fields_ReservationHistoryDB::DB_COLUMN_TYPE},
            $dataObject->{Model_Fields_ReservationHistoryDB::DB_COLUMN_CREATION_DATE}
        );

        $reservation->setLoaded();

        return $reservation;
    }

    /**
     * @brief: Create a new ReservationHistory
     *
     * @param $reservation - Model_Fields_Reservation instance
     * @param $type - A for Add, D for Delete
     *
     * @return Model_Fields_ReservationHistory
     * @throws AssertionException
     */
    public static function Create($reservation, $type) {
        precondition($type == Model_Fields_ReservationHistory::ADD or $type == Model_Fields_ReservationHistory::DELETE, "Error, type:$type is invalid, must be A or D");

        $dbHandle = new Model_Fields_ReservationHistoryDB();
        $dataObject = $dbHandle->create(
            $reservation->m_season,
            $reservation->m_field,
            $reservation->m_team,
            $reservation->m_team->m_coach,
            $reservation->startTime,
            $reservation->endTime,
            $reservation->daysOfWeek,
            $type);
        assertion(!empty($dataObject), "Unable to create ReservationHistory");

        return Model_Fields_ReservationHistory::GetInstance(
            $dataObject,
            $reservation->m_season,
            $reservation->m_field,
            $reservation->m_team,
            $reservation->m_team->m_coach);
    }

    /**
     * @brief: Get Model_Fields_ReservationHistory instance for the specified ReservationHistory identifier
     *
     * @param bigint $reservationHistoryId: Unique ReservationHistory identifier
     *
     * @return Model_Fields_ReservationHistory
     */
    public static function LookupById($reservationHistoryId) {
        $dbHandle = new Model_Fields_ReservationHistoryDB();
        $dataObject = $dbHandle->getById($reservationHistoryId);
        assertion(!empty($dataObject), "ReservationHistory row for id: '$reservationHistoryId' not found");

        return Model_Fields_ReservationHistory::GetInstance($dataObject);
    }

    /**
     * @brief: Get Model_Fields_ReservationHistory instances for the specified season
     *
     * @param $season - Model_Fields_Season instance
     *
     * @return Model_Fields_ReservationHistory array | empty array
     */
    public static function LookupBySeason($season) {
        $dbHandle = new Model_Fields_ReservationHistoryDB();
        $dataObjects = $dbHandle->getBySeason($season);
        $reservations = array();

        foreach ($dataObjects as $dataObject) {
            $reservations[] = Model_Fields_ReservationHistory::GetInstance($dataObject, $season, NULL, NULL);
        }

        return $reservations;
    }

    /**
     * @brief: Get Model_Fields_ReservationHistory instances for the specified team and season
     *
     * @param $season - Model_Fields_Season instance
     * @param $team - Model_Fields_Team instance
     *
     * @return Model_Fields_ReservationHistory array | empty array
     */
    public static function LookupByTeam($season, $team) {
        $dbHandle = new Model_Fields_ReservationHistoryDB();
        $dataObjects = $dbHandle->getByTeam($season, $team);
        $reservations = array();

        foreach ($dataObjects as $dataObject) {
            $reservations[] = Model_Fields_ReservationHistory::GetInstance($dataObject, $season, NULL, $team);
        }

        return $reservations;
    }

    /**
     * @brief: Get Model_Fields_ReservationHistory instance for the specified ReservationHistory field and season
     *
     * @param $season - Model_Fields_Season instance
     * @param $field - Model_Fields_Field instance
     *
     * @return Model_Fields_ReservationHistory array
     */
    public static function LookupByField($season, $field) {
        $dbHandle = new Model_Fields_ReservationHistoryDB();
        $dataObjects = $dbHandle->getByField($season, $field);
        $reservations = array();

        foreach ($dataObjects as $dataObject) {
            $reservations[] = Model_Fields_ReservationHistory::GetInstance($dataObject, $season, $field);
        }

        return $reservations;
    }

    /**
     * @brief: Get Model_Fields_ReservationHistory instances for the specified ReservationHistory coach and season
     *
     * @param $season - Model_Fields_Season instance
     * @param $coach - Model_Fields_Coach instance
     *
     * @return Model_Fields_ReservationHistory array | empty array
     */
    public static function LookupByCoach($season, $coach) {
        $dbHandle = new Model_Fields_ReservationHistoryDB();
        $dataObjects = $dbHandle->getByCoach($season, $coach);
        $reservations = array();

        foreach ($dataObjects as $dataObject) {
            $reservations[] = Model_Fields_ReservationHistory::GetInstance($dataObject, $season, NULL, NULL, $coach);
        }

        return $reservations;
    }

    /**
     * @brief: Delete all reservation histories for a season
     *
     * @param $season - Model_Fields_Season instance
     */
    public static function Delete($season) {
        $reservations = Model_Fields_ReservationHistory::LookupBySeason($season);
        foreach ($reservations as $reservation) {
            $reservation->_delete();
        }
    }

    /**
     * @brief: Delete by Identifier
     *
     * @param $reservationId - Model_Fields_ReservationHistory identifier
     */
    public static function DeleteById($reservationId) {
        $reservation = Model_Fields_ReservationHistory::LookupById($reservationId);
        $reservation->_delete();
    }
}