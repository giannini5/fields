<?php

use \DAG\Domain\Schedule\Coordinator;

/**
 * Class Controller_Base
 *
 * @brief Encapsulates everything that is common for the various controllers.
 *        Derived classes must implement all abstract method.
 */
abstract class Controller_Fields_Base extends Controller_Base
{
    # Attributes constructed from League
    public $m_genders;

    # Attributes constructed from POST
    public $m_phone;
    public $m_gender;
    public $m_divisionId;
    public $m_teamId;
    public $m_facility;
    public $m_field;
    public $m_fieldId;
    public $m_startTime;
    public $m_endTime;
    public $m_daysSelected;
    public $m_reservationId;
    public $m_filterLocationId;
    public $m_filterFacilityId;
    public $m_filterDivisionId;
    public $m_filterTeamId;

    # Attributes constructed from session
    /** @var Model_Fields_Coach */
    public $m_coach;
    /** @var Model_Fields_Team  */
    public $m_team;
    /** @var Model_Fields_Division  */
    public $m_division;
    /** @var Model_Fields_Reservation[] */
    public $m_reservations;

    # Other attributes constructed from above based on controller
    public $m_creatingAccount;
    public $m_createReservationError;
    public $m_reservationConfirmationMessage;
    public $m_loginErrorMessage;

    public function __construct()
    {
        $this->_reset();
        parent::__construct(self::SESSION_FIELD_COOKIE, Coordinator::COACH_USER_TYPE);

        $this->m_genders = array();

        $this->_getGenders();

        if (isset($_SERVER['REQUEST_METHOD']) and $_SERVER['REQUEST_METHOD'] == 'POST') {
            $facilityId = $this->getPostAttribute(View_Base::FACILITY_ID, NULL, false);
            if ($facilityId != NULL) {
                $this->m_facility = Model_Fields_Facility::LookupById($facilityId);
            }
        }

        if (isset($this->m_session) and $this->m_session->userType == Coordinator::COACH_USER_TYPE) {
            $this->m_coach = Model_Fields_Coach::LookupById($this->m_session->userId);
            $this->m_division = Model_Fields_Division::LookupById($this->m_coach->divisionId);
            $this->m_team = Model_Fields_Team::LookupById($this->m_session->teamId);
            $this->_getReservations();
        }
    }

    /**
     * @brief Get the header button to show:
     *          signIn if user is creating an account
     *          signOut if user is already authenticated
     *          createAccount if user is not creating account and not authenticated
     *
     * @return string Button to show, SIGN_IN, SIGN_OUT, CREATE_ACCOUNT
     */
    public function getHeaderButtonToShow() {
        if ($this->m_isAuthenticated) {
            return View_Base::SIGN_OUT;
        } else if (!$this->m_season->loginAllowed) {
            return View_Base::NO_BUTTON;
        } elseif ($this->m_creatingAccount or $this->m_operation == View_Base::CREATE_ACCOUNT) {
            return View_Base::SIGN_IN;
        } elseif ($this->m_operation == View_Base::SIGN_IN) {
            return View_Base::CREATE_ACCOUNT;
        }

        return View_Base::CREATE_ACCOUNT;
    }

    /**
     * @brief Get list of genders for selector
     */
    private function _getGenders() {
        $this->m_genders['B'] = 'Boys';
        $this->m_genders['G'] = 'Girls';
    }

    /**
     * @brief Initialize m_reservations, m_field and m_facility
     */
    protected function _getReservations()
    {
        $this->m_reservations = Model_Fields_Reservation::LookupByTeam($this->m_season, $this->m_team, FALSE);
        if (count($this->m_reservations) > 0
            and !isset($this->m_field)
            and !isset($this->m_facility)) {
            // Populate field and facility from first reservation
            // This will likely come back to bite me!!!
            $this->m_field = $this->m_reservations[0]->m_field;
            $this->m_facility = $this->m_field->m_facility;
        }
    }

    /**
     * @brief Set isAuthenticated and update session as necessary
     */
    protected function setAuthentication() {
        if ($this->m_coach != NULL &&
            $this->m_division != NULL &&
            $this->m_team != NULL) {
            $this->_setAuthentication();
        }
    }

    /**
     * @brief Reset attributes to default values
     */
    protected function _reset() {
        parent::_reset();

        $this->m_phone = '';
        $this->m_divisionId = '';
        $this->m_gender = '';
        $this->m_fieldId = NULL;
        $this->m_startTime = NULL;
        $this->m_endTime = NULL;
        $this->m_daysSelected = array();
        $this->m_reservationId = NULL;
        $this->m_filterFacilityId = 0;
        $this->m_filterDivisionId = 0;
        $this->m_filterLocationId = 0;
        $this->m_filterTeamId = 0;

        $this->m_missingAttributes = 0;
        $this->m_operation = '';
        $this->m_session = null;

        $this->m_division = null;
        $this->m_team = null;
        $this->m_coach = null;
        $this->m_reservations = array();

        $this->m_creatingAccount = FALSE;
        $this->m_createReservationError = '';
        $this->m_reservationConfirmationMessage = '';
        $this->m_loginErrorMessage = '';
    }

    /**
     * @brief Return the name of the coach or empty string if not authenticated
     *
     * @return string : Name of coach or empty string
     */
    public function getCoachName() {
        if ($this->m_isAuthenticated) {
            return $this->m_coach->name;
        }

        return '';
    }

    /**
     * @brief Return the name of the division or empty string if not authenticated
     *
     * @return string : Name of division or empty string
     */
    public function getDivisionName() {
        if ($this->m_isAuthenticated) {
            return $this->m_division->name;
        }

        return '';
    }

    /**
     * @brief Return the gender of the team or empty string if not authenticated
     *
     * @return string : Gender of team or empty string
     */
    public function getGender() {
        if ($this->m_isAuthenticated) {
            return $this->m_team->gender;
        }

        return '';
    }

    /**
     * @param bool $getFromScratch
     * @return Model_Fields_Reservation[]
     */
    public function getReservationsForTeam($getFromScratch = false) {
        if ($this->m_isAuthenticated) {
            if (count($this->m_reservations) == 0 or $getFromScratch) {
                $this->_getReservations();
            }

            return $this->m_reservations;
        }

        return array();
    }
}