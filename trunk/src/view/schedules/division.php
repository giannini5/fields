<?php

use \DAG\Domain\Schedule\Division;
use \DAG\Domain\Schedule\GameTime;
use \DAG\Domain\Schedule\Coach;
use \DAG\Domain\Schedule\Schedule;
use \DAG\Domain\Schedule\Pool;
use \DAG\Domain\Schedule\Game;

/**
 * @brief Show the Division Schedule Viewing page
 */
class View_Schedules_Division extends View_Schedules_Base {
    /**
     * @brief Construct he View
     *
     * @param $controller - Controller that contains data used when rendering this view.
     */
    public function __construct($controller) {
        parent::__construct(self::SCHEDULE_DIVISION_PAGE, $controller);
    }

    /**
     * @brief Render data for display on the page.
     */
    public function render()
    {
        $this->_printViewSchedulesByDivision();

        if (isset($this->m_controller->m_division)) {
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
                        <form method='get' action='" . self::SCHEDULE_DIVISION_PAGE . "'>";

        $this->displaySelector('Division:', View_Base::DIVISION_NAME, '', $divisionsSelector, $this->m_controller->m_divisionName, NULL, true, 140, left, 'Select a Division');

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
            <table valign='top' align='center' width='400' border='1' cellpadding='5' cellspacing='0'>
                <tr>
                    <td valign='top'>";

        $schedulePrinted = false;
        foreach ($schedules as $schedule) {
            if ($schedule->published == 1) {
                $this->_printSchedule($schedule);
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
     * @brief Display schedule
     *
     * @param Schedule $schedule  - Schedule to be edited
     */
    private function _printSchedule($schedule) {
        $startTime          = $this->m_controller->m_season->startTime;
        $endTime            = $this->m_controller->m_season->endTime;
        $interval           = new \DateInterval("PT" . $schedule->division->gameDurationMinutes . "M");
        $defaultGameTimes   = GameTime::getDefaultGameTimes($startTime, $endTime, $interval);
        $pools              = Pool::lookupBySchedule($schedule);

        foreach ($pools as $pool) {
            $games = Game::lookupByPool($pool);

            // No games for pools with cross-pool play so suppress output
            if (count($games) == 0) {
                continue;
            }

            $gamesByDateByFieldByTime = [];
            foreach ($games as $game) {
                $day        = $game->gameTime->gameDate->day;
                $startTime  = $game->gameTime->startTime;
                $fieldName  = $game->gameTime->field->facility->name . ": " . $game->gameTime->field->name;
                $gamesByDateByFieldByTime[$day][$fieldName][$startTime] = $game;
            }
            ksort($gamesByDateByFieldByTime);

            // Print table header
            print "
            <table valign='top' align='center' border='1' cellpadding='5' cellspacing='0'>";

            $colspan    = 2 + count($defaultGameTimes);
            $headerRow  = $schedule->division->name . ' ' . $schedule->division->gender . ' ' . $pool->name;
            if ($pool->gamesAgainstPool->id != $pool->id) {
                $name = $pool->gamesAgainstPool->name;
                $headerRow .= "<font color='red'> (Cross-pool play with $name)</font>";
            }

            print "
                <thead>
                <tr bgcolor='lightskyblue'>
                    <th colspan='$colspan' align='center'>$headerRow</th>
                </tr>
                <tr bgcolor='lightskyblue'>
                    <th>Day</th>
                    <th>Field</th>";

            foreach ($defaultGameTimes as $gameTime) {
                $gameTime = ltrim(substr($gameTime, 0, 5), "0");
                print "
                    <th nowrap>$gameTime</th>";
            }

            print "
                </tr>
                </thead>";


            // Print table rows
            foreach ($gamesByDateByFieldByTime as $day => $gameFieldData) {
                ksort($gameFieldData);

                $rowspan = count($gameFieldData);
                $loopIndex = 0;
                foreach ($gameFieldData as $fieldName => $gameTimeData) {
                    ksort($gameTimeData);

                    if ($loopIndex == 0) {
                        print "
                        <tr>
                            <td nowrap rowspan='$rowspan'>$day</td>";
                    } else {
                        print "
                        <tr>";
                    }
                    $loopIndex += 1;

                    print "
                    <td nowrap>$fieldName</td>";

                    foreach ($defaultGameTimes as $defaultGameTime) {
                        $entryFound = false;
                        foreach ($gameTimeData as $gameTime => $game) {
                            if ($gameTime == $defaultGameTime) {
                                $homeTeamCoach      = Coach::lookupByTeam($game->homeTeam);
                                $visitingTeamCoach  = Coach::lookupByTeam($game->visitingTeam);
                                $gender             = $game->homeTeam->division->gender;
                                $bgHTML             = $gender == 'Boys' ? "bgcolor='lightblue'" : "bgcolor='lightyellow'";
                                $gameData           = "H: " . $game->homeTeam->name . ": " . $homeTeamCoach->lastName . "<br>";
                                $gameData           .= "V: " . $game->visitingTeam->name . ": " . $visitingTeamCoach->lastName;
                                $title              = "title='" . $homeTeamCoach->name . " vs " . $visitingTeamCoach->name . "'";

                                print "
                                    <td nowrap $bgHTML $title>$gameData</td>";
                                $entryFound = true;
                            }
                        }

                        if (!$entryFound) {
                            print "
                                    <td nowrap>&nbsp</td>";
                        }
                    }

                    print "
                </tr>";
                }
            }

            print "
            </table><br>";
        }
    }
}