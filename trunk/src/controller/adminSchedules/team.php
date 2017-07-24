<?php

use \DAG\Domain\Schedule\Team;
use \DAG\Domain\Schedule\Coach;
use \DAG\Domain\Schedule\AssistantCoach;
use \DAG\Domain\Schedule\Player;
use \DAG\Domain\Schedule\Schedule;

/**
 * Class Controller_AdminSchedules_Team
 *
 * @brief Select a field to administer or create a new team
 */
class Controller_AdminSchedules_Team extends Controller_AdminSchedules_Base {

    public $m_showPlayers = false;
    public $m_swapTeamId1;
    public $m_swapTeamId2;

    public function __construct() {
        parent::__construct();

        if (isset($_SERVER['REQUEST_METHOD']) and $_SERVER['REQUEST_METHOD'] == 'POST') {
            if ($this->m_operation == View_Base::FILTER) {
                $this->m_filterDivisionId = $this->getPostAttribute(View_Base::FILTER_DIVISION_ID, 0);
                $this->m_filterTeamId     = $this->getPostAttribute(View_Base::FILTER_TEAM_ID, 0);
                $this->m_filterCoachId    = $this->getPostAttribute(View_Base::FILTER_COACH_ID, 0);
                $this->m_showPlayers      = $this->getPostCheckboxAttribute(View_Base::SHOW_PLAYERS, false);
            }

            if ($this->m_operation == View_Base::SWAP) {
                $this->m_swapTeamId1 = $this->getPostAttribute(View_Base::SWAP_TEAM_ID1, 0, TRUE, TRUE);
                $this->m_swapTeamId2 = $this->getPostAttribute(View_Base::SWAP_TEAM_ID2, 0, TRUE, TRUE);
            }
        }
    }

    /**
     * @brief On GET, render the page to administer seasons
     *        On POST, complete the transaction and then render the page (with error message if any)
     */
    public function process() {
        if ($this->m_missingAttributes == 0) {
            switch ($this->m_operation) {
                case View_Base::SWAP:
                    $this->swapTeams();
                    break;
            }
        }

        if ($this->m_isAuthenticated) {
            $view = new View_AdminSchedules_Team($this);
        } else {
            $view = new View_AdminSchedules_Home($this);
        }

        $view->displayPage();
    }

    /**
     * @brief Swap Two Teams:
     *        - Assign coach, assistant coach and players on team1 to team2 and vice versa
     */
    private function swapTeams() {
        // Verify teams exists
        $team1 = Team::lookupById((int)$this->m_swapTeamId1);
        $team2 = Team::lookupById((int)$this->m_swapTeamId2);

        // Verify teams are in the same division
        if ($team1->division->id != $team2->division->id) {
            $this->m_errorString = "Swap not allowed, teams must be in the same division.";
            return;
        }

        // Verify there is only one schedule (swapping is not allowed if there are multiple schedules)
        $schedules = Schedule::lookupByDivision($team1->division);
        if (count($schedules) > 1) {
            $this->m_errorString = "Too many schedules.  Swapping only allowed if there is just one schedule.";
            return;
        }

        // Verify schedules are not published
        foreach ($schedules as $schedule) {
            if ($schedule->published == 1) {
                $this->m_errorString = "Schedule(s) have been published.  You must unpublished before you can swap teams";
                return;
            }
        }

        // Get coaches and players for team 1
        $team1Coach             = Coach::lookupByTeam($team1);
        $team1AssistantCoaches  = AssistantCoach::lookupByTeam($team1);
        $team1Players           = Player::lookupByTeam($team1);

        // Get coaches and players for team 2
        $team2Coach             = Coach::lookupByTeam($team2);
        $team2AssistantCoaches  = AssistantCoach::lookupByTeam($team2);
        $team2Players           = Player::lookupByTeam($team2);

        // Make the swap

        // Swap coaches
        $team1Coach->swapTeams($team2Coach);

        foreach ($team1AssistantCoaches as $coach) {
            $coach->team = $team2;
        }

        foreach ($team2AssistantCoaches as $coach) {
            $coach->team = $team1;
        }

        // Swap players
        foreach ($team1Players as $team1Player) {
            $team1Player->team = $team2;
        }

        foreach ($team2Players as $team2Player) {
            $team2Player->team = $team1;
        }

        // Swap team meta data
        $team1Name      = $team1->name == $team1->nameId ? $team2->name : $team1->name;
        $team1Region    = $team1->region;
        $team1City      = $team1->city;

        $team1->name    = $team2->name == $team2->nameId ? $team1->name : $team2->name;
        $team1->region  = $team2->region;
        $team1->city    = $team2->city;

        $team2->name    = $team1Name;
        $team2->region  = $team1Region;
        $team2->city    = $team1City;

        $this->m_messageString = $team2->nameId . " swapped with " . $team1->nameId;
    }
}