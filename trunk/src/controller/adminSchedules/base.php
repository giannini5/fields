<?php

use \DAG\Framework\Exception\Assertion;
use \DAG\Domain\Schedule\Coordinator;
use \DAG\Domain\Schedule\League;
use \DAG\Domain\Schedule\Season;
use \DAG\Domain\Schedule\Division;
use \DAG\Domain\Schedule\Team;
use \DAG\Domain\Schedule\Coach;
use \DAG\Domain\Schedule\AssistantCoach;

/**
 * Class Controller_AdminSchedules_Base
 *
 * @brief Encapsulates everything that is common for the Admin controllers.
 *        Derived classes must implement all abstract method.
 */
abstract class Controller_AdminSchedules_Base extends Controller_Base
{
    public $m_teams = [];
    public $m_coaches = [];
    public $m_assistantCoaches = [];

    public function __construct()
    {
        parent::__construct(self::SCHEDULE_ADMIN_COOKIE, Coordinator::SCHEDULE_COORDINATOR_USER_TYPE);

        $this->_getTeams();
        $this->_getCoaches();
        $this->_getAssistantCoaches();
    }

    /**
     * @brief get Divisions from division names
     *
     * @param string[]  $divisionNames
     *
     * @return Division[]
     */
    protected function _getDivisionsFromNames($divisionNames)
    {
        $divisions = [];
        foreach ($divisionNames as $divisionNameWithGender) {
            // DivisionName: <name> <gender>
            $divisionNameAttributes = explode(' ', $divisionNameWithGender);
            Assertion::isTrue(2 == count($divisionNameAttributes), "Invalid divisionName: $divisionNameWithGender");

            $divisionName   = $divisionNameAttributes[0];
            $gender         = $divisionNameAttributes[1];
            $divisions[]    = Division::lookupByNameAndGender($this->m_season, $divisionName, $gender);
        }

        return $divisions;
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
        if (isset($this->m_season)) {
            $this->m_divisions = Division::lookupBySeason($this->m_season);
        }
    }

    /**
     * @brief Get list of teams
     */
    protected function _getTeams() {
        $this->m_teams = [];

        foreach ($this->m_divisions as $division) {
            $this->m_teams = array_merge($this->m_teams, Team::lookupByDivision($division));
        }
    }

    /**
     * @brief Get list of coaches
     */
    protected function _getCoaches() {
        $this->m_coaches = [];

        foreach ($this->m_teams as $team) {
            if (Coach::findCoachForTeam($team, $coach)) {
                $this->m_coaches[] = Coach::lookupByTeam($team);
            }
        }
    }

    /**
     * @brief Get list of assistant coaches
     */
    protected function _getAssistantCoaches() {
        $this->m_assistantCoaches = [];

        foreach ($this->m_teams as $team) {
            $this->m_assistantCoaches = array_merge($this->m_assistantCoaches, AssistantCoach::lookupByTeam($team));
        }
    }
}