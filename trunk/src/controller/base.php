<?php

/**
 * Class Controller_Base
 *
 * @brief Encapsulates everything that is common for the various controllers.
 *        Derived classes must implement all abstract method.
 */
abstract class Controller_Base
{
    # Session Cookie name
    protected $m_cookieName;

    # Attributes constructed from League
    public $m_league;
    public $m_season;
    public $m_divisions = [];

    # Attributes constructed from POST
    public $m_operation;

    # Session to avoid login with each page load
    protected $m_session;

    # Other attributes constructed from above based on controller
    public $m_missingAttributes;
    public $m_isAuthenticated;
    public $m_errorString = '';
    public $m_messageString = '';

    # Filters
    public $m_filterFacilityId;
    public $m_filterDivisionId;
    public $m_filterLocationId;
    public $m_filterTeamId;
    public $m_filterCoachId;

    # Popup or regular page
    public $m_isPopup = false;

    public function __construct($cookieName, $populateDivisions = true)
    {
        $this->m_cookieName = $cookieName;

        $this->_reset();

        $this->_getLeague();
        $this->_getSeason();

        if ($populateDivisions) {
            $this->_getDivisions();
        }

        if (isset($_SERVER['REQUEST_METHOD']) and $_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->m_operation = $this->getPostAttribute(View_Base::SUBMIT, '', FALSE);
        }
    }

    /**
     * @brief Get league
     */
    protected function _getLeague() {
        $this->m_league = Model_Fields_League::LookupByName('AYSO Region 122');
    }

    /**
     * @brief Get season
     */
    protected function _getSeason() {
        $this->m_season = Model_Fields_Season::GetEnabledSeason($this->m_league, FALSE);
    }

    /**
     * @brief Get list of divisions for selector
     */
    protected function _getDivisions() {
        $this->m_divisions = Model_Fields_Division::GitList($this->m_league);
    }

    /**
     * @brief Return a list of teams ordered by Division and gender
     *        Only return teams for current season
     *
     * @return Model_Fields_Team[]
     */
    public function getTeams()
    {
        $teams = [];
        foreach ($this->m_divisions as $division) {
            $divisionTeams = Model_Fields_Team::GetTeams($division);
            foreach ($divisionTeams as $divisionTeam) {
                $coach = Model_Fields_Coach::LookupById($divisionTeam->coachId);
                if ($coach->seasonId == $this->m_season->id) {
                    $teams[] = $divisionTeam;
                }
            }
        }

        return $teams;
    }

    /**
     * @brief Returns the found POST attribute's value.  Returns default value
     *        if attribute not found.  Keeps track of count of attributes not found in
     *        the $m_missingAttributes counter.
     *
     * @param $attributeName - Name of the POST attribute
     * @param $defaultValue - Default value returned if POST attribute not found
     * @param $rememberIfMissing - Increments m_missingAttributes if TRUE
     * @param $isNumeric - TRUE if the attribute is numeric; FALSE by default
     * @param $errorMessage - Defaults to empty, if set and remembering errors then concat controller's error string
     *
     * @return mixed - Value associated with attribute name or $defaultValue if attribute not found
     */
    protected function getPostAttribute($attributeName, $defaultValue, $rememberIfMissing = TRUE, $isNumeric = FALSE, $errorMessage = '') {
        if (isset($_POST[$attributeName])) {
            if (!empty($_POST[$attributeName]) or $isNumeric) {
                return $_POST[$attributeName];
            }
        }

        if ($rememberIfMissing) {
            $this->setErrorString($errorMessage);
        }

        return $defaultValue;
    }

    /**
     * @brief Returns the found REQUEST attribute's value.  Returns default value
     *        if attribute not found.
     *
     * @param string    $attributeName  - Name of the POST attribute
     * @param mixed     $defaultValue   - Default value returned if POST attribute not found
     *
     * @return mixed    Value associated with attribute name or $defaultValue if attribute not found
     */
    protected function getRequestAttribute($attributeName, $defaultValue) {
        if (isset($_REQUEST[$attributeName])) {
            return $_REQUEST[$attributeName];
        }

        return $defaultValue;
    }

    /**
     * @brief Returns true if checkbox checked; false otherwise
     *
     * @param $attributeName - Name of the POST attribute checkbox
     * @param $defaultValue - Default value returned if POST attribute not found or not set
     *
     * @return bool
     */
    protected function getPostCheckboxAttribute($attributeName, $defaultValue)
    {
        if (isset($_POST[$attributeName])) {
            if (!empty($_POST[$attributeName])) {
                return true;
            }
        }

        return $defaultValue;
    }

    /**
     * @brief Increment the number errors found and set the error string for display.
     *
     * @param string $errorMessage - Error message to be displayed.  If empty then no message is displayed.
     */
    protected function setErrorString($errorMessage)
    {
        $this->m_missingAttributes += 1;
        if ($errorMessage != '') {
            $this->m_errorString .= empty($this->m_errorString) ? $errorMessage : "<br>$errorMessage";
        }
    }

    /**
     * @brief Returns the found POST attribute's array.  Empty array if POST attribute not set.
     *
     * @param $attributeName - Name of the POST attribute
     *
     * @return array
     */
    protected function getPostAttributeArray($attributeName)
    {
        if (isset($_POST[$attributeName])) {
            return $_POST[$attributeName];
        }

        return array();
    }

    /**
     * @brief Returns the found GET attribute's value.  Returns default value
     *        if attribute not found.
     *
     * @param $attributeName - Name of the GET attribute
     * @param $defaultValue - Default value returned if GET attribute not found
     *
     * @return mixed - Value associated with attribute name or $defaultValue if attribute not found
     */
    protected function getGetAttribute($attributeName, $defaultValue)
    {
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
    protected function createSession($userId, $userType, $teamId)
    {
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
    protected function _setAuthentication()
    {
        if (isset($this->m_session) and $this->m_session->isValid()) {
            $this->m_session->renew();
            $this->m_isAuthenticated = TRUE;

            setcookie($this->m_cookieName, $this->m_session->id, time()+60*60*24*7, "/"); // Expire in 7 days
        }
    }

    /**
     * @brief Reset attributes to default values
     */
    protected function _reset()
    {
        $this->m_missingAttributes  = 0;
        $this->m_operation          = '';
        $this->m_session            = null;

        $this->m_isAuthenticated    = FALSE;
        $this->m_errorString        = '';
    }

    /**
     * @brief: Get authenticated - either login or create an account.  If operation not specified then
     *         show the login view.
     *
     * @param $navigationSelection - Selection to highlight in the navigation bar
     */
    protected function getAuthenticated($navigationSelection)
    {
        switch ($this->m_operation) {
            case View_Base::CREATE_ACCOUNT:
                $view = new View_Fields_CreateAccount($this, $navigationSelection);
                $view->displayPage();
                break;

            default:
                $view = new View_Fields_Login($this, $navigationSelection);
                $view->displayPage();
                break;
        }
    }

    /**
     * @brief Get list of facilities
     *
     * @return Model_Fields_Facility[] - array of facilities; empty array if no facilities found
     */
    public function getFacilities()
    {
        $facilities = Model_Fields_Facility::LookupByLeague($this->m_league);

        return $facilities;
    }

    /**
     * @brief Get list of locations
     *
     * @return Model_Fields_Location[] - Array of locations; empty array if no locations found
     */
    public function getLocations()
    {
        $locations = Model_Fields_Location::GetLocations($this->m_league->id);

        return $locations;
    }

    /**
     * @brief Get list of fields for a specified facility
     *
     * @param $facility - Model_Fields_Facility instance
     * @param bool $enabledOnly - If TRUE then get enabled fields only; otherwise get all fields
     *
     * @return Model_Fields_Field[] - Model_Fields_Field instances; empty array if no fields found
     */
    public function getFields($facility, $enabledOnly = FALSE)
    {
        $fields = Model_Fields_Field::LookupByFacility($facility, $enabledOnly);

        return $fields;
    }

    /**
     * @brief Return this sessions identifier
     *
     * @return int - Session identifier; 0 if no session enabled
     */
    public function getSessionId()
    {
        if (isset($this->m_session)) {
            return $this->m_session->id;
        }

        return 0;
    }

    /**
     * @brief Return TRUE if the day was selected; FALSE otherwise
     *
     * @param $day - ViewBase::MONDAY, ...
     *
     * @return bool TRUE if day was selected; FALSE otherwise
     */
    protected function _isDaySelected($day)
    {
        $postValue = $this->getPostAttribute($day, NULL, FALSE);
        return isset($postValue);
    }

    /**
     * @brief Return a comma separated list of days selected in the reservation.
     *
     * @param $reservation
     *
     * @return string : comma separated list of days string: "Monday, Tuesday, ..., Friday"
     */
    public function getDaysSelectedString($reservation)
    {
        $daysSelected = '';
        for ($i = 0; $i < 7; ++$i) {
            if ($reservation->isDaySelected($i)) {
                if (!empty($daysSelected)) {
                    $daysSelected .= ", ";
                }
                $daysSelected .= $this->_getDayOfWeek($i);
            }
        }

        return $daysSelected;
    }

    /**
     * @brief Return the string version of the passed in integer
     *
     * @param int $day - 0 is Monday, 6 is Sunday
     *
     * @return string (Monday, Tuesday, ..., Sunday)
     */
    protected function _getDayOfWeek($day)
    {
        switch ($day) {
            case 0:
                return 'Monday';
            case 1:
                return 'Tuesday';
            case 2:
                return 'Wednesday';
            case 3:
                return 'Thursday';
            case 4:
                return 'Friday';
            case 5:
                return 'Saturday';
            case 6:
                return 'Sunday';
            default:
                return 'ERROR';
        }
    }

    /**
     * @brief Return a list of reservations based on filter
     *
     * @param $filterFacilityId - Only include this facilities if filter enabled
     * @param $filterDivisionId - Only include this divisions if filter enabled
     * @param $filterTeamId - Only include this team if filter enabled
     *
     * @return array $reservations
     */
    public function getFilteredReservations($filterFacilityId, $filterDivisionId, $filterTeamId)
    {
        $reservations = array();

        foreach ($this->m_divisions as $division) {
            // Check division filter
            if ($filterDivisionId != 0 and $filterDivisionId != $division->id) {
                continue;
            }

            $teams = Model_Fields_Team::GetTeams($division);

            foreach ($teams as $team) {
                // Check team filter
                if ($filterTeamId != 0 and $filterTeamId != $team->id) {
                    continue;
                }

                $teamReservations = Model_Fields_Reservation::LookupByTeam($this->m_season, $team, FALSE);
                foreach ($teamReservations as $teamReservation) {
                    // Check facility filter
                    if ($filterFacilityId != 0 and $filterFacilityId != $teamReservation->m_field->m_facility->id) {
                        continue;
                    }

                    $reservations[] = $teamReservation;
                }
            }

        }

        return $reservations;
    }


    /**
     * @brief Get list of reservations for field
     *
     * @param: $field - Field to get reservations for
     * @return array|Model_Fields_Reservation
     */
    public function getReservationsForField($field) {
        if ($this->m_isAuthenticated) {
            $reservations = Model_Fields_Reservation::LookupByField($this->m_season, $field, FALSE);
            return $reservations;
        }

        return array();
    }

    abstract public function process();
}