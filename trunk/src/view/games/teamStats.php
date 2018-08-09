<?php

use \DAG\Domain\Schedule\Division;
use \DAG\Domain\Schedule\Team;
use \DAG\Domain\Schedule\Coach;
use \DAG\Domain\Schedule\Game;

/**
 * @brief Show the Team Statistics page
 */
class View_Games_TeamStats extends View_Games_Base
{
    /**
     * @brief Construct he View
     *
     * @param View_Games_TeamStats $controller - Controller that contains data used when rendering this view.
     */
    public function __construct($controller)
    {
        parent::__construct(self::GAMES_TEAM_STATS_PAGE, $controller);
    }

    /**
     * @brief Render data for display on the page.
     */
    public function render()
    {
        // Print selectors
        $this->_printDivisionSelector();

        // Print standings for division if selected
        if (isset($this->m_controller->m_division)) {
            $this->printTeamStatsForDivision($this->m_controller->m_division);
        }
    }

    /**
     * @brief Print standings by divisions(s)
     *        - Division
     */
    private function _printDivisionSelector() {
        $divisionsSelector = $this->getDivisionsSelector(true, false, true, true);

        // Print the form
        print "
            <table valign='top' align='center' border='1' cellpadding='5' cellspacing='0'>
                <tr>
                    <td valign='top' bgcolor='" . View_Base::VIEW_COLOR  . "'>
                        <table valign='top' align='center' border='0' cellpadding='5' cellspacing='0'>
                            <tr>
                                <th nowrap colspan='2' align='left'>View Team Statistics by Division</th>
                            </tr>
                        <form method='get' action='" . self::GAMES_TEAM_STATS_PAGE . "'>";

        $this->displaySelector('Division:', View_Base::DIVISION_NAME, '', $divisionsSelector, $this->m_controller->m_divisionName, NULL, true, 140, 'left', 'Select a Division');

        // Print View button and end form
        print "
                            <tr>
                                <td align='left'>
                                    <input style='background-color: yellow' name='" . View_Base::SUBMIT . "' type='submit' value='" . View_Base::SUBMIT . "'>
                                </td>
                            </tr>
                        </form>
                        </table>
                    </td>
                </tr>
            </table>
            <br>";
    }

    /**
     * @param Division $division
     */
    private function printTeamStatsForDivision($division)
    {
        $teams  = Team::lookupByDivision($division);

        print "
                <table valign='top' align='center' bgcolor='white' width='100' border='1' cellpadding='5' cellspacing='0'>
                    <tr bgcolor='lightskyblue'>
                        <th>Team</th>
                        <th>Coach</th>
                        <th>Wins</th>
                        <th>Losses</th>
                        <th>Ties</th>
                        <th title='Number of goals scored by team'>Goals Scored</th>
                        <th title='Number of goals allowed by team'>Goals Allowed</th>
                        <th>Yellow Cards</th>
                        <th>Red Cards</th>
                    </tr>";

        foreach ($teams as $team) {
            $this->printStatsForTeam($team);
        }

        print "
                </table>
                <br>";
    }

    /**
     * @param Team $team
     */
    private function printStatsForTeam($team)
    {
        $coach        = Coach::lookupByTeam($team);
        $wins         = 0;
        $losses       = 0;
        $ties         = 0;
        $goalsScored  = 0;
        $goalsAllowed = 0;
        $yellowCards  = 0;
        $redCards     = 0;
        $teamName     = $team->nameIdWithSeed;
        $coachName    = $coach->name;

        $games = \DAG\Domain\Schedule\Game::lookupByTeam($team);

        foreach ($games as $game) {
            if (isset($game->homeTeam) and $game->homeTeam->id == $team->id) {
                $goalsScored    += $game->homeTeamScore;
                $goalsAllowed   += $game->visitingTeamScore;
                $yellowCards    += $game->homeTeamYellowCards;
                $redCards       += $game->homeTeamRedCards;

                $wins   += $game->homeTeamScore > $game->visitingTeamScore ? 1 : 0;
                $losses += $game->homeTeamScore < $game->visitingTeamScore ? 1 : 0;
                $ties   += $game->homeTeamScore == $game->visitingTeamScore ? 1 : 0;
            } else if (isset($game->visitingTeam) and $game->visitingTeam->id == $team->id) {
                $goalsScored    += $game->visitingTeamScore;
                $goalsAllowed   += $game->homeTeamScore;
                $yellowCards    += $game->visitingTeamYellowCards;
                $redCards       += $game->visitingTeamRedCards;

                $wins   += $game->homeTeamScore < $game->visitingTeamScore ? 1 : 0;
                $losses += $game->homeTeamScore > $game->visitingTeamScore ? 1 : 0;
                $ties   += $game->homeTeamScore == $game->visitingTeamScore ? 1 : 0;
            }
        }

        print "
                <tr>
                    <td nowrap>$teamName</td>
                    <td nowrap>$coachName</td>
                    <td align='right'>$wins</td>
                    <td align='right'>$losses</td>
                    <td align='right'>$ties</td>
                    <td align='right'>$goalsScored</td>
                    <td align='right'>$goalsAllowed</td>
                    <td align='right'>$yellowCards</td>
                    <td align='right'>$redCards</td>
                </tr>";
    }
}