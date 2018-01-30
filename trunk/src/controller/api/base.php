<?php

use \DAG\Orm\Schedule\ScheduleCoordinatorOrm;
use \DAG\Domain\Schedule\League;
use \DAG\Domain\Schedule\Season;
use \DAG\Framework\Orm\NoResultsException;

/**
 * Class Controller_Api_Base
 *
 * @brief Encapsulates everything that is common for the Api controllers.
 *        Derived classes must implement all abstract methods
 */
abstract class Controller_Api_Base extends Controller_Base
{
    const SCHEDULE_ADMIN_COOKIE = 'schedule_admin';

    public $m_coordinator;

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
        }

        $this->setAuthentication();
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
}