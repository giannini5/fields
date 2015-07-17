<?php

/**
 * Class Controller_Base
 *
 * @brief Encapsulates everything that is common for the various controllers.
 *        Derived classes must implement all abstract method.
 */
abstract class Controller_Base
{
    # Attributes constructed from League
    public $m_league;
    public $m_season;
    public $m_divisions;
    public $m_teamNumbers;

    # Attributes constructed from POST
    public $m_name;
    public $m_email;
    public $m_phone;
    public $m_password;
    public $m_divisionId;
    public $m_teamId;
    protected $m_operation;
    public $m_facility;

    # Attributes constructed from session
    protected $m_session;
    public $m_coach;
    public $m_team;
    public $m_division;

    # Other attributes constructed from above based on controller
    protected $m_missingAttributes;
    public $m_isAuthenticated;

    public function __construct()
    {
        $this->_reset();

        $this->m_divisions = array();
        $this->m_teamNumbers = array();

        $this->_getLeague();
        $this->_getSeason();
        $this->_getDivisions();
        $this->_getTeamNumbers();

        if (isset($_SERVER['REQUEST_METHOD']) and $_SERVER['REQUEST_METHOD'] == 'POST') {
            $sessionId = $this->getPostAttribute(View_Base::SESSION_ID, NULL);
            if ($sessionId != NULL) {
                $this->m_session = Model_Fields_Session::LookupById($sessionId);
            }

            $facilityId = $this->getPostAttribute(View_Base::FACILITY_ID, NULL);
            if ($facilityId != NULL) {
                $this->m_facility = Model_Fields_Facility::LookupById($facilityId);
            }

            $this->m_operation = $this->getPostAttribute(View_Base::SUBMIT, '');

            $this->_init();
        }

        $this->_setAuthentication();
    }

    /**
     * @brief Get league
     */
    private function _getLeague() {
        $this->m_league = Model_Fields_League::LookupByName('AYSO Region 122');
    }

    /**
     * @brief Get season
     */
    private function _getSeason() {
        $this->m_season = Model_Fields_Season::LookupByName($this->m_league, "Fall 2015");
    }

    /**
     * @brief Get list of divisions for selector
     */
    private function _getDivisions() {
        $this->m_divisions = Model_Fields_Division::GitList($this->m_league);
    }

    /**
     * @brief Get list of team numbers for selector
     * TODO turn this into a map of divisions to team numbers and pull from database
     */
    private function _getTeamNumbers() {
        $i = 1;
        while ($i <= 40) {
            $this->m_teamNumbers[$i] = $i;
            $i++;
        }
    }

    /**
     * @brief Initialize member variables from session
     */
    private function _init() {
        if ($this->m_session != NULL) {
            $this->m_coach = Model_Fields_Coach::LookupById($this->m_session->userId);
            $this->m_team = Model_Fields_Team::LookupById($this->m_coach->teamId);
            $this->m_division = Model_Fields_Division::LookupById($this->m_team->divisionId);
        }
    }

    /**
     * @brief Set isAuthenticated and update session as necessary
     */
    private function _setAuthentication() {
        $this->m_isAuthenticated = FALSE;

        if ($this->m_session != NULL &&
            $this->m_coach != NULL &&
            $this->m_division != NULL &&
            $this->m_team != NULL) {
            if ($this->m_session->isValid()) {
                $this->m_session->renew();
                $this->m_isAuthenticated = TRUE;
            }
        }

    }

    /**
     * @brief Returns the found POST attribute's value.  Returns default value
     *        if attribute not found.  Keeps track of count of attributes not found in
     *        the $m_missingAttributes counter.
     *
     * @param $attributeName - Name of the POST attribute
     * @param $defaultValue - Default value returned if POST attribute not found
     *
     * @return Value associated with attribute name or $defaultValue if attribute not found
     */
    protected function getPostAttribute($attributeName, $defaultValue) {
        if (isset($_POST[$attributeName])) {
            return $_POST[$attributeName];
        }

        $this->m_missingAttributes += 1;
        return $defaultValue;
    }

    /**
     * @brief Creates a session for an authenticated user
     *
     * @param $userId - User identifier
     * @param $userType - Type of user (see Model_Fields_Session for types)
     */
    protected function createSession($userId, $userType) {
        // Lookup session and update timestamp if session already exists
        // else create a new session
        $this->m_session = Model_Fields_Session::LookupByUser($userId, $userType, FALSE);
        if ($this->m_session == NULL) {
            $this->m_session = Model_Fields_Session::Create($userId, $userType);
        }
    }

    /**
     * @brief Get list of reservations for team
     */
    protected function getReservationsForTeam() {
        $reservations = Model_Fields_Reservation::LookupByTeam($this->m_season, $this->m_team, FALSE);

        return $reservations;
    }

    /**
     * @brief Reset attributes to default values
     */
    protected function _reset() {
        $this->m_name = '';
        $this->m_email = '';
        $this->m_phone = '';
        $this->m_password = '';
        $this->m_divisionId = '';
        $this->m_teamNumber = '';

        $this->m_missingAttributes = 0;
        $this->m_operation = '';
        $this->m_session = null;

        $this->m_division = null;
        $this->m_team = null;
        $this->m_coach = null;

        $this->m_isAuthenticated = FALSE;
    }

    /**
     * @brief Get list of facilities
     *
     * @return Array of facilities; empty array if no facilities found
     */
    public function getFacilities() {
        $facilities = Model_Fields_Facility::LookupByLeague($this->m_league);

        return $facilities;
    }

    /**
     * @brief Get list of fields for a specified facility
     *
     * @param $facility - Model_Fields_Facility instance
     *
     * @return Array of Model_Fields_Field instances; empty array if no fields found
     */
    public function getFields($facility) {
        $fields = Model_Fields_Field::LookupByFacility($facility);

        return $fields;
    }

    /**
     * @brief Return this sessions identifier
     *
     * @return Session identifier; 0 if no session established
     */
    public function getSessionId() {
        if (isset($this->m_session)) {
            return $this->m_session->id;
        }

        return 0;
    }

    abstract public function process();
}