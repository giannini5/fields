<?php

namespace DAG\Domain\Schedule;

use DAG\Domain\Domain;
use DAG\Framework\Exception\Precondition;

class HomeGameTracker extends Domain
{
    /** @var array */
    private $homeGamesByTeamId      = [];
    private $visitingGamesByTeamId  = [];

    /** @var Team[] */
    private $teams = [];

    /**
     * HomeGameTracker constructor.
     * @param Team[]   $teams
     */
    public function __construct($teams)
    {
        $this->addTeams($teams);
    }

    /**
     * @param Team[]    $teams
     */
    public function addTeams($teams)
    {
        $this->teams = array_merge($this->teams, $teams);

        foreach ($teams as $team) {
            Precondition::isTrue(!isset($this->homeGamesByTeamId[$team->id]),
                "$team->id already exists in homeGamesByTeamId");
            $this->homeGamesByTeamId[$team->id]     = 0;
            $this->visitingGamesByTeamId[$team->id] = 0;
        }
    }

    /**
     * Return a list that contains the homeTeam and visitingTeam
     *
     * @param Team  $team1
     * @param Team  $team2
     *
     * @return  array
     */
    public function getHomeVisitorTeams($team1, $team2)
    {
        Precondition::arrayKeyExists($this->homeGamesByTeamId, $team1->id, "$team1->id");
        Precondition::arrayKeyExists($this->homeGamesByTeamId, $team2->id, "$team2->id");

        $team1Count = $this->homeGamesByTeamId[$team1->id];
        $team2Count = $this->homeGamesByTeamId[$team2->id];

        $homeTeam       = $team1Count < $team2Count ? $team1 : $team2;
        $visitingTeam   = $homeTeam->id == $team1->id ? $team2 : $team1;

        $this->homeGamesByTeamId[$homeTeam->id]         += 1;
        $this->visitingGamesByTeamId[$visitingTeam->id] += 1;

        return array($homeTeam, $visitingTeam);
    }

    /**
     * Best attempt to even out the home/visiting team assignments
     */
    public function evenOutHomeGames()
    {
        // For each team that has more home games than visiting games
        foreach ($this->teams as $team) {
            if ($this->homeGamesByTeamId[$team->id] > $this->visitingGamesByTeamId[$team->id]) {
                // Get the games by homeTeamId
                $homeGames = Game::lookupByHomeTeam($team);

                foreach ($homeGames as $game) {
                    // Swap with another team that has more visiting games than home games
                    $swapTeam = $game->visitingTeam;
                    if ($this->homeGamesByTeamId[$swapTeam->id] < $this->visitingGamesByTeamId[$swapTeam->id]) {
                        $game->swapTeams();

                        // Adjust Counts
                        $this->homeGamesByTeamId[$team->id]         -= 1;
                        $this->visitingGamesByTeamId[$team->id]     += 1;
                        $this->homeGamesByTeamId[$swapTeam->id]     += 1;
                        $this->visitingGamesByTeamId[$swapTeam->id] -= 1;

                        // Break out and continue with next team if counts are not equal
                        if ($this->homeGamesByTeamId[$team->id] == $this->visitingGamesByTeamId[$team->id]) {
                            break;
                        }
                    }
                }
            }
        }
    }
}