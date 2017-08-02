<?php

use \DAG\Orm\Schedule\ScheduleCoordinatorOrm;
use \DAG\Framework\Orm\NoResultsException;
use \DAG\Domain\Schedule\League;
use \DAG\Domain\Schedule\Season;

/**
 * Class Controller_AdminScoring_Base
 *
 * @brief Encapsulates everything that is common for the Admin scoring controllers.
 *        Derived classes must implement all abstract method.
 */
abstract class Controller_AdminScoring_Base extends Controller_Base
{
    const SCORING_ADMIN_COOKIE = 'scoring_admin';

    public $m_coordinator;
    public $m_email;
    public $m_password;

    public function __construct()
    {
        parent::__construct(self::SCORING_ADMIN_COOKIE);

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
        precondition($this->m_coordinator != null, "Controller_Base::signOut called with a NULL coordinator");

        // Delete session from the database
        Model_Fields_Session::Delete($this->m_coordinator->id, Model_Fields_Session::SCORING_COORDINATOR_USER_TYPE, 0);
        $this->m_session            = null;
        $this->m_isAuthenticated    = null;

        // Delete cookie if it exists
        if (isset($_COOKIE[$this->m_cookieName])) {
            unset($_COOKIE[$this->m_cookieName]);
            setcookie($this->m_cookieName, null, -1, '/');
        }
    }

    /**
     * @brief Login to existing account
     */
    protected function _login() {
        try {
            if ($this->m_operation == View_Base::SUBMIT) {
                $this->m_coordinator = ScheduleCoordinatorOrm::loadByLeagueIdAndEmail($this->m_league->id, $this->m_email);

                if ($this->m_coordinator->password != $this->m_password) {
                    $this->_reset();
                    $this->m_password = "* Incorrect password - try again";
                } else {
                    $this->createSession($this->m_coordinator->id, Model_Fields_Session::SCORING_COORDINATOR_USER_TYPE, 0);
                }
            }
        } catch (NoResultsException $e) {
            $this->_reset();
            $this->m_email = "* Incorrect email - try again";
        }
    }

    /**
     * @brief Construct the controller from the session identifier
     *
     * @param $sessionId
     */
    private function _constructFromSessionId($sessionId) {
        try {
            $this->m_session = Model_Fields_Session::LookupById($sessionId, FALSE);
            if (isset($this->m_session) and $this->m_session->userType == Model_Fields_Session::SCORING_COORDINATOR_USER_TYPE) {
                $this->m_coordinator = ScheduleCoordinatorOrm::loadById($this->m_session->userId);
            }
        } catch (NoResultsException $e) {
            // I guess someone messed with the database.  Force login.
            // TODO: delete the cookie
            $this->m_session = null;
        }
    }

    /**
     * @brief Initialize member variables from session
     */
    private function _init() {
        try {
            if ($this->m_session != NULL) {
                // TODO add new scoring coordinator admin table
                $this->m_coordinator = ScheduleCoordinatorOrm::loadById($this->m_session->userId);
            }
        } catch (NoResultsException $e) {
            // I guess the database got reset
            // TODO: remove the cookie
            $this->m_session = NULL;
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
     * @brief Return the name of the coordinator or empty string if not authenticated
     *
     * @return string : Name of coordinator or empty string
     */
    public function getCoordinatorsName() {
        if ($this->m_isAuthenticated) {
            return $this->m_coordinator->name;
        }

        return '';
    }

    /**
     * @brief Get league
     */
    protected function _getLeague() {
        $this->m_league = League::LookupByName('AYSO Region 122');
    }

    /**
     * @brief Get season
     */
    protected function _getSeason() {
        $seasons = Season::lookupByLeague($this->m_league);
        foreach ($seasons as $season) {
            if ($season->enabled == 1) {
                $this->m_season = $season;
                return;
            }
        }
    }

    /**
     * @brief Get list of divisions for selector
     */
    protected function _getDivisions() {
        $this->m_divisions = [];
        // No op
    }

    /**
     * @brief Get list of teams
     */
    protected function _getTeams() {
        $this->m_teams = [];
        // No op
    }
}