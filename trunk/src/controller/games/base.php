<?php

use \DAG\Domain\Schedule\League;
use \DAG\Domain\Schedule\Season;
use \DAG\Domain\Schedule\Coordinator;

/**
 * Class Controller_Games_Base
 *
 * @brief Encapsulates everything that is common for the Schedules controllers.
 *        Derived classes must implement all abstract method.
 */
abstract class Controller_Games_Base extends Controller_Base
{
    public function __construct()
    {
        parent::__construct(Controller_Base::SCHEDULE_ADMIN_COOKIE, Coordinator::SCHEDULE_COORDINATOR_USER_TYPE, false);
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
}