<?php

/**
 * Class Controller_Welcome
 *
 * @brief Get user to create an account or login to an existing account.
 */
class Controller_Fields_Welcome extends Controller_Fields_Base {
    public function __construct() {
        parent::__construct();

        if (isset($_SERVER['REQUEST_METHOD']) and $_SERVER['REQUEST_METHOD'] == 'POST') {
            if ($this->m_operation == View_Base::SUBMIT) {
                $this->m_divisionId = $this->getPostAttribute(
                    Model_Fields_TeamDB::DB_COLUMN_DIVISION_ID,
                    '* Division is required'
                );
                $this->m_gender = $this->getPostAttribute(
                    Model_Fields_TeamDB::DB_COLUMN_GENDER,
                    '* Gender is required'
                );
                $this->m_name = $this->getPostAttribute(Model_Fields_CoachDB::DB_COLUMN_NAME, '* Name is required');
                $this->m_email = $this->getPostAttribute(
                    Model_Fields_CoachDB::DB_COLUMN_EMAIL,
                    '* Email Address is required'
                );
                $this->m_phone = $this->getPostAttribute(
                    Model_Fields_CoachDB::DB_COLUMN_PHONE,
                    '* Phone Number is required'
                );
                $this->m_password = $this->getPostAttribute(
                    Model_Fields_CoachDB::DB_COLUMN_PASSWORD,
                    '* Password is required'
                );
            }
        }
    }

    /**
     * @brief On GET, render the page to ask user to Create account or Login.
     *        On POST, complete login or create account
     */
    public function process() {
        switch ($this->m_operation) {
            case View_Base::SUBMIT:
                $this->_createAccount();
                break;

            default:
                // If session not found then go to welcome page
                // If facility not selected then go to select facility page
                // else go to show reservation page
                if (!$this->m_isAuthenticated) {
                    $this->m_creatingAccount = TRUE;
                    $view = new View_Fields_Welcome($this);
                } elseif ($this->m_facility != NULL) {
                    $view = new View_Fields_ShowReservation($this);
                } else {
                    $view = new View_Fields_SelectFacility($this);
                }

                $view->displayPage();
                break;
        }
    }

    /**
     * @brief Create new account and then transition to show reservation page
     */
    private function _createAccount() {
        $this->m_division = Model_Fields_Division::LookupById($this->m_divisionId);
        $this->m_coach = Model_Fields_Coach::LookupByEmail($this->m_season, $this->m_division, $this->m_email, FALSE);

        // If coach already exists then verify password
        if (isset($this->m_coach) and $this->m_coach->password != $this->m_password) {
            $this->_reset();
            $this->m_password = "* account already exists";
            $view = new View_Fields_Login($this);
            $view->displayPage();
            return;
        }

        // Create coach if not found
        if (!isset($this->m_coach)) {
            $this->m_coach = Model_Fields_Coach::Create($this->m_season, $this->m_division, $this->m_name, $this->m_email, $this->m_phone, $this->m_password);
        }

        // Get the coaches team, create if not found
        $this->m_team = Model_Fields_Team::LookupByCoach($this->m_coach, $this->m_gender);
        if (!isset($this->m_team)) {
            $this->m_team = Model_Fields_Team::Create($this->m_division, $this->m_coach, $this->m_gender, '');
        }

        // Create session for coach
        $this->createSession($this->m_coach->id, Model_Fields_Session::COACH_USER_TYPE, $this->m_team->id);

        $view = new View_Fields_SelectFacility($this);
        $view->displayPage();
    }

    /**
     * @brief Login to existing account and then transition to show reservation page
     */
    private function _login() {
        $view = new View_Fields_Login($this);
        $view->displayPage();
    }
}