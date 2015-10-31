<?php

/**
 * Class Controller_Admin_Home
 *
 * @brief Get user to login to an practice field coordinator.
 */
class Controller_Admin_Home extends Controller_Admin_Base {
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
        $view = new View_Admin_Home($this);
        $view->displayPage();
    }

    /**
     * @brief Login to existing account and then transition to show reservation page
     */
    private function _login() {
        $this->m_coordinator = Model_Fields_PracticeFieldCoordinator::LookupByEmail($this->m_league, $this->m_email, FALSE);

        // Verify coordinator found, then verify password, then create session
        if (!isset($this->m_coordinator)) {
            $this->_reset();
            $this->m_email = "* Incorrect email - try again";
        } else if ($this->m_coordinator->password != $this->m_password) {
            $this->_reset();
            $this->m_password = "* Incorrect password - try again";
        } else {
            $this->createSession($this->m_coordinator->id, Model_Fields_Session::PRACTICE_FIELD_COORDINATOR_USER_TYPE, 0);
        }
    }
}