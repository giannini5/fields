<?php

use \DAG\Domain\Schedule\Division;
use \DAG\Domain\Schedule\Coach;
use \DAG\Domain\Schedule\Schedule;
use \DAG\Domain\Schedule\Game;
use \DAG\Domain\Schedule\Flight;
use \DAG\Orm\Schedule\ScheduleOrm;

/**
 * @brief Show the Schedule Viewing page
 */
class View_Games_Schedule extends View_Games_Base
{
    /**
     * @brief Construct he View
     *
     * @param Controller_Base   $controller - Controller that contains data used when rendering this view.
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
        if (!$this->m_controller->m_isPopup) {
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
        }

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
                View_Games_Schedule::printSchedule($schedule);
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
     * @param Schedule  $schedule
     */
    static public function printSchedule($schedule)
    {
        print "
            <table bgcolor='yellow' valign='top' align='center' width='400' border='0' cellpadding='5' cellspacing='0'>
                <tr>
                    <th><h1>$schedule->name</h1></th>
                 </tr>
                 <tr>
                    <td valign='top'>";

        $flights = Flight::lookupBySchedule($schedule);
        foreach ($flights as $flight) {
            // Skip flights where games are not being scheduled
            if ($flight->scheduleGames != 1) {
                continue;
            }

            $games          = Game::lookupByFlight($flight);
            $gamesByPool    = [];
            $poolsById      = [];
            $poolsByName    = [];
            foreach ($games as $game) {
                if (isset($game->pool)) {
                    $poolsByName[$game->pool->name] = $game->pool;
                    $poolsById[$game->pool->id]     = $game->pool;
                    $gamesByPool[$game->pool->id][] = $game;
                } else {
                    $gamesByPool[0][] = $game;
                }
            }

            ksort($poolsByName);

            print "
            <table bgcolor='lightgray' valign='top' align='center' width='400' border='1' cellpadding='5' cellspacing='0'>
                <tr>
                    <td valign='top'>";

            foreach ($poolsByName as $poolName => $pool) {
                foreach ($gamesByPool as $poolId => $poolGames) {
                    // No games for pools with cross-pool play so suppress output
                    if (count($poolGames) == 0 or $poolId == 0) {
                        continue;
                    }

                    if ($pool->id != $poolId) {
                        continue;
                    }

                    $pool = $poolsById[$poolId];
                    $gamesByDateByTimeByField = [];
                    foreach ($poolGames as $game) {
                        $day = $game->gameTime->gameDate->day;
                        $startTime = $game->gameTime->actualStartTime;
                        $fieldName = $game->gameTime->field->facility->name . ": " . $game->gameTime->field->name;
                        $gamesByDateByTimeByField[$day][$startTime][$fieldName] = $game;
                    }

                    ksort($gamesByDateByTimeByField);

                    View_Games_Schedule::printGames($schedule, $flight, $pool, $gamesByDateByTimeByField);
                }
            }

            $titleGamesByDateByTimeByField  = [];
            if (isset($gamesByPool[0])) {
                $games = $gamesByPool[0];
                foreach ($games as $game) {
                    $day        = $game->gameTime->gameDate->day;
                    $startTime  = $game->gameTime->actualStartTime;
                    $fieldName  = $game->gameTime->field->facility->name . ": " . $game->gameTime->field->name;
                    $titleGamesByDateByTimeByField[$day][$startTime][$fieldName] = $game;
                }

                ksort($titleGamesByDateByTimeByField);

                View_Games_Schedule::printGames($schedule, $flight,null, $titleGamesByDateByTimeByField);
            }

            print "
                    </td>
                </tr>
            </table><br><br>";
        }


        print "
                    </td>
                 </tr>
             </table><br><br>";
    }

    /**
     * @param Schedule  $schedule
     * @param Flight    $flight
     * @param Pool|null $pool
     * @param array     $gamesByDateByTimeByField
     */
    static public function printGames($schedule, $flight, $pool, $gamesByDateByTimeByField)
    {
        $poolName = isset($pool) ? $pool->name : 'Medal Round';

        // Print table header
        print "
            <table bgcolor='white' valign='top' align='center' border='1' cellpadding='5' cellspacing='0' width='750'>";

        $headerRow  = $schedule->name . ": " . $schedule->division->name . ' ' . $schedule->division->gender . ' Flight ' . $flight->name . ": " . $poolName;
        if (isset($pool) and $pool->gamesAgainstPool->id != $pool->id) {
            $name = $pool->gamesAgainstPool->name;
            $headerRow .= "<font color='red'> (Cross-pool play with $name)</font>";
        }

        // Printer Header row
        $colspan = 9;
        print "
                <thead>
                    <tr bgcolor='lightskyblue'>
                        <th colspan='$colspan' align='center'>$headerRow</th>
                    </tr>
                    <tr bgcolor='lightskyblue'>
                        <th rowspan='2'>Day</th>
                        <th rowspan='2'>Field</th>
                        <th rowspan='2'>Time</th>
                        <th rowspan='2'>Game Id</th>
                        <th colspan='2'>Home Team</th>
                        <th colspan='2'>Visiting Team</th>
                        <th rowspan='2'>Goal Differential</th>
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
                    $title = $game->title;

                    $values         = View_AdminSchedules_Base::getDisplayLabels($game, true);
                    $homeTeamName   = $schedule->scheduleType == ScheduleOrm::SCHEDULE_TYPE_TOURNAMENT ?
                        $values[View_Base::TEAM_ID_COACH_SHORT_NAME] : $values[View_Base::TEAM_ID_COACH_SHORT_NAME];
                    $homeTeamTitle  = "title='" . $values[View_Base::HOVER_TEXT] . "'";
                    $homeTeamScore  = $values[View_Base::SCORE];

                    $values             = View_AdminSchedules_Base::getDisplayLabels($game, false);
                    $visitingTeamName   = $schedule->scheduleType == ScheduleOrm::SCHEDULE_TYPE_TOURNAMENT ?
                        $values[View_Base::TEAM_ID_COACH_SHORT_NAME] : $values[View_Base::TEAM_ID_COACH_SHORT_NAME];
                    $visitingTeamTitle  = "title='" . $values[View_Base::HOVER_TEXT] . "'";
                    $visitingTeamScore  = $values[View_Base::SCORE];
                    $goalDifferential   = abs($homeTeamScore - $visitingTeamScore);
                    $gdbgColor          = $goalDifferential > 5 ? "bgcolor='salmon'" : '';

                    $dayRow         = $dayRowPrinted ? "" : "<td nowrap rowspan='$dayRowSpan'>$day</td>";
                    $dayRowPrinted  = true;

                    $titleLine = empty($title) ? "" : "<br><strong style='color:green'>$title</strong>";
                    print "
                        <tr>
                            $dayRow
                            <td nowrap>$fieldName$titleLine</td>
                            <td nowrap>$gameTime</td>
                            <td nowrap align='center'>
                                <a href=\"javascript:window.open('" . View_Base::GAMES_GAME_CARDS_PAGE . "?gameId=$game->id&submit=Enter&scoringType=game&popup=1','game cards','width=890,height=800')\">$game->id</a>
                            <td nowrap $homeTeamTitle>$homeTeamName</td>
                            <td nowrap align='center'>$homeTeamScore</td>
                            <td nowrap $visitingTeamTitle>$visitingTeamName</td>
                            <td nowrap align='center'>$visitingTeamScore</td>
                            <td nowrap align='center' $gdbgColor>$goalDifferential</td>
                        </tr>";
                }
            }
        }

        print "
            </table><br>";
    }
}