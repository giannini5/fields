<?php

use \DAG\Orm\Schedule\ScheduleCoordinatorOrm;
use \DAG\Framework\Orm\NoResultsException;

/**
 * Class Controller_Schedules_Base
 *
 * @brief Encapsulates everything that is common for the Admin controllers.
 *        Derived classes must implement all abstract method.
 */
abstract class Controller_Schedules_Base extends Controller_Base
{
    const SCHEDULE_ADMIN_COOKIE = 'schedule_admin';

    public $m_coordinator;
    public $m_email;
    public $m_password;
    public $m_teams = [];
    public $m_coaches = [];
    public $m_assistantCoaches = [];

    public function __construct()
    {
        parent::__construct(self::SCHEDULE_ADMIN_COOKIE);

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
        $this->_getTeams();
        $this->_getCoaches();
        $this->_getAssistantCoaches();
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

        // Delete cookie if it exists
        if (isset($_COOKIE[$this->m_cookieName])) {
            unset($_COOKIE[$this->m_cookieName]);
            setcookie($this->m_cookieName, null, -1, '/');
        }

        $this->_getTeams();
    }

    /**
     * @brief Construct the controller from the session identifier
     *
     * @param $sessionId
     */
    private function _constructFromSessionId($sessionId) {
        try {
            $this->m_session = Model_Fields_Session::LookupById($sessionId, FALSE);
            if (isset($this->m_session) and $this->m_session->userType == Model_Fields_Session::SCHEDULE_COORDINATOR_USER_TYPE) {
                $this->m_coordinator = \DAG\Orm\Schedule\ScheduleCoordinatorOrm::loadById($this->m_session->userId);
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

    /**
     * @brief Get league
     */
    protected function _getLeague() {
        $this->m_league = \DAG\Domain\Schedule\League::LookupByName('AYSO Region 122');
    }

    /**
     * @brief Get season
     */
    protected function _getSeason() {
        $seasons = \DAG\Domain\Schedule\Season::lookupByLeague($this->m_league);
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
        if (isset($this->m_season)) {
            $this->m_divisions = \DAG\Domain\Schedule\Division::lookupBySeason($this->m_season);
        }
    }

    /**
     * @brief Get list of teams
     */
    protected function _getTeams() {
        $this->m_teams = [];

        foreach ($this->m_divisions as $division) {
            $this->m_teams = array_merge($this->m_teams, \DAG\Domain\Schedule\Team::lookupByDivision($division));
        }
    }

    /**
     * @brief Get list of coaches
     */
    protected function _getCoaches() {
        $this->m_coaches = [];

        foreach ($this->m_teams as $team) {
            if (\DAG\Domain\Schedule\Coach::findCoachForTeam($team, $coach)) {
                $this->m_coaches[] = \DAG\Domain\Schedule\Coach::lookupByTeam($team);
            }
        }
    }

    /**
     * @brief Get list of assistant coaches
     */
    protected function _getAssistantCoaches() {
        $this->m_assistantCoaches = [];

        foreach ($this->m_teams as $team) {
            $this->m_assistantCoaches = array_merge($this->m_assistantCoaches, \DAG\Domain\Schedule\AssistantCoach::lookupByTeam($team));
        }
    }
}