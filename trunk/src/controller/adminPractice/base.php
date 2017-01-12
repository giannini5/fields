<?php

/**
 * Class Controller_AdminPractice_Base
 *
 * @brief Encapsulates everything that is common for the Admin controllers.
 *        Derived classes must implement all abstract method.
 */
abstract class Controller_AdminPractice_Base extends Controller_Base
{
    const SESSION_ADMIN_COOKIE = 'session_admin';

    public $m_coordinator;
    public $m_email;
    public $m_password;

    public function __construct()
    {
        parent::__construct(self::SESSION_ADMIN_COOKIE);

        if (isset($_SERVER['REQUEST_METHOD']) and $_SERVER['REQUEST_METHOD'] == 'POST') {
            $sessionId = $this->getPostAttribute(View_Base::SESSION_ID, NULL, FALSE);
            if ($sessionId != NULL) {
                $this->_constructFromSessionId($sessionId);
            } else {
                $this->_init();
            }

            $this->m_operation = $this->getPostAttribute(View_Base::SUBMIT, '');
        } elseif (isset($_COOKIE[$this->m_cookieName])) {
            $this->_constructFromSessionId($_COOKIE[$this->m_cookieName]);
        }

        $this->setAuthentication();
    }

    /**
     * @brief Sign out user
     */
    public function signOut()
    {
        precondition($this->m_coordinator != NULL, "Controller_Base::signOut called with a NULL coordinator");

        // Delete session from the database
        Model_Fields_Session::Delete($this->m_coordinator->id, Model_Fields_Session::PRACTICE_FIELD_COORDINATOR_USER_TYPE, 0);
        $this->m_session = NULL;
        $this->m_isAuthenticated = NULL;

        // Delete cooking if it exists
        if (isset($_COOKIE[$this->m_cookieName])) {
            unset($_COOKIE[$this->m_cookieName]);
            setcookie($this->m_cookieName, null, -1, '/');
        }
    }

    /**
     * @brief Construct the controller from the session identifier
     *
     * @param $sessionId
     */
    private function _constructFromSessionId($sessionId) {
        $this->m_session = Model_Fields_Session::LookupById($sessionId, FALSE);
        if (isset($this->m_session) and $this->m_session->userType == Model_Fields_Session::PRACTICE_FIELD_COORDINATOR_USER_TYPE) {
            $this->m_coordinator = Model_Fields_PracticeFieldCoordinator::LookupById($this->m_session->userId);
        }
    }

    /**
     * @brief Initialize member variables from session
     */
    private function _init() {
        if ($this->m_session != NULL) {
            $this->m_coordinator = Model_Fields_PracticeFieldCoordinator::LookupById($this->m_session->userId);
        }
    }

    /**
     * @brief Set isAuthenticated and update session as necessary
     */
    protected function setAuthentication() {
        if ($this->m_coordinator != NULL) {
            $this->_setAuthentication();
        }
    }

    /**
     * @brief Reset attributes to default values
     */
    protected function _reset() {
        parent::_reset();

        $this->m_coordinator = null;
        $this->m_email = null;
        $this->m_password = null;

    }

    /**
     * @brief Return the name of the coach or empty string if not authenticated
     *
     * @return string : Name of coach or empty string
     */
    public function getCoordinatorsName() {
        if ($this->m_isAuthenticated) {
            return $this->m_coordinator->name;
        }

        return '';
    }
}