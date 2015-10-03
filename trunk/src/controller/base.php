<?php

/**
 * Class Controller_Base
 *
 * @brief Encapsulates everything that is common for the various controllers.
 *        Derived classes must implement all abstract method.
 */
abstract class Controller_Base
{
    const SESSION_COOKIE = 'session';

    # Attributes constructed from League
    public $m_league;
    public $m_season;
    public $m_divisions;

    # Attributes constructed from POST
    public $m_operation;

    # Session to avoid login with each page load
    protected $m_session;

    # Other attributes constructed from above based on controller
    protected $m_missingAttributes;
    public $m_isAuthenticated;

    public function __construct()
    {
        $this->_reset();

        $this->m_divisions = array();

        $this->_getLeague();
        $this->_getSeason();
        $this->_getDivisions();

        if (isset($_SERVER['REQUEST_METHOD']) and $_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->m_operation = $this->getPostAttribute(View_Base::SUBMIT, '');
        }
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
     * @brief Returns the found GET attribute's value.  Returns default value
     *        if attribute not found.
     *
     * @param $attributeName - Name of the GET attribute
     * @param $defaultValue - Default value returned if GET attribute not found
     *
     * @return Value associated with attribute name or $defaultValue if attribute not found
     */
    protected function getGetAttribute($attributeName, $defaultValue) {
        if (isset($_REQUEST[$attributeName])) {
            return $_REQUEST[$attributeName];
        }

        return $defaultValue;
    }

    /**
     * @brief Creates a session for an authenticated user
     *
     * @param $userId - User identifier
     * @param $userType - Type of user (see Model_Fields_Session for types)
     * @param $teamId - Team identifier
     */
    protected function createSession($userId, $userType, $teamId) {
        // Lookup session and update timestamp if session already exists
        // else create a new session
        $this->m_session = Model_Fields_Session::LookupByUser($userId, $userType, $teamId, FALSE);
        if ($this->m_session == NULL) {
            $this->m_session = Model_Fields_Session::Create($userId, $userType, $teamId);
        } else {
            $this->m_session->renew();
        }

        $this->_setAuthentication();
    }

    /**
     * @brief Renew the session, set class to authenticated and create cookie so that
     *        visit to next page does not require authentication.
     */
    protected function _setAuthentication() {
        if (isset($this->m_session) and $this->m_session->isValid()) {
            $this->m_session->renew();
            $this->m_isAuthenticated = TRUE;

            setcookie(self::SESSION_COOKIE, $this->m_session->id, time()+60*60*24*7, "/"); // Expire in 7 days
        }
    }

    /**
     * @brief Reset attributes to default values
     */
    protected function _reset() {
        $this->m_missingAttributes = 0;
        $this->m_operation = '';
        $this->m_session = null;

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
     * @brief Get list of locations
     *
     * @return Array of locations; empty array if no locations found
     */
    public function getLocations() {
        $locations = Model_Fields_Location::GetLocations($this->m_league->id);

        return $locations;
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