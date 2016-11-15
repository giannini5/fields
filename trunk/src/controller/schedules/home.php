<?php

/**
 * Class Controller_Schedules_Home
 *
 * @brief Get user to login to a game schedule coordinator.
 */
class Controller_Schedules_Home extends Controller_Schedules_Base {
    public function __construct() {
        parent::__construct();

        if (isset($_SERVER['REQUEST_METHOD']) and $_SERVER['REQUEST_METHOD'] == 'POST') {
            if ($this->m_operation == View_Base::SUBMIT) {
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
                break;

            case View_Base::SIGN_IN:
            default:
                break;
        }

        // Display Home page with error message if login failed
        // or successful login with next steps for administration
        $view = new View_Schedules_Home($this);
        $view->displayPage();
    }

    /**
     * @brief Login to existing account and then transition to show reservation page
     */
    private function _login() {
        try {
            $this->m_coordinator = \DAG\Orm\Schedule\ScheduleCoordinatorOrm::loadByLeagueIdAndEmail($this->m_league->id, $this->m_email);

            if ($this->m_coordinator->password != $this->m_password) {
                $this->_reset();
                $this->m_password = "* Incorrect password - try again";
            } else {
                $this->createSession($this->m_coordinator->id, Model_Fields_Session::SCHEDULE_COORDINATOR_USER_TYPE, 0);
            }
        } catch (\DAG\Framework\Orm\NoResultsException $e) {
            $this->_reset();
            $this->m_email = "* Incorrect email - try again";
        }
    }
}