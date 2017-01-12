<?php

use \DAG\Domain\Schedule\Schedule;
use \DAG\Domain\Schedule\Pool;
use \DAG\Domain\Schedule\Team;
use \DAG\Domain\Schedule\GameDate;
use \DAG\Domain\Schedule\GameTime;
use \DAG\Domain\Schedule\Game;
use \DAG\Domain\Schedule\DivisionField;
use \DAG\Domain\Schedule\Coach;

/**
 * @brief Show the Schedule page and get the user to select a schedule to administer or create a new schedule.
 */
class View_AdminSchedules_Schedule extends View_AdminSchedules_Base {
    /**
     * @brief Construct the View
     *
     * @param $controller - Controller that contains data used when rendering this view.
     */
    public function __construct($controller) {
        parent::__construct(self::SCHEDULE_SCHEDULES_PAGE, $controller);
    }

    /**
     * @brief Render data for display on the page.
     */
    public function render()
    {
        $sessionId          = $this->m_controller->getSessionId();
        $divisionsSelector  = $this->getDivisionsSelector(true, false, true);

        $messageString  = $this->m_controller->m_messageString;
        if (!empty($messageString)) {
            print "
                <p style='color: green' align='center'><strong>$messageString</strong></p><br>";
        }

        print "
            <table valign='top' align='center' width='400' border='1' cellpadding='5' cellspacing='0'>
                <tr>
                    <td bgcolor='" . View_Base::CREATE_COLOR  . "'>";

        $this->_printCreateScheduleForm($sessionId, $divisionsSelector);

        print "
                    </td>
                    <td valign='top' bgcolor='" . View_Base::VIEW_COLOR  . "'>";

        $this->_printViewScheduleForm($sessionId, $divisionsSelector);

        print "
                    </td>
                    <td valign='top' bgcolor='" . View_Base::VIEW_COLOR  . "'>";

        View_AdminSchedules_Preview::_printViewSchedulesByFamily($this, self::SCHEDULE_SCHEDULES_PAGE, $sessionId, true);

        print "
                    </td>
                </tr>
            </table>
            <br><br>";

        if ($this->m_controller->m_operation == View_Base::FAMILY_VIEW or
            $this->m_controller->m_operation == View_Base::FAMILY_FIX) {
            View_AdminSchedules_Preview::printSchedulesByFamily($this);
        } else {
            $schedules = [];
            foreach ($this->m_controller->m_divisions as $division) {
                $schedules = array_merge($schedules, Schedule::lookupByDivision($division));
            }

            $dataPrinted = false;
            foreach ($schedules as $schedule) {
                $this->_printUpdateScheduleForm($schedule, $sessionId, $divisionsSelector);
                $dataPrinted = true;
            }

            if (!$dataPrinted and $this->m_controller->m_operation == View_Base::VIEW) {
                print "
                    <p align='center' style='color: red; font-size: medium'>No schedules found for the selected division.  Use 'Create New Schedule' to create a schedule.</p>";
            }
        }
    }

    /**
     * @brief Print the form to create a schedule.  Form includes the following
     *        - Schedule Name
     *        - List of Divisions
     *
     * @param $sessionId            - Session Identifier
     * @param $divisionsSelector    - List of divisionId => name
     */
    private function _printCreateScheduleForm($sessionId, $divisionsSelector) {
        print "
            <table valign='top' align='center' border='0' cellpadding='5' cellspacing='0'>
                <tr>
                    <th nowrap align='left' colspan='2'>Create New Schedule(s)</th>
                </tr>
            <form method='post' action='" . self::SCHEDULE_SCHEDULES_PAGE . $this->m_urlParams . "'>";

        $this->displayInput('Schedule Name:', 'text', View_Base::NAME, 'Schedule Name', '');
        $this->displayMultiSelector('Divisions', View_Base::DIVISION_NAMES, '', $divisionsSelector, count($divisionsSelector));
        $this->displayInput('Games Per Team:', 'int', View_Base::GAMES_PER_TEAM, 'Games Per Team', '');
        $this->displayCalendarDateSelector(4, View_Base::START_DATE, 'First Day of Schedule', $this->m_controller->m_season->startDate);
        $this->displayCalendarDateSelector(4, View_Base::END_DATE, 'Last Day of Schedule', $this->m_controller->m_season->endDate);
        $this->printDaySelector(4, NULL, $this->m_controller->m_season->daysOfWeek, "Game Days");

        // Print Create button and end form
        print "
                <tr>
                    <td align='left'>
                        <input style='background-color: yellow' name='" . View_Base::SUBMIT . "' type='submit' value='" . View_Base::CREATE . "'>
                        <input type='hidden' id='sessionId' name='sessionId' value='$sessionId'>
                    </td>
                </tr>
            </form>
            </table>";
    }

    /**
     * @brief Print the form to view a schedule.  Form includes the following
     *        - Division
     *
     * @param int       $sessionId
     * @param mixed[]   $divisionsSelector
     */
    private function _printViewScheduleForm($sessionId, $divisionsSelector) {
        // Print the start of the form to select which field to view
        print "
                    <table valign='top' align='center' border='0' cellpadding='5' cellspacing='0'>
                        <tr>
                            <th nowrap align='left' colspan='2'>View/Update Existing Schedule(s)</th>
                        </tr>
                        <form method='post' action='" . self::SCHEDULE_SCHEDULES_PAGE . $this->m_urlParams . "'>";

        $this->displayMultiSelector('Divisions', View_Base::DIVISION_NAMES, $this->m_controller->m_divisionNames, $divisionsSelector, count($divisionsSelector));

        // Print View button and end form
        print "
                        <tr>
                            <td align='left'>
                                <input style='background-color: yellow' name='" . View_Base::SUBMIT . "' type='submit' value='" . View_Base::VIEW . "'>
                                <input type='hidden' id='sessionId' name='sessionId' value='$sessionId'>
                            </td>
                        </tr>
                        </form>
                    </table>";
    }

    /**
     * @brief Print the form to update a schedule.  Form includes the following
     *        - Schedule Name
     *        - Division
     *
     * @param $schedule             - Schedule to be edited
     * @param $sessionId            - Session Identifier
     */
    private function _printUpdateScheduleForm($schedule, $sessionId) {
        // Print the start of the form to select a facility
        print "
            <table valign='top' align='center' width='400' border='1' cellpadding='5' cellspacing='0'>
                <tr>
                    <td valign='top'>";

        $this->_printUpdatePoolsForm($schedule, $sessionId);

        print "
                    </td>
                    <td valign='top'>";

        $this->_printUpdateGamesForm($schedule, $sessionId);

        print "
                    </td>
                </tr>
            </table>
            <br><br>";

    }

    /**
     * @brief Print the form to update a pools.  Form includes the following
     *        - Schedule Name
     *        - Number of Games
     *        - Update and Delete options
     *
     * @param $schedule             - Schedule to be edited
     * @param $sessionId            - Session Identifier
     */
    private function _printUpdatePoolsForm($schedule, $sessionId) {
        $errorString = ($this->m_controller->m_scheduleId == $schedule->id and $this->m_controller->m_missingAttributes > 0) ? $this->m_controller->m_name : '';

        // Print the start of the form to select a facility
        print "
            <table valign='top' align='center' border='0' cellpadding='5' cellspacing='0'>
            <form method='post' action='" . self::SCHEDULE_SCHEDULES_PAGE . $this->m_urlParams . "'>
                <tr>";

        $divisionName = $schedule->division->name . " " . $schedule->division->gender;
        print "
                    <td colspan='4' nowrap><strong>$divisionName</strong></td>
                </tr>
                <tr>
                    <td colspan='3' nowrap align='right'><strong>Schedule Name:</strong></td>";

        $this->displayInput('', 'text', View_Base::NAME, 'Schedule Name', $errorString, $schedule->name, null, 1, false, 75);

        print "
                </tr>
                    <td colspan='3' nowrap align='right'><strong>Games Per Team:</strong></td>";
        $this->displayInput('', 'int', View_Base::GAMES_PER_TEAM, 'Games Per Team', $errorString, $schedule->gamesPerTeam, null, 1, false, 75);
        print "
                </tr>";

        print "
                </tr>
                    <td colspan='3' nowrap align='right'><strong>First Day of Schedule:</strong></td>";
        $this->displayCalendarDateSelector(4, View_Base::START_DATE, '', $schedule->startDate, null, 1, 75, false);
        print "
                </tr>";

        print "
                </tr>
                    <td colspan='3' nowrap align='right'><strong>Last Day of Schedule:</strong></td>";
        $this->displayCalendarDateSelector(4, View_Base::END_DATE, '', $schedule->endDate, null, 1, 75, false);
        print "
                </tr>";

        print "
                </tr>
                    <td colspan='3' nowrap align='right'><strong>Game Days:</strong></td>";
        $this->printDaySelector(4, NULL, $schedule->daysOfWeek, "", true, false, 1, 3);
        print "
                </tr>";

        $published  = $schedule->published == 1 ? "<strong>Yes</strong>" : "<strong>No</strong>";
        $style      = $schedule->published == 1 ? "style='color: green'" : "style='color: red'";
        print "
                </tr>
                    <td colspan='3' nowrap align='right'><strong>Published:</strong></td>
                    <td $style>$published</td>
                </tr>";

        $pools = Pool::lookupBySchedule($schedule);
        $poolSelector = [];
        foreach ($pools as $pool) {
            $poolSelector[$pool->id] = $pool->name;
        }

        $gamesExist = false;
        foreach ($pools as $pool) {
            $games = Game::lookupByPool($pool);
            if (!$gamesExist) {
                $gamesExist = count($games) > 0;
            }

            print "
                <tr><td>&nbsp</td></tr>";

            print "
                <tr>
                    <td colspan='1' align='left'><strong>$pool->name</strong></td>
                    <td nowrap colspan='2' align='right' style='color: " . self::AQUA . "'><strong>Play Games Against:</strong></td>";

            $selectedPoolName   = isset($pool->gamesAgainstPool) ? $pool->gamesAgainstPool->name : $pool->name;
            $selectorColor      = isset($pool->gamesAgainstPool) ? 'salmon' : 'lightgreen';
            $name               = View_Base::CROSS_POOL_UPDATE_DATA . "[$pool->id]";
            $this->displaySelector('', $name, '', $poolSelector, $selectedPoolName, null, false, 75, 'right', '', $selectorColor);

            print "
                </tr>";

            $teams = Team::lookupByPool($pool);
            foreach ($teams as $team) {
                print "
                <tr>
                    <td>&nbsp</td>
                    <td>$team->name</td>";

                $name = View_Base::TEAM_POOL_UPDATE_DATA . "[$team->id][" . View_Base::POOL_ID . "]";
                $this->displaySelector('Pool:', $name, '', $poolSelector, $team->pool->name, null, false, 75, 'right');

                print "
                </tr>";
            }
        }

        print "
                <tr><td>&nbsp</td></tr>
                <tr>";

        $publishButton = "
                    <td align='right' colspan='1'>
                        <input title='Publish schedule everyone to see' style='background-color: lightgreen' name='" . View_Base::SUBMIT . "' type='submit' value='" . View_Base::PUBLISH . "'>
                        <input type='hidden' id='scheduleId' name='scheduleId' value='$schedule->id'>
                        <input type='hidden' id='sessionId' name='sessionId' value='$sessionId'>
                    </td>";
        $clearButton = "
                    <td align='right' colspan='1'>
                        <input title='Un-publish, Delete all games, but keep schedule name, number of games and pools' style='background-color: salmon' name='" . View_Base::SUBMIT . "' type='submit' value='" . View_Base::CLEAR . "'>
                        <input type='hidden' id='scheduleId' name='scheduleId' value='$schedule->id'>
                        <input type='hidden' id='sessionId' name='sessionId' value='$sessionId'>
                    </td>";

        $populateButton = "
                    <td align='right' colspan='1'>
                        <input title='Populate games for teams in pools' style='background-color: lightgreen' name='" . View_Base::SUBMIT . "' type='submit' value='" . View_Base::POPULATE . "'>
                        <input type='hidden' id='scheduleId' name='scheduleId' value='$schedule->id'>
                        <input type='hidden' id='sessionId' name='sessionId' value='$sessionId'>
                    </td>";

        $publishOrClearButton   = $schedule->published == 1 ? $clearButton : $publishButton;
        $populateOrClearButton  = $gamesExist ? $clearButton : $populateButton;

        // Print Submit button and end form
        print "
                    <td align='left' colspan='1'>
                        <input title='Update this schedules name, games and teams in pools' style='background-color: lightgreen' name='" . View_Base::SUBMIT . "' type='submit' value='" . View_Base::UPDATE . "'>
                        <input type='hidden' id='scheduleId' name='scheduleId' value='$schedule->id'>
                        <input type='hidden' id='sessionId' name='sessionId' value='$sessionId'>
                    </td>";


        print $populateOrClearButton;
        print $publishOrClearButton;

        print "
                    <td align='right' colspan='1'>
                        <input title='Delete this schedule, including pools and games' style='background-color: salmon' name='" . View_Base::SUBMIT . "' type='submit' value='" . View_Base::DELETE . "'>
                        <input type='hidden' id='scheduleId' name='scheduleId' value='$schedule->id'>
                        <input type='hidden' id='sessionId' name='sessionId' value='$sessionId'>
                    </td>
                </tr>
            </form>
            </table>";
    }

    /**
     * @brief Print the form to update a games.  Form includes the following
     *        - Days of games
     *        - Times of games
     *        - Teams and field
     *
     * @param Schedule      $schedule  - Schedule to be edited
     * @param int           $sessionId - Session Identifier
     */
    private function _printUpdateGamesForm($schedule, $sessionId) {
        $startTime          = $this->m_controller->m_season->startTime;
        $endTime            = $this->m_controller->m_season->endTime;
        $interval           = new \DateInterval("PT" . $schedule->division->gameDurationMinutes . "M");
        $defaultGameTimes   = GameTime::getDefaultGameTimes($startTime, $endTime, $interval);
        $pools              = Pool::lookupBySchedule($schedule);

        foreach ($pools as $pool) {
            $games = Game::lookupByPool($pool);
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
            /*<form method='post' action='" . self::SCHEDULE_SCHEDULES_PAGE . $this->m_urlParams . "'>*/

            $colspan    = 2 + count($defaultGameTimes);
            $headerRow  = $schedule->division->name . ' ' . $schedule->division->gender . ' ' . $pool->name;
            if ($pool->gamesAgainstPool->id != $pool->id) {
                $name = $pool->gamesAgainstPool->name;
                $headerRow .= "<font color='red'> (Cross-pool play with $name)</font>";
            }

            print "
                <thead bgcolor='lightskyblue'>
                <tr>
                    <th colspan='$colspan' align='center'>$headerRow</th>
                </tr>
                <tr>
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
                                $gameData           = $game->homeTeam->name . " vs " . $game->visitingTeam->name;
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

            /*
            // Print Update and Delete buttons and end form
            // TODO, need a way to distinguish this UPDATE/DELETE from POOL update/delete
            print "
                    <tr>
                        <td align='left' colspan='2'>
                            <input style='background-color: lightgreen' name='" . View_Base::SUBMIT . "' type='submit' value='" . View_Base::UPDATE . "'>
                            <input type='hidden' id='scheduleId' name='scheduleId' value='$schedule->id'>
                            <input type='hidden' id='sessionId' name='sessionId' value='$sessionId'>
                        </td>
                        <td align='right' colspan='2'>
                            <input style='background-color: salmon' name='" . View_Base::SUBMIT . "' type='submit' value='" . View_Base::DELETE . "'>
                            <input type='hidden' id='scheduleId' name='scheduleId' value='$schedule->id'>
                            <input type='hidden' id='sessionId' name='sessionId' value='$sessionId'>
                        </td>
                    </tr>
                </form>
            */
            print "
            </table><br>";
        }
    }
}