<?php
/**
 * This file contains Model_Fields_Reservation
 */

/**
 * Model_Fields_Reservation class
 */
class Model_Fields_Reservation extends Model_Fields_Base implements SaveModelInterface {

    public $m_season;
    public $m_field;
    public $m_team;

    /**
     * @brief: Constructor
     *
     * @param $season - Model_Fields_Season instance
     * @param $field - Model_Fields_Field instance
     * @param $team - Model_Fields_Team instance
     * @param $id - unique identifier
     * @param $seasonId - unique season identifier
     * @param $fieldId - unique field identifier
     * @param $teamId - unique team identifier
     * @param string $startTime - start time of reservation
     * @param string $endTime - end time of reservation
     */
    public function __construct($season = NULL, $field = NULL, $team = NULL, $id = NULL, $seasonId = NULL, $fieldId = NULL, $teamId = NULL, $startTime = '', $endTime = '') {
        parent::__construct('Model_Fields_ReservationDB', Model_Base::AUTO_DECLARE_CLASS_VARIABLE_ON);

        $this->m_season = $season;
        $this->m_field = $field;
        $this->m_team = $team;
        $this->{Model_Fields_ReservationDB::DB_COLUMN_ID}   = $id;
        $this->{Model_Fields_ReservationDB::DB_COLUMN_SEASON_ID} = $seasonId;
        $this->{Model_Fields_ReservationDB::DB_COLUMN_FIELD_ID} = $fieldId;
        $this->{Model_Fields_ReservationDB::DB_COLUMN_TEAM_ID} = $teamId;
        $this->{Model_Fields_ReservationDB::DB_COLUMN_START_TIME} = $startTime;
        $this->{Model_Fields_ReservationDB::DB_COLUMN_END_TIME} = $endTime;

        $this->_setSeason();
        $this->_setField();
        $this->_setTeam();
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
        /** @var Model_Fields_ReservationDB $dbHandle */
        $dbHandle = $this->_getDBHandle();
        if (!is_null($this->{Model_Fields_ReservationDB::DB_COLUMN_ID})) {
            $dataObj = $dbHandle->getById($this->{Model_Fields_ReservationDB::DB_COLUMN_ID});
        } else if (!is_null($this->{Model_Fields_ReservationDB::DB_COLUMN_TEAM_ID})) {
            $dataObj = $dbHandle->getByTeam(
                NULL,
                $this->{Model_Fields_ReservationDB::DB_COLUMN_TEAM_ID},
                TRUE,
                $this->{Model_Fields_ReservationDB::DB_COLUMN_SEASON_ID});
        }

        if (!empty($dataObj)) {
            $this->assignModel($dataObj);
            $this->_setSeason();
            $this->_setField();
            $this->_setTeam();
            return TRUE;
        }

        return FALSE;
    }

    /**
     * @brief: Set the season member variable if not already set
     */
    private function _setSeason() {
        if (!isset($this->m_season)) {
            $this->m_season = Model_Fields_Season::LookupById($this->{Model_Fields_ReservationDB::DB_COLUMN_SEASON_ID});
        }
    }

    /**
     * @brief: Set the field member variable if not already set
     */
    private function _setField() {
        if (!isset($this->m_field)) {
            $this->m_field = Model_Fields_Field::LookupById($this->{Model_Fields_ReservationDB::DB_COLUMN_FIELD_ID});
        }
    }

    /**
     * @brief: Set the team member variable if not already set
     */
    private function _setTeam() {
        if (!isset($this->m_team)) {
            $this->m_team = Model_Fields_Team::LookupById($this->{Model_Fields_ReservationDB::DB_COLUMN_TEAM_ID});
        }
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
     * @brief: Get and instance of this object from databaes data.
     *
     * @param $dataObject - data object representing the content of the object
     * @param $season - Model_Fields_Season instance
     * @param $field - Model_Fields_Field instance
     * @param $team - Model_Fields_Team instance
     *
     * @return Model_Fields_Reservation
     */
    public static function GetInstance($dataObject, $season = NULL, $field = NULL, $team = NULL) {
        $reservation = new Model_Fields_Reservation(
            $season,
            $field,
            $team,
            $dataObject->{Model_Fields_ReservationDB::DB_COLUMN_ID},
            $dataObject->{Model_Fields_ReservationDB::DB_COLUMN_SEASON_ID},
            $dataObject->{Model_Fields_ReservationDB::DB_COLUMN_FIELD_ID},
            $dataObject->{Model_Fields_ReservationDB::DB_COLUMN_TEAM_ID},
            $dataObject->{Model_Fields_ReservationDB::DB_COLUMN_START_TIME},
            $dataObject->{Model_Fields_ReservationDB::DB_COLUMN_END_TIME});

        $reservation->setLoaded();

        return $reservation;
    }

    /**
     * @brief: Create a new Field
     *
     * @param $season - Model_Fields_Season instance
     * @param $field - Model_Fields_Field instance
     * @param $team - Model_Fields_Team instance
     * @param $startTime
     * @param $endTime - end time of reservation
     *
     * @return Model_Fields_Reservation
     * @throws AssertionException
     */
    public static function Create($season, $field, $team, $startTime, $endTime) {
        $dbHandle = new Model_Fields_ReservationDB();
        $dataObject = $dbHandle->create($season, $field, $team, $startTime, $endTime);
        assertion(!empty($dataObject), "Unable to create Reservation with startTime:'$startTime', endTime:'$endTime'");

        return Model_Fields_Reservation::GetInstance($dataObject, $season, $field, $team);
    }

    /**
     * @brief: Get Model_Fields_Reservation instance for the specified Reservation identifier
     *
     * @param bigint $reservationId: Unique Reservation identifier
     *
     * @return Model_Fields_Reservation
     */
    public static function LookupById($reservationId) {
        $dbHandle = new Model_Fields_ReservationDB();
        $dataObject = $dbHandle->getById($reservationId);
        assertion(!empty($dataObject), "Reservation row for id: '$reservationId' not found");

        return Model_Fields_Reservation::GetInstance($dataObject);
    }

    /**
     * @brief: Get Model_Fields_Reservation instance for the specified Reservation team and season
     *
     * @param $season - Model_Fields_Season instance
     * @param $team - Model_Fields_Team instance
     * @param bool $assertIfNotFound - If TRUE then assert object if found.  Otherwise return NULL when object not found
     *
     * @return Model_Fields_Reservation array | null
     * @throws AssertionException
     */
    public static function LookupByTeam($season, $team, $assertIfNotFound = TRUE) {
        $dbHandle = new Model_Fields_ReservationDB();
        $dataObjects = $dbHandle->getByTeam($season, $team);
        $reservations = array();

        if ($assertIfNotFound) {
            assertion(!empty($dataObjects), "Reservation row for team: '$team->name' not found");
        } else if (empty($dataObjects)) {
            return $reservations;
        }

        foreach ($dataObjects as $dataObject) {
            $reservations[] = Model_Fields_Reservation::GetInstance($dataObject, $season, NULL, $team);
        }

        return $reservations;
    }

    /**
     * @brief: Get Model_Fields_Reservation instance for the specified Reservation field and season
     *
     * @param $season - Model_Fields_Season instance
     * @param $field - Model_Fields_Field instance
     * @param bool $assertIfNotFound - If TRUE then assert object if found.  Otherwise return NULL when object not found
     *
     * @return Model_Fields_Reservation array
     * @throws AssertionException
     */
    public static function LookupByField($season, $field, $assertIfNotFound = TRUE) {
        $dbHandle = new Model_Fields_ReservationDB();
        $dataObjects = $dbHandle->getByField($season, $field);
        $reservations = array();

        if ($assertIfNotFound) {
            assertion(!empty($dataObjects), "Reservation row for field: '$field->name' not found");
        } else if (empty($dataObjects)) {
            return $reservations;
        }

        foreach ($dataObjects as $dataObject) {
            $reservations[] = Model_Fields_Reservation::GetInstance($dataObject, $season, $field, NULL);
        }

        return $reservations;
    }

    /**
     * @brief: Delete if exists
     *
     * @param $season - Model_Fields_Season instance
     * @param $field - Model_Fields_Field instance
     * @param $team - Model_Fields_Team instance
     */
    public static function Delete($season, $team) {
        $reservations = Model_Fields_Reservation::LookupByTeam($season, $team, FALSE);
        foreach ($reservations as $reservation) {
            $reservation->_delete();
        }
    }
}