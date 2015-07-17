<?php

/**
 * Class Controller_Login
 *
 * @brief Get user to login to an existing account.
 */
class Controller_Login extends Controller_Base {
    public function __construct() {
        parent::__construct();

        if (isset($_SERVER['REQUEST_METHOD']) and $_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->m_email = $this->getPostAttribute(
                Model_Fields_CoachDB::DB_COLUMN_EMAIL,
                '* Email Address is required'
            );
            $this->m_password = $this->getPostAttribute(
                Model_Fields_CoachDB::DB_COLUMN_PASSWORD,
                '* Password is required'
            );
            $this->m_divisionId = $this->getPostAttribute(
                Model_Fields_TeamDB::DB_COLUMN_DIVISION_ID,
                '* Division is required'
            );
            $this->m_teamNumber = $this->getPostAttribute(
                Model_Fields_TeamDB::DB_COLUMN_TEAM_NUMBER,
                '* Team Number is required'
            );

            $this->m_teamName = $this->getPostAttribute(Model_Fields_TeamDB::DB_COLUMN_NAME, '* Team Name is required');
        }
    }

    /**
     * @brief On GET, render the page to ask user to Create account or Login.
     *        On POST, complete login or create account
     */
    public function process() {
        switch ($this->m_operation) {
            case View_Base::LOGIN:
                $this->_login();
                break;

            case View_Base::CREATE_ACCOUNT:
                $this->_createAccount();
                break;

            default:
                $view = new View_Login($this);
                $view->displayPage();
                break;
        }
    }

    /**
     * @brief Login to existing account and then transition to show reservation page
     */
    private function _login() {
        $this->m_division = Model_Fields_Division::LookupById($this->m_divisionId);
        $this->m_team = Model_Fields_Team::LookupByNumber($this->m_division, $this->m_teamNumber);
        $this->m_coach = Model_Fields_Coach::LookupByEmail($this->m_team, $this->m_email);

        if (isset($this->m_coach) and $this->m_coach->password != $this->m_password) {
            $this->_reset();
            $this->m_password = "* Incorrect password - try again";
            $view = new View_Login($this);
            $view->displayPage();
            return;
        }

        // Create session for coach
        $this->createSession($this->m_coach->id, Model_Fields_Session::COACH_USER_TYPE);

        // If reservation(s) found then show reservation(s); otherwise go to select facility
        $reservations = $this->getReservationsForTeam();
        if (count($reservations) > 0) {
            $view = new View_ShowReservation($this);
        } else {
            $view = new View_SelectFacility($this);
        }

        $view->displayPage();
    }

    /**
     * @brief Navigate to the create account page
     */
    private function _createAccount() {
        $view = new View_Welcome($this);
        $view->displayPage();
    }
}