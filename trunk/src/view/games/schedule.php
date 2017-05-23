<?php

use \DAG\Domain\Schedule\Season;
use \DAG\Domain\Schedule\Division;
use \DAG\Domain\Schedule\Coach;
use \DAG\Domain\Schedule\Schedule;
use \DAG\Domain\Schedule\Game;
use \DAG\Domain\Schedule\Flight;
use \DAG\Domain\Schedule\GameTime;

/**
 * @brief Show the Schedule Viewing page
 */
class View_Games_Schedule extends View_Games_Base
{
    /**
     * @brief Construct he View
     *
     * @param $controller - Controller that contains data used when rendering this view.
     */
    public function __construct($controller)
    {
        parent::__construct(self::GAMES_SCHEDULE_PAGE, $controller);
    }

    /**
     * @brief Render data for display on the page.
     */
    public function render()
    {
        // Print selectors
        print "
        <table valign='top' align='center' border=0 cellpadding='5' cellspacing='0'>
                <tr><td>";

        View_Games_Team::printTeamSelectors($this->m_controller->m_season, $this->m_controller->m_filterCoachId);

        print "
                </td><td>";

        $this->_printViewSchedulesByDivision();
        print "
                </td></tr>
        </table>";

        // Print schedule for team if selected
        if ($this->m_controller->m_filterCoachId != 0) {
            $coach = Coach::lookupById($this->m_controller->m_filterCoachId);
            View_Games_Team::printScheduleForTeam($coach->team, $coach);
        }

        // Print schedule for division if selected
        else if (isset($this->m_controller->m_division)) {
            $this->printScheduleForDivision($this->m_controller->m_division);
        }

    }

    /**
     * @brief Print schedules by divisions(s)
     *        - Division
     */
    private function _printViewSchedulesByDivision() {
        $divisionsSelector = $this->getDivisionsSelector(true, false, true);

        // Print the form
        print "
            <table valign='top' align='center' border='1' cellpadding='5' cellspacing='0'>
                <tr>
                    <td valign='top' bgcolor='" . View_Base::VIEW_COLOR  . "'>
                        <table valign='top' align='center' border='0' cellpadding='5' cellspacing='0'>
                            <tr>
                                <th nowrap colspan='2' align='left'>View Schedules by Division</th>
                            </tr>
                        <form method='get' action='" . self::GAMES_SCHEDULE_PAGE . "'>";

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
    private function printScheduleForDivision($division)
    {
        $schedules = Schedule::lookupByDivision($division);

        print "
            <table valign='top' align='center' width='400' border='0' cellpadding='5' cellspacing='0'>
                <tr>
                    <td valign='top'>";

        $schedulePrinted = false;
        foreach ($schedules as $schedule) {
            if ($schedule->published == 1) {
                $this->printSchedule($this->m_controller->m_season, $schedule);
                $schedulePrinted = true;
            }
        }

        if (!$schedulePrinted) {
            print "<p style='color: red; font-size: medium' align='center'>Schedules have not yet been published for Division: $division->name $division->gender.</p>";
        }

        print "
                    </td>
                </tr>
            </table>";
    }

    /**
     * @param Season    $season
     * @param Schedule  $schedule
     */
    private function printSchedule($season, $schedule)
    {
        $flights = Flight::lookupBySchedule($schedule);
        foreach ($flights as $flight) {
            $games          = Game::lookupByFlight($flight);
            $gamesByPool    = [];
            $poolsById      = [];
            foreach ($games as $game) {
                if (isset($game->pool)) {
                    $poolsById[$game->pool->id]     = $game->pool;
                    $gamesByPool[$game->pool->id][] = $game;
                } else {
                    $gamesByPool[0][] = $game;
                }
            }

            print "
            <table valign='top' align='center' width='400' border='1' cellpadding='5' cellspacing='0'>
                <tr>
                    <td valign='top'>";

            foreach ($gamesByPool as $poolId => $poolGames) {
                // No games for pools with cross-pool play so suppress output
                if (count($poolGames) == 0 or $poolId == 0) {
                    continue;
                }

                $pool                           = $poolsById[$poolId];
                $gamesByDateByTimeByField       = [];
                foreach ($poolGames as $game) {
                    $day        = $game->gameTime->gameDate->day;
                    $startTime  = $game->gameTime->startTime;
                    $fieldName  = $game->gameTime->field->facility->name . ": " . $game->gameTime->field->name;
                    $gamesByDateByTimeByField[$day][$startTime][$fieldName] = $game;
                }

                ksort($gamesByDateByTimeByField);

                $this->printGames($schedule, $flight, $pool, $gamesByDateByTimeByField);
            }

            $titleGamesByDateByTimeByField  = [];
            if (isset($gamesByPool[0])) {
                $games = $gamesByPool[0];
                foreach ($games as $game) {
                    $day        = $game->gameTime->gameDate->day;
                    $startTime  = $game->gameTime->startTime;
                    $fieldName  = $game->gameTime->field->facility->name . ": " . $game->gameTime->field->name;
                    $titleGamesByDateByTimeByField[$day][$startTime][$fieldName] = $game;
                }

                ksort($titleGamesByDateByTimeByField);

                $this->printGames($schedule, $flight,null, $titleGamesByDateByTimeByField, true);
            }

            print "
                    </td>
                </tr>
            </table><br><br>";
        }
    }

    /**
     * @param Schedule  $schedule
     * @param Flight    $flight
     * @param Pool|null $pool
     * @param array     $gamesByDateByTimeByField
     * @param bool      $includeGameTitle
     */
    private function printGames($schedule, $flight, $pool, $gamesByDateByTimeByField, $includeGameTitle = false)
    {
        $poolName = isset($pool) ? $pool->name : 'Medal Round';

        // Print table header
        print "
            <table valign='top' align='center' border='1' cellpadding='5' cellspacing='0' width='750'>";

        $headerRow  = $schedule->division->name . ' ' . $schedule->division->gender . ' ' . $flight->name . ": " . $poolName;
        if (isset($pool) and $pool->gamesAgainstPool->id != $pool->id) {
            $name = $pool->gamesAgainstPool->name;
            $headerRow .= "<font color='red'> (Cross-pool play with $name)</font>";
        }

        // Printer Header row
        $gameTitleHeader    = $includeGameTitle ? "<th rowspan='2'>Title</th>" : "";
        $colspan            = $includeGameTitle ? 9 : 8;
        print "
                <thead>
                    <tr bgcolor='lightskyblue'>
                        <th colspan='$colspan' align='center'>$headerRow</th>
                    </tr>
                    <tr bgcolor='lightskyblue'>
                        <th rowspan='2'>Day</th>
                        $gameTitleHeader
                        <th rowspan='2'>Field</th>
                        <th rowspan='2'>Time</th>
                        <th rowspan='2'>Game Id</th>
                        <th colspan='2'>Home Team</th>
                        <th colspan='2'>Visiting Team</th>
                    </tr>
                    <tr bgcolor='lightskyblue'>
                        <th>Name</th>
                        <th>Score</th>
                        <th>Name</th>
                        <th>Score</th>
                    </tr>
                </thead>";

        // Print table rows
        foreach ($gamesByDateByTimeByField as $day => $gameTimeFieldData) {
            ksort($gameTimeFieldData);
            $dayRowSpan     = 0;
            $dayRowPrinted  = false;

            foreach ($gameTimeFieldData as $gameTime => $gameFieldData) {
                $dayRowSpan += count($gameFieldData);
            }

            foreach ($gameTimeFieldData as $gameTime => $gameFieldData) {
                ksort($gameFieldData);
                $gameTime = substr($gameTime, 0, 5);

                foreach ($gameFieldData as $fieldName => $game) {
                    $homeTeamName       = "&nbsp";
                    $visitingTeamName   = "&nbsp";
                    $homeTeamScore      = "&nbsp";
                    $visitingTeamScore  = "&nbsp";
                    $title              = $game->title;
                    if (isset($game->homeTeam)) {
                        $homeTeamCoach      = Coach::lookupByTeam($game->homeTeam);
                        $visitingTeamCoach  = Coach::lookupByTeam($game->visitingTeam);
                        $homeTeamName       = $game->homeTeam->name . ": " . $homeTeamCoach->lastName;
                        $visitingTeamName   = $game->visitingTeam->name . ": " . $visitingTeamCoach->lastName;
                        $homeTeamScore      = isset($game->homeTeamScore) ? $game->homeTeamScore : "&nbsp";
                        $visitingTeamScore  = isset($game->visitingTeamScore) ? $game->visitingTeamScore : "&nbsp";
                    }

                    $dayRow         = $dayRowPrinted ? "" : "<td nowrap rowspan='$dayRowSpan'>$day</td>";
                    $dayRowPrinted  = true;

                    $titleRow = $includeGameTitle ? "<td nowrap>$title</td>" : "";
                    print "
                        <tr>
                            $dayRow
                            $titleRow
                            <td nowrap>$fieldName</td>
                            <td nowrap>$gameTime</td>
                            <td nowrap align='center'>$game->id</td>
                            <td nowrap>$homeTeamName</td>
                            <td nowrap align='center'>$homeTeamScore</td>
                            <td nowrap>$visitingTeamName</td>
                            <td nowrap align='center'>$visitingTeamScore</td>
                        </tr>";
                }
            }
        }

        print "
            </table><br>";
    }
}