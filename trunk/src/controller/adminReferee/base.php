<?php

use \DAG\Domain\Schedule\League;
use \DAG\Domain\Schedule\Season;
use \DAG\Domain\Schedule\Coordinator;

/**
 * Class Controller_AdminReferee_Base
 *
 * @brief Encapsulates everything that is common for the Admin Referee controllers.
 *        Derived classes must implement all abstract method.
 */
abstract class Controller_AdminReferee_Base extends Controller_Base
{
    public function __construct()
    {
        parent::__construct(self::REFEREE_ADMIN_COOKIE, Coordinator::REFEREE_COORDINATOR_USER_TYPE);
    }

    /**
     * @brief Get league
     */
    protected function _getLeague() {
        $this->m_league = League::LookupByName(LEAGUE_NAME);
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