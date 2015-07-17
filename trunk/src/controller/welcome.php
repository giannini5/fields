<?php

/**
 * Class Controller_Welcome
 *
 * @brief Get user to create an account or login to an existing account.
 */
class Controller_Welcome extends Controller_Base {
    public function __construct() {
        parent::__construct();

        if (isset($_SERVER['REQUEST_METHOD']) and $_SERVER['REQUEST_METHOD'] == 'POST') {
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
            $this->m_divisionId = $this->getPostAttribute(
                Model_Fields_TeamDB::DB_COLUMN_DIVISION_ID,
                '* Division is required'
            );
            $this->m_teamNumber = $this->getPostAttribute(
                Model_Fields_TeamDB::DB_COLUMN_TEAM_NUMBER,
                '* Team Number is required'
            );
        }
    }

    /**
     * @brief On GET, render the page to ask user to Create account or Login.
     *        On POST, complete login or create account
     */
    public function process() {
        switch ($this->m_operation) {
            case View_Base::CREATE_ACCOUNT:
                $this->_createAccount();
                break;

            case View_Base::LOGIN:
                $this->_login();
                break;

            default:
                $view = new View_Welcome($this);
                $view->displayPage();
                break;
        }
    }

    /**
     * @brief Create new account and then transition to show reservation page
     */
    private function _createAccount() {
        $this->m_division = Model_Fields_Division::LookupById($this->m_divisionId);
        $this->m_team = Model_Fields_Team::LookupByNumber($this->m_division, $this->m_teamNumber);
        $this->m_coach = Model_Fields_Coach::LookupByTeam($this->m_team, FALSE);

        if (isset($this->m_coach) and $this->m_coach->email != $this->m_email) {
            $this->_reset();
            $this->m_email = "* $this->m_coach->email is already coaching this team";
            $view = new View_Welcome($this);
            $view->displayPage();
            return;
        }

        if (isset($this->m_coach) and $this->m_coach->password != $this->m_password) {
            $this->_reset();
            $this->m_password = "* account already exists - password does not match";
            $view = new View_Login($this);
            $view->displayPage();
            return;
        }

        if (!isset($this->m_coach)) {
            $this->m_coach = Model_Fields_Coach::Create($this->m_team, $this->m_name, $this->m_email, $this->m_phone, $this->m_password);
        }

        // Create session for coach
        $this->createSession($this->m_coach->id, Model_Fields_Session::COACH_USER_TYPE);

        $view = new View_SelectFacility($this);
        $view->displayPage();
    }

    /**
     * @brief Login to existing account and then transition to show reservation page
     */
    private function _login() {
        $view = new View_Login($this);
        $view->displayPage();
    }
}