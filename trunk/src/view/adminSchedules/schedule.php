<?php

use \DAG\Domain\Schedule\Schedule;
use \DAG\Domain\Schedule\Pool;
use \DAG\Domain\Schedule\Team;
use \DAG\Domain\Schedule\GameTime;
use \DAG\Domain\Schedule\DivisionField;
use \DAG\Domain\Schedule\Coach;
use \DAG\Orm\Schedule\ScheduleOrm;
use \DAG\Domain\Schedule\Flight;

/**
 * @brief Show the Schedule page and get the user to select a schedule to administer or create a new schedule.
 */
class View_AdminSchedules_Schedule extends View_AdminSchedules_Base {
    const SCHEDULE  = 'schedule';
    const FLIGHT    = 'flight';
    const FLIGHTS   = 'flights';
    const POOLS     = 'pools';

    /**
     * @brief Construct the View
     *
     * @param Controller_Base $controller - Controller that contains data used when rendering this view.
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
        $gameDateSelector   = $this->getGameDateSelector();

        $messageString  = $this->m_controller->m_messageString;
        if (!empty($messageString)) {
            print "
                <p style='color: green' align='center'><strong>$messageString</strong></p><br>";
        }

        print "
            <table valign='top' align='center' width='400' border='1' cellpadding='5' cellspacing='0'>
                <tr>
                    <td bgcolor='" . View_Base::CREATE_COLOR  . "'>";

        $this->_printCreateScheduleForm($sessionId, $divisionsSelector, $gameDateSelector);

        print "
                    </td>
                    <td valign='top' bgcolor='" . View_Base::VIEW_COLOR  . "'>";

        $this->_printViewScheduleForm($sessionId, $divisionsSelector, $this->m_controller->m_showPublishedScheduled);

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
                // If not VIEW then only show schedules just created
                if (($this->m_controller->m_operation == View_Base::CREATE
                    or $this->m_controller->m_operation == View_Base::UPDATE)
                    and $schedule->name == $this->m_controller->m_name) {
                    $this->_printUpdateScheduleForm($schedule, $gameDateSelector, $sessionId);
                    $dataPrinted = true;
                }

                // If View then only show published schedules if checked
                if (($this->m_controller->m_operation == View_Base::VIEW)
                    and ($this->m_controller->m_showPublishedSchedules or !$schedule->published)) {
                    $this->_printUpdateScheduleForm($schedule, $gameDateSelector, $sessionId);
                    $dataPrinted = true;
                }

                // View schedule if it was just published
                if (($this->m_controller->m_operation == View_Base::POPULATE
                    or $this->m_controller->m_operation == View_Base::PUBLISH
                    or $this->m_controller->m_operation == View_Base::UN_PUBLISH
                    or $this->m_controller->m_operation == View_Base::CLEAR
                    or $this->m_controller->m_operation == View_Base::MOVE
                    or $this->m_controller->m_operation == View_Base::SWAP
                    or $this->m_controller->m_operation == View_Base::ALTER
                    or $this->m_controller->m_operation == View_Base::TOGGLE
                    or $this->m_controller->m_operation == View_Base::CREATE_FLIGHT
                    or $this->m_controller->m_operation == View_Base::DELETE_FLIGHT
                    or $this->m_controller->m_operation == View_Base::CREATE_POOL
                    or $this->m_controller->m_operation == View_Base::DELETE_POOL
                    or $this->m_controller->m_operation == View_Base::ADD)
                    and $this->m_controller->m_scheduleId == $schedule->id) {

                    $this->_printUpdateScheduleForm($schedule, $gameDateSelector, $sessionId);
                    $dataPrinted = true;
                }
            }

            if (!$dataPrinted and $this->m_controller->m_operation == View_Base::VIEW) {
                if ($this->m_controller->m_showPublishedSchedules) {
                    print "
                    <p align='center' style='color: red; font-size: medium'>No schedules found for the selected division.  Use 'Create New Schedule' to create a schedule.</p>";
                } else {
                    print "
                    <p align='center' style='color: red; font-size: medium'>No unpublished schedules found for the selected division.  Try selecting the 'Show Published Scheduled' checkbox.</p>";
                }
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
     * @param $gameDateSelector     - List of gameDateId => day
     */
    private function _printCreateScheduleForm($sessionId, $divisionsSelector, $gameDateSelector) {
        print "
            <table valign='top' align='center' border='0' cellpadding='5' cellspacing='0'>
                <tr>
                    <th nowrap align='left' colspan='2'>Create New Schedule(s)</th>
                </tr>
            <form method='post' action='" . self::SCHEDULE_SCHEDULES_PAGE . $this->m_urlParams . "'>";

        $startTime  = $this->m_controller->m_season->startTime;
        $endTime    = $this->m_controller->m_season->endTime;
        $name       = $this->m_controller->m_season->name;

        print "
                <tr>";
        $this->displayInput('Schedule Name:', 'text', View_Base::NAME, 'Schedule Name', '', $name, null, 1, false, 135, false);
        $this->displayInput('Games Per Team:', 'int', View_Base::GAMES_PER_TEAM, 'Games Per Team', '', '', null, 1, false, 135, false);
        print "
                </tr>
                <tr>";
        $this->displayMultiSelector('Divisions', View_Base::DIVISION_NAMES, '', $divisionsSelector, count($divisionsSelector), null, 1, false, 6);
        $this->displaySelector('First Day of Schedule', View_Base::START_DATE, '', $gameDateSelector, "", null, false, 135);
        // $this->displayCalendarDateSelector(4, View_Base::START_DATE, 'First Day of Schedule', $this->m_controller->m_season->startDate, null, 1, 135, false);
        print "
                </tr>
                <tr>";
        $this->displaySelector('Last Day of Schedule', View_Base::END_DATE, '', $gameDateSelector, end($gameDateSelector), null, false, 135);
        // $this->displayCalendarDateSelector(4, View_Base::END_DATE, 'Last Day of Schedule', $this->m_controller->m_season->endDate, null, 1, 135, false);
        print "
                </tr>
                <tr>";
        $this->printGameTimeSelectors(4, $startTime, $endTime, null, 1, "First Game Of Day", "Last Game of Day");
        print "
                </tr>";
        print "
                </tr>
                <tr>";

        $selectorData = ScheduleOrm::$scheduleTypes;
        $this->displayRadioSelector("Type of Schedule", View_Base::SCHEDULE_TYPE, $selectorData, 'League', NULL, 1, false);

        print "
                </tr>";
        $this->printDaySelector(4, NULL, $this->m_controller->m_season->daysOfWeek, "Game Days", true, true, 7, 1, 3);

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
     * @param bool      $showPublishedScheduled
     */
    private function _printViewScheduleForm($sessionId, $divisionsSelector, $showPublishedScheduled) {
        // Print the start of the form to select which field to view
        print "
                    <table valign='top' align='center' border='0' cellpadding='5' cellspacing='0'>
                        <tr>
                            <th nowrap align='left' colspan='2'>View/Update Existing Schedule(s)</th>
                        </tr>
                        <form method='post' action='" . self::SCHEDULE_SCHEDULES_PAGE . $this->m_urlParams . "'>";

        $this->displayMultiSelector('Divisions', View_Base::DIVISION_NAMES, $this->m_controller->m_divisionNames, $divisionsSelector, count($divisionsSelector));
        $this->printCheckboxSelector(View_Base::SHOW_PUBLISHED, "Show Published Schedules", $showPublishedScheduled, 2);

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
     * @param $gameDateSelector     - List of gameDateId => day
     * @param $sessionId            - Session Identifier
     */
    private function _printUpdateScheduleForm($schedule, $gameDateSelector, $sessionId) {
\DAG\Framework\Utils\TimeUtils::time_elapsed("Prime");
        $flights        = Flight::lookupBySchedule($schedule);
        $pools          = Pool::lookupBySchedule($schedule);
\DAG\Framework\Utils\TimeUtils::time_elapsed("Pools Lookup");
        $gameIds        = [];
        $gamesByPoolId  = [];
        foreach ($pools as $pool) {
            $gamesByPoolId[$pool->id]   = 0;
        }
\DAG\Framework\Utils\TimeUtils::time_elapsed("Game lookup");

        $divisionFields = DivisionField::lookupByDivision($schedule->division);
\DAG\Framework\Utils\TimeUtils::time_elapsed("DivisionField lookup");
        $gameTimes      = [];
        $fieldIds       = [];
        foreach ($divisionFields as $divisionField) {
            $fieldIds[$divisionField->field->id] = $divisionField->field->facility->name . ": " . $divisionField->field->name;
            $gameTimes = array_merge($gameTimes, GameTime::lookupByField($divisionField->field));
        }
\DAG\Framework\Utils\TimeUtils::time_elapsed("GameTime lookup");

        $startTime          = $this->m_controller->m_season->startTime;
        $endTime            = $this->m_controller->m_season->endTime;
        $interval           = new \DateInterval("PT" . $schedule->division->gameDurationMinutes . "M");
        $gameTimesSelector  = [];
        $defaultGameTimes   = GameTime::getDefaultGameTimes($startTime, $endTime, $interval);
        foreach ($defaultGameTimes as $gameTime) {
            $readableGameTime               = ltrim(substr($gameTime, 0, 5), "0");
            $gameTimesSelector[$gameTime]   = $readableGameTime;
        }
\DAG\Framework\Utils\TimeUtils::time_elapsed("GameTimeSelector lookup");

        // Order games by day, field and startTime
        $gamesByDateByFieldByTime   = [];
        $fieldSelector              = [];
        $teamSelector               = [];
        $fieldSelector[0]           = 'Any';
        foreach ($gameTimes as $gameTime) {
            if ($gameTime->gameDate->day >= $schedule->startDate
                and $gameTime->gameDate->day <= $schedule->endDate) {
                $day                                                    = $gameTime->gameDate->day;
                $startTime                                              = $gameTime->startTime;
                $fieldName                                              = $gameTime->field->facility->name . ": " . $gameTime->field->name;
                $fieldSelector[$gameTime->field->id]                    = $fieldName;
                $gamesByDateByFieldByTime[$day][$fieldName][$startTime] = $gameTime->game;
                if (isset($gameTime->game)) {
                    $gameIds[$gameTime->game->id]               = $gameTime->game->id;
                    $gamesByPoolId[$gameTime->game->pool->id]   += 1;

                    $homeTeamCoachName      = '';
                    $visitingTeamCoachName  = '';
                    if (isset($gameTime->game->homeTeam)) {
                        $homeTeamCoachName      = ': ' . Coach::lookupByTeam($gameTime->game->homeTeam)->shortName;
                        $visitingTeamCoachName  = ': ' . Coach::lookupByTeam($gameTime->game->visitingTeam)->shortName;
                    }

                    $teamSelector[$gameTime->game->homeTeam->id]        = $gameTime->game->homeTeam->nameId . $homeTeamCoachName;
                    $teamSelector[$gameTime->game->visitingTeam->id]    = $gameTime->game->visitingTeam->nameId . $visitingTeamCoachName;
                }
            }
        }
        ksort($gamesByDateByFieldByTime);
\DAG\Framework\Utils\TimeUtils::time_elapsed("GamesByDateByFieldByTime population/sort");

        // Print the start of the form to select a facility
        print "
            <table valign='top' align='center' width='400' border='1' cellpadding='5' cellspacing='0'>
                <tr>
                    <td valign='top'>
                        <table>
                            <tr>
                                <td>";

        $this->_printUpdatePoolsForm($schedule, $pools, $gamesByPoolId, $gameDateSelector, $sessionId);

        print "
                                </td>
                            </tr>
                            <tr><td>&nbsp</td></tr>
                            <tr>
                                <td>";

        $this->_printCreateFlightForm($schedule->id, $flights, $sessionId);

        print "
                                </td>
                            </tr>
                            <tr><td>&nbsp</td></tr>
                            <tr>
                                <td>";

        $this->_printCreateDeletePoolForm($schedule->id, $flights, $pools, $sessionId);

        print "
                                </td>
                            </tr>
                            <tr><td>&nbsp</td></tr>
                            <tr>
                                <td>";

        $this->_printMoveGameForm($schedule->id, $gameIds, $fieldIds, $gameTimesSelector, $sessionId);

        print "
                                </td>
                            </tr>
                            <tr><td>&nbsp</td></tr>
                            <tr>
                                <td>";

        $this->_printSwapGameForm($schedule->id, $gameIds, $sessionId);

        print "
                                </td>
                            </tr>
                            <tr><td>&nbsp</td></tr>
                            <tr>
                                <td>";

        $this->_printLockGameForm($schedule->id, $gameIds, $sessionId);

        print "
                                </td>
                            </tr>
                            <tr><td>&nbsp</td></tr>
                            <tr>
                                <td>";

        $this->_printAlterTeamGames($schedule->id, $teamSelector, $fieldSelector, $gameDateSelector, $sessionId);

        print "
                                </td>
                            </tr>
                            <tr><td>&nbsp</td></tr>
                            <tr>
                                <td>";

        $this->_printAddGame($schedule, $gameTimes, $sessionId);

        print "
                                </td>
                            </tr>
                        </table>
                    </td>
                    <td valign='top'>";

        $this->_printGames($schedule, $pools, $defaultGameTimes, $gamesByDateByFieldByTime);

        print "
                    </td>
                </tr>
            </table>
            <br><br>";

    }
    /**
     * @brief Print Medal Round Game Options
     *
     * @param int       $flightId       - Flight identifier
     * @param int       $fifthSixth     - 0 or 1
     * @param int       $semiFinals     - 0 or 1
     * @param int       $thirdFourth    - 0 or 1
     * @param int       $championship   - 0 or 1
     * @param string    $label          - defaults to empty (caller displays the label)
     * @param bool      $newRow         - defaults to true
     * @param int       $itemsPerRow    - defaults to 1
     * @param int       $emptyCells     - defaults to 0; number of empty cells before finishing off row
     */
    protected function printMedalRoundSelector(
        $flightId,
        $fifthSixth,
        $semiFinals,
        $thirdFourth,
        $championship,
        $label = '',
        $newRow = true,
        $itemsPerRow = 1,
        $emptyCells = 0)
    {
        $medalRoundData = array(
            View_Base::INCLUDE_5TH_6TH_GAME         => (isset($fifthSixth) and $fifthSixth == 1) ? 'checked' : '',
            View_Base::INCLUDE_SEMI_FINAL_GAMES     => (isset($semiFinals) and $semiFinals == 1) ? 'checked' : '',
            View_Base::INCLUDE_3RD_4TH_GAME         => (isset($thirdFourth) and $thirdFourth == 1) ? 'checked' : '',
            View_Base::INCLUDE_CHAMPIONSHIP_GAME    => (isset($championship) and $championship == 1) ? 'checked' : '',
        );

        if ($newRow) {
            print "
                <tr>";

            for ($i = 0; $i < $emptyCells; $i++) {
                print "
                    <td>&nbsp</td>";
            }
        }

        if (!empty($label)) {
            print "
                    <td nowrap><font color='" . View_Base::AQUA . "'><b>$label:&nbsp</b></font></td>";
        }

        // Print medal round data
        $currentItemsPrinted = 0;

        foreach ($medalRoundData as $gameType => $checked) {
            $name = View_Base::FLIGHT_UPDATE_DATA . "[$flightId][$gameType]";
            if ($currentItemsPrinted == $itemsPerRow) {
                print "
                </tr>
                <tr>";

                for ($i = 0; $i < $emptyCells; $i++) {
                    print "
                    <td>&nbsp</td>";
                }

                $currentItemsPrinted = 0;
            }

            print "
                    <td nowrap>
                        <nobr><input type=checkbox name='$name' id='$gameType' value='1' $checked>$gameType</nobr>
                    </td>";

            $currentItemsPrinted += 1;
        }

        if ($newRow) {
            print "
                </tr>";
        }
    }

    /**
     * @brief Print the form to update a pools.  Form includes the following
     *        - Schedule Name
     *        - Number of Games
     *        - Update and Delete options
     *
     * @param Schedule  $schedule           - Schedule to be edited
     * @param Pool[]    $pools              - Pools in schedule
     * @param array     $gamesByPoolId      - Games by poolId
     * @param array     $gameDateSelector   - List of gameDateId => day
     * @param int       $sessionId          - Session Identifier
     */
    private function _printUpdatePoolsForm($schedule, $pools, $gamesByPoolId, $gameDateSelector, $sessionId)
    {
        $errorString = ($this->m_controller->m_scheduleId == $schedule->id and $this->m_controller->m_missingAttributes > 0) ? $this->m_controller->m_name : '';
        $startTime   = $schedule->startTime;
        $endTime     = $schedule->endTime;

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

        $this->displayInput('', 'text', View_Base::NAME, 'Schedule Name', $errorString, $schedule->name, null, 1, false, 120);

        print "
                </tr>
                    <td colspan='3' nowrap align='right'><strong>Games Per Team:</strong></td>";
        $this->displayInput('', 'int', View_Base::GAMES_PER_TEAM, 'Games Per Team', $errorString, $schedule->gamesPerTeam, null, 1, false, 75);
        print "
                </tr>";

        print "
                </tr>
                    <td colspan='3' nowrap align='right'><strong>First Day of Schedule:</strong></td>";
        $this->displaySelector('', View_Base::START_DATE, '', $gameDateSelector, $schedule->startDate, null, false, 100);
        print "
                </tr>";

        print "
                </tr>
                    <td colspan='3' nowrap align='right'><strong>Last Day of Schedule:</strong></td>";
        $this->displaySelector('', View_Base::END_DATE, '', $gameDateSelector, $schedule->endDate, null, false, 100);
        print "
                </tr>";

        print "
                </tr>
                    <td colspan='3' nowrap align='right'><strong>First Game of Day:</strong></td>";
        $this->printTimeSelector(View_Base::START_TIME, "", $startTime);
        print "
                </tr>";

        print "
                </tr>
                    <td colspan='3' nowrap align='right'><strong>Last Game of Day:</strong></td>";
        $this->printTimeSelector(View_Base::END_TIME, "", $endTime);
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

        $poolSelector = [];
        foreach ($pools as $pool) {
            $poolSelector[$pool->id] = $pool->fullName;
        }

        $gamesExist = false;
        $flight     = null;
        foreach ($pools as $pool) {
            if (!$gamesExist) {
                $gamesExist = $gamesByPoolId[$pool->id] > 0;
            }

            print "
                <tr><td>&nbsp</td></tr>";

            if (!isset($flight) or $flight->id != $pool->flight->id) {
                $flight = $pool->flight;
                print "
                <tr style='border: inset'>
                    <td colspan='5'>";

                print "
                        <table valign='top' align='left' border='0' cellpadding='5' cellspacing='0'>
                            <tr>
                                <td colspan='1' align='left'><strong>Flight:</strong></td>";

                $name = View_Base::FLIGHT_UPDATE_DATA . "[$flight->id][" . View_Base::NAME . "]";
                $this->displayInput('', 'text', $name, 'Flight Name', '', $flight->name, null, 1, false, 75);

                $name = View_Base::FLIGHT_UPDATE_DATA . "[$flight->id][" . View_Base::FLIGHT_SCHEDULE_GAMES . "]";
                $this->printCheckboxSelector($name, "Schedule Games", $flight->scheduleGames == 1, 1, false);

                if ($pool->schedule->scheduleType == ScheduleOrm::SCHEDULE_TYPE_TOURNAMENT) {
                    print "
                            </tr>
                            <tr>
                                <td colspan='2' style='color: " . View_Base::AQUA . "'><strong>Include Medal Round Games:</strong></td>
                            </tr>";

                    $this->printMedalRoundSelector(
                        $flight->id,
                        $flight->include5th6thGame,
                        $flight->includeSemiFinalGames,
                        $flight->include3rd4thGame,
                        $flight->includeChampionshipGame,
                        "",
                        true,
                        2,
                        1);
                }

                print "
                        </table>
                    </td>
                </tr>";
            }

            print "
                <tr>
                    <td colspan='1' align='left'><strong>$pool->name</strong></td>
                    <td nowrap colspan='2' align='right' style='color: " . self::AQUA . "'><strong>Play Games Against:</strong></td>";

            $selectedPoolName   = isset($pool->gamesAgainstPool) ? $pool->gamesAgainstPool->fullName : $pool->fullName;
            $selectorColor      = isset($pool->gamesAgainstPool) ? 'salmon' : 'lightgreen';
            $name               = View_Base::CROSS_POOL_UPDATE_DATA . "[$pool->id]";
            $this->displaySelector('', $name, '', $poolSelector, $selectedPoolName, null, false, 120, 'right', '', $selectorColor);

            print "
                </tr>";

            $teams = Team::lookupByPool($pool);
            foreach ($teams as $team) {
                $coach = Coach::lookupByTeam($team);
                $title = "title='$coach->name'";
                print "
                <tr>
                    <td>&nbsp</td>
                    <td $title nowrap>$team->nameId</td>";

                $name = View_Base::TEAM_POOL_UPDATE_DATA . "[$team->id][" . View_Base::POOL_ID . "]";
                $this->displaySelector('Pool:', $name, '', $poolSelector, $team->pool->fullName, null, false, 120, 'right');

                print "
                </tr>";
            }
        }

        print "
                <tr><td>&nbsp</td></tr>
                <tr>";

        $publishButton = "
                    <td align='right' colspan='1'>
                        <input title='Publish schedule for everyone to see' style='background-color: lightgreen' name='" . View_Base::SUBMIT . "' type='submit' value='" . View_Base::PUBLISH . "'>
                        <input type='hidden' id='scheduleId' name='scheduleId' value='$schedule->id'>
                        <input type='hidden' id='sessionId' name='sessionId' value='$sessionId'>
                    </td>";

        $unPublishButton = "
                    <td align='right' colspan='1'>
                        <input title='Un-Publish schedule so you can modify' style='background-color: lightgreen' name='" . View_Base::SUBMIT . "' type='submit' value='" . View_Base::UN_PUBLISH . "'>
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

        $publishOrUnPublishButton   = $schedule->published == 1 ? $unPublishButton : $publishButton;
        $populateOrClearButton      = $gamesExist ? $clearButton : $populateButton;

        // Print Submit button and end form
        print "
                    <td align='left' colspan='1'>
                        <input title='Update this schedules name, games and teams in pools' style='background-color: lightgreen' name='" . View_Base::SUBMIT . "' type='submit' value='" . View_Base::UPDATE . "'>
                        <input type='hidden' id='scheduleId' name='scheduleId' value='$schedule->id'>
                        <input type='hidden' id='sessionId' name='sessionId' value='$sessionId'>
                    </td>";


        print $populateOrClearButton;
        print $publishOrUnPublishButton;

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
     * Print form to create a flight
     *
     * @param int       $scheduleId
     * @param Flight[]  $flights
     * @param int       $sessionId
     * @param int       $javaScriptClassIdentifier - Used for expand/collapse
     */
    private function _printCreateFlightForm($scheduleId, $flights, $sessionId, $javaScriptClassIdentifier = 5)
    {
        $expandContract = "expandContract$javaScriptClassIdentifier";
        $collapsible    = "collapsible$javaScriptClassIdentifier";
        $flightSelector   = [];
        foreach ($flights as $flight) {
            $flightSelector[$flight->id] = $flight->name;
        }

        print "
            <table valign='top' align='left' border='1' cellpadding='5' cellspacing='0'><tr><td>
            <table valign='top' align='left' border='0' cellpadding='5' cellspacing='0'>
            <tr class='$expandContract'>
                <td colspan='2' style='color: " . View_Base::AQUA . "; font-size: medium'><strong>Create Flight</strong></td>
            </tr>";

        print "
            <form method='post' action='" . self::SCHEDULE_SCHEDULES_PAGE . $this->m_urlParams . "'>
                <tr class='$collapsible'>";

        $this->displayInput('', 'text', View_Base::FLIGHT_NAME, 'Flight Name', '', '', null, 1, false, 75, false, true);

        print "
                    <td align='left' colspan='1' title='Create a new flight'>
                        <input style='background-color: lightgreen' name='" . View_Base::SUBMIT . "' type='submit' value='" . View_Base::CREATE_FLIGHT . "'>
                        <input type='hidden' id='" . View_Base::SCHEDULE_ID . "' name='" . View_Base::SCHEDULE_ID . "' value='$scheduleId'>
                        <input type='hidden' id='sessionId' name='sessionId' value='$sessionId'>
                    </td>
                </tr>
            </form>
            </table>
            </td></tr>
            </table>";
    }

    /**
     * Print form to create/delete a pool
     *
     * @param int       $scheduleId
     * @param Flight[]  $flights
     * @param Pool[]    $pools
     * @param int       $sessionId
     * @param int       $javaScriptClassIdentifier - Used for expand/collapse
     */
    private function _printCreateDeletePoolForm($scheduleId, $flights, $pools, $sessionId, $javaScriptClassIdentifier = 6)
    {
        $expandContract = "expandContract$javaScriptClassIdentifier";
        $collapsible    = "collapsible$javaScriptClassIdentifier";
        $flightSelector = [];
        $poolSelector   = [];

        foreach ($flights as $flight) {
            $flightSelector[$flight->id] = $flight->name;
        }

        foreach ($pools as $pool) {
            $poolSelector[$pool->id] = $pool->fullName;
        }

        print "
            <table valign='top' align='left' border='1' cellpadding='5' cellspacing='0'><tr><td>
            <table valign='top' align='left' border='0' cellpadding='5' cellspacing='0'>
            <tr class='$expandContract'>
                <td colspan='2' style='color: " . View_Base::AQUA . "; font-size: medium'><strong>Create/Delete Pool</strong></td>
            </tr>";

        print "
            <form method='post' action='" . self::SCHEDULE_SCHEDULES_PAGE . $this->m_urlParams . "'>";

        $this->displayInput('', 'text', View_Base::POOL_NAME, 'Pool Name', '', '', $collapsible, 2, true, 75, false, true);

        print "
                <tr class='$collapsible'>";

        $this->displaySelector('', View_Base::FLIGHT_ID, '', $flightSelector, '', $collapsible, false, 120, 'right', 'Select Flight');

        print "
                    <td align='left' colspan='1' title='Create a new pool'>
                        <input style='background-color: lightgreen' name='" . View_Base::SUBMIT . "' type='submit' value='" . View_Base::CREATE_POOL . "'>
                        <input type='hidden' id='" . View_Base::SCHEDULE_ID . "' name='" . View_Base::SCHEDULE_ID . "' value='$scheduleId'>
                        <input type='hidden' id='sessionId' name='sessionId' value='$sessionId'>
                    </td>
                </tr>
            </form>
            <form method='post' action='" . self::SCHEDULE_SCHEDULES_PAGE . $this->m_urlParams . "'>
                <tr class='$collapsible'>";

        $this->displaySelector('', View_Base::POOL_ID, '', $poolSelector, '', $collapsible, false, 120, 'right', 'Select Pool');

        print "
                    <td align='left' colspan='1' title='Create a new pool'>
                        <input style='background-color: salmon' name='" . View_Base::SUBMIT . "' type='submit' value='" . View_Base::DELETE_POOL . "'>
                        <input type='hidden' id='" . View_Base::SCHEDULE_ID . "' name='" . View_Base::SCHEDULE_ID . "' value='$scheduleId'>
                        <input type='hidden' id='sessionId' name='sessionId' value='$sessionId'>
                    </td>
                </tr>
            </form>

            </table>
            </td></tr>
            </table>";
    }

    /**
     * Print form to move a game to an open time-slot
     *
     * @param int   $scheduleId
     * @param array $gameIds
     * @param array $fieldIds
     * @param array $gameTimeSelector
     * @param int   $sessionId
     * @param int   $javaScriptClassIdentifier - Used for expand/collapse
     */
    private function _printMoveGameForm($scheduleId, $gameIds, $fieldIds, $gameTimeSelector, $sessionId, $javaScriptClassIdentifier = 1)
    {
        $expandContract = "expandContract$javaScriptClassIdentifier";
        $collapsible    = "collapsible$javaScriptClassIdentifier";

        // Print Move controls and button
        print "
            <table valign='top' align='left' border='1' cellpadding='5' cellspacing='0'><tr><td>
            <table valign='top' align='left' border='0' cellpadding='5' cellspacing='0'>
            <form method='post' action='" . self::SCHEDULE_SCHEDULES_PAGE . $this->m_urlParams . "'>
            <tr class='$expandContract'>
                <td colspan='2' style='color: " . View_Base::AQUA . "; font-size: medium'><strong>Move game to an open time-slot</strong></td>
            </tr>";

        asort($gameIds);
        $this->displaySelector('Game Id To Move:', View_Base::GAME_ID, '', $gameIds, '', $collapsible, true, 150, 'left', 'Select Game Id to Move');
        $this->displaySelector('New Field:', View_Base::FIELD_ID, '', $fieldIds, '', $collapsible, true, 150, 'left', 'Select New Field');
        $this->displaySelector('New Game Time:', View_Base::GAME_TIME, '', $gameTimeSelector, '', $collapsible, true, 150, 'left', 'Select New Game Time');

        print "
                <tr class='$collapsible'>
                    <td align='left' colspan='2' title='Move game to an open time slot'>
                        <input style='background-color: lightgreen' name='" . View_Base::SUBMIT . "' type='submit' value='" . View_Base::MOVE . "'>
                        <input type='hidden' id='" . View_Base::SCHEDULE_ID . "' name='" . View_Base::SCHEDULE_ID . "' value='$scheduleId'>
                        <input type='hidden' id='sessionId' name='sessionId' value='$sessionId'>
                    </td>
                </tr>
            </form>
            </table>
            </td></tr>
            </table>";
    }

    /**
     * Print form to swap two games
     *
     * @param int   $scheduleId
     * @param array $gameIds
     * @param int   $sessionId
     * @param int   $javaScriptClassIdentifier - Used for expand/collapse
     */
    private function _printSwapGameForm($scheduleId, $gameIds, $sessionId, $javaScriptClassIdentifier = 2)
    {
        $expandContract = "expandContract$javaScriptClassIdentifier";
        $collapsible    = "collapsible$javaScriptClassIdentifier";

        // Print Move controls and button
        print "
            <table valign='top' align='left' border='1' cellpadding='5' cellspacing='0'><tr><td>
            <table valign='top' align='left' border='0' cellpadding='5' cellspacing='0'>
            <form method='post' action='" . self::SCHEDULE_SCHEDULES_PAGE . $this->m_urlParams . "'>
            <tr class='$expandContract'>
                <td colspan='2' style='color: " . View_Base::AQUA . "; font-size: medium'><strong>Swap two games</strong></td>
            </tr>";

        asort($gameIds);
        $this->displaySelector('Game Id1:', View_Base::GAME_ID1, '', $gameIds, '', $collapsible, true, 150, 'left', 'Primary Game Id');
        $this->displaySelector('Game Id2:', View_Base::GAME_ID2, '', $gameIds, '', $collapsible, true, 150, 'left', 'Secondary Game Id');

        print "
                <tr class='$collapsible'>
                    <td align='left' colspan='2' title='Swap two games'>
                        <input style='background-color: lightgreen' name='" . View_Base::SUBMIT . "' type='submit' value='" . View_Base::SWAP . "'>
                        <input type='hidden' id='" . View_Base::SCHEDULE_ID . "' name='" . View_Base::SCHEDULE_ID . "' value='$scheduleId'>
                        <input type='hidden' id='sessionId' name='sessionId' value='$sessionId'>
                    </td>
                </tr>
            </form>
            </table>
            </td></tr>
            </table>";
    }

    /**
     * Print form to lock/unlock a game
     *
     * @param int   $scheduleId
     * @param array $gameIds
     * @param int   $sessionId
     * @param int   $javaScriptClassIdentifier - Used for expand/collapse
     */
    private function _printLockGameForm($scheduleId, $gameIds, $sessionId, $javaScriptClassIdentifier = 3)
    {
        $expandContract = "expandContract$javaScriptClassIdentifier";
        $collapsible    = "collapsible$javaScriptClassIdentifier";

        // Print Move controls and button
        print "
            <table valign='top' align='left' border='1' cellpadding='5' cellspacing='0'><tr><td>
            <table valign='top' align='left' border='0' cellpadding='5' cellspacing='0'>
            <form method='post' action='" . self::SCHEDULE_SCHEDULES_PAGE . $this->m_urlParams . "'>
            <tr class='$expandContract'>
                <td colspan='2' style='color: " . View_Base::AQUA . "; font-size: medium' title='Locking a game prevents it from being moved or swapped with another game'><strong>Lock/Unlock Game</strong></td>
            </tr>";

        asort($gameIds);
        $this->displaySelector('Game Id:', View_Base::GAME_ID, '', $gameIds, '', $collapsible, true, 150, 'left', 'Game Id');

        print "
                <tr class='$collapsible'>
                    <td align='left' colspan='2' title='Lock game if unlocked; otherwise unlock it'>
                        <input style='background-color: orange' name='" . View_Base::SUBMIT . "' type='submit' value='" . View_Base::TOGGLE . "'>
                        <input type='hidden' id='" . View_Base::SCHEDULE_ID . "' name='" . View_Base::SCHEDULE_ID . "' value='$scheduleId'>
                        <input type='hidden' id='sessionId' name='sessionId' value='$sessionId'>
                    </td>
                </tr>
            </form>
            </table>
            </td></tr>
            </table>";
    }

    /**
     * Print form to modify game times for a team
     *
     * @param int   $scheduleId
     * @param array $teamSelector
     * @param array $fieldSelector
     * @param array $gameDateSelector - List of gameDateId => day
     * @param int   $sessionId
     * @param int   $javaScriptClassIdentifier - Used for expand/collapse
     */
    private function _printAlterTeamGames($scheduleId, $teamSelector, $fieldSelector, $gameDateSelector, $sessionId, $javaScriptClassIdentifier = 4)
    {
        $expandContract = "expandContract$javaScriptClassIdentifier";
        $collapsible    = "collapsible$javaScriptClassIdentifier";

        // Print controls and button to support moving a teams games during a date range to between a selected time
        // range and field.
        print "
            <table valign='top' align='left' border='1' cellpadding='5' cellspacing='0'><tr><td>
            <table valign='top' align='left' border='0' cellpadding='5' cellspacing='0'>
            <form method='post' action='" . self::SCHEDULE_SCHEDULES_PAGE . $this->m_urlParams . "'>
            <tr class='$expandContract'>
                <td colspan='2' style='color: " . View_Base::AQUA . "; font-size: medium' title='Alter games for a team based on date range, time range and field. Games will be locked.'><strong>Alter Team Game(s)</strong></td>
            </tr>";

        asort($teamSelector);
        $this->displaySelector('Team:', View_Base::TEAM_ID, '', $teamSelector, '', $collapsible, true, 150, 'left', 'Select Team');
        $this->displaySelector('From Date', View_Base::START_DATE, '', $gameDateSelector, '', $collapsible);
        $this->displaySelector('To Date', View_Base::END_DATE, '', $gameDateSelector, end($gameDateSelector), $collapsible);
        $this->printTimeSelector(View_Base::START_TIME, "From Time", "07:00:00", 1, $collapsible);
        $this->printTimeSelector(View_Base::END_TIME, "To Time", "19:00:00", 1, $collapsible);
        $this->displaySelector('Field:', View_Base::FIELD_ID, 0, $fieldSelector, '', $collapsible, true, 150, 'left');

        print "
                <tr class='$collapsible'>
                    <td align='left' colspan='2' title='Alter games for a team based on date range, time range and field. Games will be locked.'>
                        <input style='background-color: orange' name='" . View_Base::SUBMIT . "' type='submit' value='" . View_Base::ALTER . "'>
                        <input type='hidden' id='" . View_Base::SCHEDULE_ID . "' name='" . View_Base::SCHEDULE_ID . "' value='$scheduleId'>
                        <input type='hidden' id='sessionId' name='sessionId' value='$sessionId'>
                    </td>
                </tr>
            </form>
            </table>
            </td></tr>
            </table>";
    }

    /**
     * Print form to add a game
     *
     * @param Schedule      $schedule
     * @param GameTime[]    $gameTimes
     * @param int           $sessionId
     * @param int           $javaScriptClassIdentifier - Used for expand/collapse
     */
    private function _printAddGame($schedule, $gameTimes, $sessionId, $javaScriptClassIdentifier = 7)
    {
        $expandContract = "expandContract$javaScriptClassIdentifier";
        $collapsible    = "collapsible$javaScriptClassIdentifier";
        $scheduleId     = $schedule->id;

        // Create team selector
        $teams = Team::lookupByDivision($schedule->division);
        $teamSelector = [];
        foreach ($teams as $team) {
            $coach                      = Coach::lookupByTeam($team);
            $teamSelector[$team->id]    = $team->name . ": " . $coach->shortName;
        }

        // Create gameTime selector
        $gameTimeSelector = [];
        foreach ($gameTimes as $gameTime) {
            if (!isset($gameTime->game)) {
                $day        = $gameTime->gameDate->day;
                $field      = $gameTime->field;
                $facility   = $field->facility;
                $gameTimeSelector[$gameTime->id] = $day . " " . $gameTime->startTime . " " . $facility->name . " " . $field->name;
            }
        }
        asort($gameTimeSelector);

        // Print controls and button to support moving a teams games during a date range to between a selected time
        // range and field.
        print "
            <table valign='top' align='left' border='1' cellpadding='5' cellspacing='0'><tr><td>
            <table valign='top' align='left' border='0' cellpadding='5' cellspacing='0'>
            <form method='post' action='" . self::SCHEDULE_SCHEDULES_PAGE . $this->m_urlParams . "'>
            <tr class='$expandContract'>
                <td colspan='2' style='color: " . View_Base::AQUA . "; font-size: medium' title='Add a game'><strong>Add Game</strong></td>
            </tr>";

        asort($teamSelector);
        $this->displaySelector('Home Team:', View_Base::HOME_TEAM_ID, '', $teamSelector, '', $collapsible, true, 150, 'left', 'Home Team');
        $this->displaySelector('Visiting Team:', View_Base::VISITING_TEAM_ID, '', $teamSelector, '', $collapsible, true, 150, 'left', 'Visiting Team');
        $this->displaySelector('Date/Time:', View_Base::GAME_TIME, '', $gameTimeSelector, '', $collapsible);

        print "
                <tr class='$collapsible'>
                    <td align='left' colspan='2' title='Add Game'>
                        <input style='background-color: lightgreen' name='" . View_Base::SUBMIT . "' type='submit' value='" . View_Base::ADD . "'>
                        <input type='hidden' id='" . View_Base::SCHEDULE_ID . "' name='" . View_Base::SCHEDULE_ID . "' value='$scheduleId'>
                        <input type='hidden' id='sessionId' name='sessionId' value='$sessionId'>
                    </td>
                </tr>
            </form>
            </table>
            </td></tr>
            </table>";
    }

    /**
     * @brief Print the schedule of games.
     *
     * @param Schedule  $schedule
     * @param Pool[]    $pools
     * @param array     $defaultGameTimes
     * @param array     $gamesByDateByFieldByTime
     */
    private function _printGames($schedule, $pools, $defaultGameTimes, $gamesByDateByFieldByTime)
    {
        // Print the game schedule
        foreach ($pools as $pool) {
            // Print table header
            print "
            <table valign='top' align='center' border='1' cellpadding='5' cellspacing='0'>";

            $colspan    = 2 + count($defaultGameTimes);
            $headerRow  = $schedule->division->name . ' ' . $schedule->division->gender . ', Flight: ' . $pool->flight->name . ', ' . $pool->name;
            if ($pool->gamesAgainstPool->id != $pool->id) {
                $name = $pool->gamesAgainstPool->name;
                $headerRow .= "<font color='red'> (Cross-pool play with $name)</font>";
            }

            print "
                <thead>
                <tr>
                    <th colspan='$colspan' align='center'>$headerRow</th>
                </tr>";

            // Finish dipsplay if no games scheduled for this pool's flight
            if ($pool->flight->scheduleGames != 1) {
                print "
                <tr>
                    <th style='color: red'>No games scheduled for this pool</th>
                </tr>
             </table>";
                continue;
            }

            print "
                <tr>
                    <th>Day</th>
                    <th>Field</th>";

            foreach ($defaultGameTimes as $gameTime) {
                $readableGameTime       = ltrim(substr($gameTime, 0, 5), "0");
                $gameTimes[$gameTime]   = $readableGameTime;

                print "
                    <th nowrap>$readableGameTime</th>";
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
                        $game       = null;
                        if (isset($gameTimeData[$defaultGameTime])) {
                            $game = $gameTimeData[$defaultGameTime];

                            if (isset($game) and $game->pool->id == $pool->id) {
                                $homeTeamCoach      = Coach::lookupByTeam($game->homeTeam);
                                $visitingTeamCoach  = Coach::lookupByTeam($game->visitingTeam);
                                $gender             = $game->homeTeam->division->gender;
                                $bgHTML             = $gender == 'Boys' ? "bgcolor='lightblue'" : "bgcolor='lightyellow'";
                                $gameData           = "Game Id: " . $game->id . "<br>";
                                $gameData           .= $game->homeTeam->nameId . " vs " . $game->visitingTeam->nameId;
                                $locked             = $game->isLocked() ? "\nLOCKED" : "";
                                $title              = "title='" . $homeTeamCoach->name . " vs " . $visitingTeamCoach->name . "$locked'";

                                // Change background color to red if game was just moved.
                                if ((isset($this->m_controller->m_moveGameId) and $game->id == $this->m_controller->m_moveGameId)
                                    or (isset($this->m_controller->m_primaryGameId) and $game->id == $this->m_controller->m_primaryGameId)
                                    or (isset($this->m_controller->m_secondaryGameId) and $game->id == $this->m_controller->m_secondaryGameId)) {
                                    $bgHTML = "bgcolor='red'";
                                }

                                // Change background color to orange if game is locked
                                if ($game->isLocked()) {
                                    $bgHTML = "bgcolor='orange'";
                                }

                                print "
                                <td nowrap $bgHTML $title>$gameData</td>";
                                $entryFound = true;
                            } else if (isset($game) and $game->title != '' and $game->flight->id == $pool->flight->id) {
                                $homeTeamCoach      = isset($game->homeTeam) ? Coach::lookupByTeam($game->homeTeam) : null;
                                $visitingTeamCoach  = isset($game->visitingTeam) ? Coach::lookupByTeam($game->visitingTeam) : null;
                                $homeCoachName      = isset($homeTeamCoach) ? $homeTeamCoach->name : 'TBD';
                                $visitingCoachName  = isset($visitingTeamCoach) ? $visitingTeamCoach->name : 'TBD';
                                $gender             = $pool->schedule->division->gender;
                                $bgHTML             = $gender == 'Boys' ? "bgcolor='lightblue'" : "bgcolor='lightyellow'";
                                $gameData           = "Game Id: " . $game->id . "<br>";
                                $gameData           .= $game->title;
                                $locked             = $game->isLocked() ? "\nLOCKED" : "";
                                $title              = "title='" . $homeCoachName . " vs " . $visitingCoachName . "$locked'";

                                // Change background color to red if game was just moved.
                                if ((isset($this->m_controller->m_moveGameId) and $game->id == $this->m_controller->m_moveGameId)
                                    or (isset($this->m_controller->m_primaryGameId) and $game->id == $this->m_controller->m_primaryGameId)
                                    or (isset($this->m_controller->m_secondaryGameId) and $game->id == $this->m_controller->m_secondaryGameId)) {
                                    $bgHTML = "bgcolor='red'";
                                }

                                // Change background color to orange if game is locked
                                if ($game->isLocked()) {
                                    $bgHTML = "bgcolor='orange'";
                                }

                                print "
                                <td nowrap $bgHTML $title>$gameData</td>";
                                $entryFound = true;
                            }
                        }

                        if (!$entryFound) {
                            if (!isset($game)) {
                                print "
                                    <td nowrap>&nbsp</td>";
                            } else {
                                $gameData   = "Game Id: $game->id";
                                if ($game->title != '') {
                                    $gameData .= "<br>$game->title";
                                }

                                if ($game->isLocked()) {
                                    $title      = $game->flight->schedule->division->name . "-" . $game->flight->schedule->division->gender . "\nLOCKED";
                                    print "
                                    <td nowrap bgcolor='orange' title='$title'>$gameData</td>";
                                } else {
                                    $title = $game->flight->schedule->division->name . "-" . $game->flight->schedule->division->gender;
                                    print "
                                    <td nowrap bgcolor='salmon' title='$title'>$gameData</td>";
                                }
                            }
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