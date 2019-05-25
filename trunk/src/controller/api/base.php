<?php

use \DAG\Domain\Schedule\League;
use \DAG\Domain\Schedule\Season;

/**
 * Class Controller_Api_Base
 *
 * @brief Encapsulates everything that is common for the Api controllers.
 *        Derived classes must implement all abstract methods
 */
abstract class Controller_Api_Base extends Controller_Base
{
    public $m_coordinator;

    /**
     * Controller_Api_Base constructor.
     * @param string    $cookieName
     * @param string    $userType
     */
    public function __construct($cookieName, $userType)
    {
        parent::__construct($cookieName, $userType, false);
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