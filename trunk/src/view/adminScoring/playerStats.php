<?php

use \DAG\Domain\Schedule\Division;
use \DAG\Orm\Schedule\PlayerOrm;
use \DAG\Domain\Schedule\Player;
use \DAG\Domain\Schedule\Team;
use \DAG\Domain\Schedule\Coach;

/**
 * @brief Show the Player Statistics page
 */
class View_AdminScoring_PlayerStats extends View_AdminScoring_Base
{
    /**
     * @brief Construct he View
     *
     * @param Controller_AdminScoring_PlayerStats $controller - Controller that contains data used when rendering this view.
     */
    public function __construct($controller)
    {
        parent::__construct(self::SCORING_PLAYER_STATS_PAGE, $controller);
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
            $this->printPlayerStatsForDivision($this->m_controller->m_division);
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
                                <th nowrap colspan='2' align='left'>View Player Statistics by Division</th>
                            </tr>
                        <form method='get' action='" . self::SCORING_PLAYER_STATS_PAGE . "'>";

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
    private function printPlayerStatsForDivision($division)
    {
        $teams  = Team::lookupByDivision($division);

        foreach ($teams as $team) {
            $this->printPlayerStatsForTeam($team);
        }
    }

    /**
     * @param Team $team
     */
    private function printPlayerStatsForTeam($team)
    {
        $players    = Player::lookupByTeam($team, PlayerOrm::ORDER_BY_NUMBER);
        $coach      = Coach::lookupByTeam($team);

        $teamName = $team->nameIdWithSeed . "<br>" . $coach->name;

        print "
                <table valign='top' align='center' bgcolor='white' width='100' border='1' cellpadding='5' cellspacing='0'>
                    <tr bgcolor='lightskyblue'>
                        <th colspan='9'>$teamName</th>
                    </tr>
                    <tr bgcolor='lightskyblue'>
                        <th rowspan='2' title='Jersey number for player'>#</th>
                        <th rowspan='2' title='Name of player'>Name</th>
                        <th rowspan='2' title='Number of goals scored by player'>Goals</th>
                        <th colspan='4'>Quarters</th>
                        <th colspan='2'>Cards</th>
                    </tr>
                    <tr bgcolor='lightskyblue'>
                        <th title='Number of quaters that the player has been a substitute'>Sub</th>
                        <th title='Number of quaters that the player has been keeper'>Keeper</th>
                        <th title='Number of quaters that the player has been injured'>Injured</th>
                        <th title='Number of quaters that the player has been absent'>Absent</th>
                        <th title='Number of yellow cards received by player'>Yellow</th>
                        <th title='Number of red cards received by player'>Red</th>
                    </tr>";

        foreach ($players as $player) {
            print "
                    <tr>
                        <td align='center'>$player->number</td>
                        <td nowrap>$player->firstName</td>
                        <td align='right'>$player->goals</td>
                        <td align='right'>$player->quartersSub</td>
                        <td align='right'>$player->quartersKeep</td>
                        <td align='right'>$player->quartersInjured</td>
                        <td align='right'>$player->quartersAbsent</td>
                        <td align='right'>$player->yellowCards</td>
                        <td align='right'>$player->redCards</td>
                    </tr>";
        }

        print "
                </table>
                <br>";
    }
}