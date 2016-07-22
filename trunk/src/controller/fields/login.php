<?php

/**
 * Class Controller_Login
 *
 * @brief Get user to login to an existing account.
 */
class Controller_Fields_Login extends Controller_Fields_Base {
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
                $this->m_email = $this->getPostAttribute(
                    Model_Fields_CoachDB::DB_COLUMN_EMAIL,
                    '* Email Address is required'
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
                $this->_login();
                break;

            case View_Base::SIGN_OUT:
                $this->signOut();
                $view = new View_Fields_Login($this, View_Base::WELCOME_PAGE);
                $view->displayPage();
                break;

            case View_Base::CREATE_ACCOUNT:
                $view = new View_Fields_CreateAccount($this, View_Base::WELCOME_PAGE);
                $view->displayPage();
                break;

            case View_Base::SIGN_IN:
            default:
                $view = new View_Fields_Login($this);
                $view->displayPage();
                break;
        }
    }

    /**
     * @brief Login to existing account and then transition to show reservation page
     */
    private function _login() {
        $this->m_division = Model_Fields_Division::LookupById($this->m_divisionId);
        $this->m_coach = Model_Fields_Coach::LookupByEmail($this->m_season, $this->m_division, $this->m_email, FALSE);

        if (View_Fields_Base::REQUIRE_PASSWORD and isset($this->m_coach) and $this->m_coach->password != $this->m_password) {
            $this->_reset();
            $this->m_password = "* Incorrect password - try again";
            $view = new View_Fields_Login($this);
            $view->displayPage();
            return;
        }

        if (!isset($this->m_coach)) {
            $this->_reset();
            $this->m_email = "* Incorrect email - try again<br>or you need to create an account.<br>Coaches must create an account at<br>the beginning of each season;<br>All-Stars is a new season";
            $view = new View_Fields_Login($this);
            $view->displayPage();
            return;
        }

        // Get coaches team.  If the team does not exist then the
        // most likely reason is that the selected the wrong gender.
        // Otherwise, there is a major bug or someone manually deleted the team
        // from the database.
        $this->m_team = Model_Fields_Team::LookupByCoach($this->m_coach, $this->m_gender);
        if (!isset($this->m_team)) {
            $loginErrorMessage = "ERROR: No team found for coach: " . $this->m_coach->name . " " . $this->m_division->name . "-" . $this->m_gender;
            $loginErrorMessage .= "<br>Try again or create an account";

            $this->_reset();
            $this->m_loginErrorMessage = $loginErrorMessage;
            $view = new View_Fields_Login($this);
            $view->displayPage();
            return;
        }

        // Create session for coach
        $this->createSession($this->m_coach->id, Model_Fields_Session::COACH_USER_TYPE, $this->m_team->id);

        // If reservation(s) found then show reservation(s); otherwise go to select facility
        $reservations = $this->getReservationsForTeam();
        if (count($reservations) > 0) {
            $this->m_filterTeamId = $this->m_team->id;
            $view = new View_Fields_ShowReservation($this);
        } else {
            $view = new View_Fields_SelectField($this);
        }

        $view->displayPage();
    }

    /**
     * @brief Navigate to the create account page
     */
    private function _createAccount() {
        $this->m_creatingAccount = TRUE;
        $view = new View_Fields_Welcome($this);
        $view->displayPage();
    }
}