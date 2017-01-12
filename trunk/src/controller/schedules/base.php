<?php

use \DAG\Domain\Schedule\League;
use \DAG\Domain\Schedule\Season;

/**
 * Class Controller_Schedules_Base
 *
 * @brief Encapsulates everything that is common for the Schedules controllers.
 *        Derived classes must implement all abstract method.
 */
abstract class Controller_Schedules_Base extends Controller_Base
{
    public function __construct()
    {
        parent::__construct(Controller_AdminSchedules_Base::SCHEDULE_ADMIN_COOKIE, false);

        if (isset($_SERVER['REQUEST_METHOD']) and $_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->m_operation = $this->getPostAttribute(View_Base::SUBMIT, '');
        }
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