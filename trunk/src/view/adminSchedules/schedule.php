<?php

use \DAG\Domain\Schedule\Schedule;
use \DAG\Domain\Schedule\Pool;
use \DAG\Domain\Schedule\Team;
use \DAG\Domain\Schedule\GameTime;
use \DAG\Domain\Schedule\DivisionField;
use \DAG\Domain\Schedule\Coach;
use \DAG\Orm\Schedule\ScheduleOrm;
use \DAG\Domain\Schedule\Flight;
use \DAG\Orm\Schedule\GameOrm;
use \DAG\Domain\Schedule\Division;

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
            print "
            <div id='redips-drag'>
            <div class='boxed'>";

            foreach ($schedules as $schedule) {
                // If not VIEW then only show schedules just created
                if (($this->m_controller->m_operation == View_Base::CREATE
                    or $this->m_controller->m_operation == View_Base::UPDATE)
                    and $schedule->name == $this->m_controller->m_name) {
                    $this->_printUpdateScheduleForm($schedule, $gameDateSelector, $sessionId, $dataPrinted);
                    $dataPrinted = true;
                }

                // If View then only show published schedules if checked
                if (($this->m_controller->m_operation == View_Base::VIEW)
                    and ($this->m_controller->m_showPublishedSchedules or !$schedule->published)) {
                    $this->_printUpdateScheduleForm($schedule, $gameDateSelector, $sessionId, $dataPrinted);
                    $dataPrinted = true;
                }

                // View schedule if it was just updated
                if (($this->m_controller->m_operation == View_Base::UPDATE_POOL
                    or $this->m_controller->m_operation == View_Base::POPULATE
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
                    or $this->m_controller->m_operation == View_Base::ADD
                    or $this->m_controller->m_operation == View_Base::DELETE_GAME)
                    and $this->m_controller->m_scheduleId == $schedule->id) {

                    $this->_printUpdateScheduleForm($schedule, $gameDateSelector, $sessionId, $dataPrinted);
                    $dataPrinted = true;
                }
            }
            print "
            </div>
            </div>";

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
     * @param Schedule  $schedule           - Schedule to be edited
     * @param array     $gameDateSelector   - List of gameDateId => day
     * @param int       $sessionId          - Session Identifier
     * @param bool      $draggableDisplayed - True if displayed, false otherwise
     */
    private function _printUpdateScheduleForm($schedule, $gameDateSelector, $sessionId, $draggableDisplayed = false) {
\DAG\Framework\Utils\TimeUtils::time_elapsed("Prime");
        $flights        = Flight::lookupBySchedule($schedule);
        $pools          = Pool::lookupBySchedule($schedule);
\DAG\Framework\Utils\TimeUtils::time_elapsed("Pools Lookup");
        $gamesByPoolId  = [];
        foreach ($pools as $pool) {
            $gamesByPoolId[$pool->id]   = 0;
        }
\DAG\Framework\Utils\TimeUtils::time_elapsed("Game lookup");

        $divisionFields = DivisionField::lookupByDivision($schedule->division);
        $startTime          = $this->m_controller->m_season->startTime;
        $endTime            = $this->m_controller->m_season->endTime;
        $interval           = new \DateInterval("PT" . $schedule->division->gameDurationMinutes . "M");
\DAG\Framework\Utils\TimeUtils::time_elapsed("DivisionField lookup");
        $gameTimes      = [];
        $fieldIds       = [];
        foreach ($divisionFields as $divisionField) {
            $fieldIds[$divisionField->field->id]    = $divisionField->field->facility->name . ": " . $divisionField->field->name;
            $gameTimes                              = array_merge($gameTimes, GameTime::lookupByField($divisionField->field));
        }
\DAG\Framework\Utils\TimeUtils::time_elapsed("GameTime lookup");

        $gameTimesSelector  = [];
        $defaultGameTimes   = GameTime::getDefaultGameTimes($startTime, $endTime, $interval);
        foreach ($defaultGameTimes as $gameTime) {
            $readableGameTime               = ltrim(substr($gameTime, 0, 5), "0");
            $gameTimesSelector[$gameTime]   = $readableGameTime;
        }
\DAG\Framework\Utils\TimeUtils::time_elapsed("GameTimeSelector lookup");

        // Populate team selector
        $teamSelector   = [];
        $teams          = Team::lookupByDivision($schedule->division);
        foreach ($teams as $team) {
            $coachName                  = ': ' . Coach::lookupByTeam($team)->shortName;
            $teamSelector[$team->id]    = $team->nameId . $coachName;
        }
\DAG\Framework\Utils\TimeUtils::time_elapsed("TeamSelector lookup");

        // Order gameTimes by day, field and startTime
        $gameTimesByDateByFieldByTime   = [];
        $fieldSelector                  = [];
        $fieldSelector[0]               = 'Any';
        $gamesExist                     = false;
        $gameIds                        = [];
        foreach ($gameTimes as $gameTime) {
            if ($gameTime->gameDate->day >= $schedule->startDate
                and $gameTime->gameDate->day <= $schedule->endDate) {
                $day                                                        = $gameTime->gameDate->day;
                $startTime                                                  = $gameTime->startTime;
                $fieldName                                                  = $gameTime->field->facility->name . ": " . $gameTime->field->name;
                $fieldSelector[$gameTime->field->id]                        = $fieldName;
                $gameTimesByDateByFieldByTime[$day][$fieldName][$startTime] = $gameTime;
                if (isset($gameTime->game)) {
                    if ($gameTime->game->flight->schedule->id == $schedule->id) {
                        $gamesExist = true;
                    }
                    $gameIds[]  = $gameTime->game->id;
                }
            }
        }
        ksort($gameTimesByDateByFieldByTime);
\DAG\Framework\Utils\TimeUtils::time_elapsed("GameTimesByDateByFieldByTime population/sort");

        $redipsDarkCell = "class='redips-mark  dark'";
        $divisionScheduleName = $schedule->division->nameWithGender . ": " . $schedule->name;
        print "
            <div id='message' style='vertical-align: middle; font-size: large; color: yellow'><strong>$divisionScheduleName</strong></div>
            <div id='s' data-sessionid='$sessionId'/>";

        print $this->_printScheduleButtons($schedule, $gamesExist, $sessionId, $redipsDarkCell);

        print "
            <div class='accordion' style='width: 1700px'>
                <h2 style='text-decoration: underline; text-align: center'><b>Schedule Tools</b></h2>
                <div class='pane'>";

        print "
            <table id='viewTable' align='center'>
            <tr><td>";

        $this->_printUpdateScheduleAttributesForm($schedule, $gameDateSelector, $sessionId, $redipsDarkCell);

        print "
                    </td>
                    <td>";

        $this->_printCreateFlightForm($schedule->id, $flights, $sessionId, $redipsDarkCell);

        print "
                                        </td>
                                        <td>";

        $this->_printCreatePoolForm($schedule->id, $flights, $sessionId, $redipsDarkCell);

        print "
                                        </td>
                                        <td>";

        $this->_printDeletePoolForm($schedule->id, $pools, $sessionId, $redipsDarkCell);

        print "
                                        </td>
                                        <td>";

        $this->_printAlterTeamGames($schedule->id, $teamSelector, $fieldSelector, $gameDateSelector, $sessionId, $redipsDarkCell);

        print "
                                        </td>
                                        <td>";

        $this->_printAddGame($schedule, $flights, $gameTimes, $sessionId, $redipsDarkCell);

        print "
                                        </td>
                                        <td>";

        $this->_printDeleteGameForm($schedule->id, $gameIds, $sessionId, $redipsDarkCell);

        print "
                </td></tr>
                </table>";
        print "
                </div>
            </div><br>";

        $this->_printGames($schedule, $pools, $defaultGameTimes, $gameTimesByDateByFieldByTime, $sessionId, $redipsDarkCell, $draggableDisplayed);

        print "
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
     * @brief Print Medal Round Game Options
     *
     * @param int       $flightId       - Flight identifier
     * @param int       $fifthSixth     - 0 or 1
     * @param int       $semiFinals     - 0 or 1
     * @param int       $thirdFourth    - 0 or 1
     * @param int       $championship   - 0 or 1
     * @param string    $label          - defaults to empty (caller displays the label)
     * @param int       $itemsPerRow    - defaults to 1
     */
    protected function printMedalRoundSelectorNoTable(
        $flightId,
        $fifthSixth,
        $semiFinals,
        $thirdFourth,
        $championship,
        $label = '',
        $itemsPerRow = 1)
    {
        $medalRoundData = array(
            View_Base::INCLUDE_5TH_6TH_GAME         => (isset($fifthSixth) and $fifthSixth == 1) ? 'checked' : '',
            View_Base::INCLUDE_SEMI_FINAL_GAMES     => (isset($semiFinals) and $semiFinals == 1) ? 'checked' : '',
            View_Base::INCLUDE_3RD_4TH_GAME         => (isset($thirdFourth) and $thirdFourth == 1) ? 'checked' : '',
            View_Base::INCLUDE_CHAMPIONSHIP_GAME    => (isset($championship) and $championship == 1) ? 'checked' : '',
        );

        if (!empty($label)) {
            print "
                    <strong style='font-color: " . View_Base::AQUA . "'>$label:&nbsp</strong>";
        }

        // Print medal round data
        $currentItemsPrinted = 0;

        foreach ($medalRoundData as $gameType => $checked) {
            $name = View_Base::FLIGHT_UPDATE_DATA . "[$flightId][$gameType]";
            if ($currentItemsPrinted == $itemsPerRow) {
                print "
                <br>&nbsp&nbsp&nbsp&nbsp";

                $currentItemsPrinted = 0;
            }

            print "
                    <nobr><input type=checkbox name='$name' id='$gameType' value='1' $checked>$gameType</nobr>";

            $currentItemsPrinted += 1;
        }
    }

    /**
     * @brief Print the form to update schedule attributes.  Form includes the following
     *        - Schedule Name
     *        - Number of Games
     *        - First Day, Last Day
     *        - First game of day
     *        - Last game of day
     *        - Game days
     *
     * @param Schedule  $schedule           - Schedule to be edited
     * @param array     $gameDateSelector   - List of gameDateId => day
     * @param int       $sessionId          - Session Identifier
     * @param string    $redipsDarkCell     - Mark cells as non-draggable
     */
    private function _printUpdateScheduleAttributesForm($schedule, $gameDateSelector, $sessionId, $redipsDarkCell)
    {
        $startTime      = $schedule->startTime;
        $endTime        = $schedule->endTime;

        // Print the start of the form to select a facility
        print "
            <table id='viewTable'>
                <tr>
                    <td $redipsDarkCell colspan='2' style='color: " . View_Base::AQUA . "; font-size: medium'><strong>Update Schedule</strong></td>
                </tr>
            <form method='post' action='" . self::SCHEDULE_SCHEDULES_PAGE . $this->m_urlParams . "'>
                <tr>
                    <td $redipsDarkCell colspan='3' nowrap align='right'><strong>Schedule Name:</strong></td>";

        $this->displayInput('', 'text', View_Base::NAME, 'Schedule Name', '', $schedule->name, null, 1, false, 120);

        print "
                </tr>
                <tr>
                    <td $redipsDarkCell colspan='3' nowrap align='right'><strong>Display Notes:</strong></td>";

        $this->displayInput('', 'text', View_Base::DISPLAY_NOTES, 'Display Notes', '', $schedule->displayNotes, null, 1, false, 240, false, false);

        print "
                </tr>
                <tr>
                    <td $redipsDarkCell colspan='3' nowrap align='right'><strong>Games Per Team:</strong></td>";
        $this->displayInput('', 'int', View_Base::GAMES_PER_TEAM, 'Games Per Team', '', $schedule->gamesPerTeam, null, 1, false, 75);
        print "
                </tr>";

        print "
                <tr>
                    <td $redipsDarkCell colspan='3' nowrap align='right'><strong>First Day of Schedule:</strong></td>";
        $this->displaySelector('', View_Base::START_DATE, '', $gameDateSelector, $schedule->startDate, null, false, 100);
        print "
                </tr>";

        print "
                <tr>
                    <td $redipsDarkCell colspan='3' nowrap align='right'><strong>Last Day of Schedule:</strong></td>";
        $this->displaySelector('', View_Base::END_DATE, '', $gameDateSelector, $schedule->endDate, null, false, 100);
        print "
                </tr>";

        print "
                <tr>
                    <td $redipsDarkCell colspan='3' nowrap align='right'><strong>First Game of Day:</strong></td>";
        $this->printTimeSelector(View_Base::START_TIME, "", $startTime);
        print "
                </tr>";

        print "
                <tr>
                    <td $redipsDarkCell colspan='3' nowrap align='right'><strong>Last Game of Day:</strong></td>";
        $this->printTimeSelector(View_Base::END_TIME, "", $endTime);
        print "
                </tr>";

        print "
                <tr>
                    <td $redipsDarkCell colspan='3' nowrap align='right'><strong>Game Days:</strong></td>";
        $this->printDaySelector(4,  null, $schedule->daysOfWeek, "", true, false, 1, 3);
        print "
                </tr>";

        // Print Submit button and end form
        print "
                <tr>
                    <td $redipsDarkCell align='left' colspan='1'>
                        <input title='Update this schedules name, games and teams in pools' style='background-color: lightgreen' name='" . View_Base::SUBMIT . "' type='submit' value='" . View_Base::UPDATE . "'>
                        <input type='hidden' id='scheduleId' name='scheduleId' value='$schedule->id'>
                        <input type='hidden' id='sessionId' name='sessionId' value='$sessionId'>
                    </td>
                </tr>
            </form>
            </table>";
    }

    /**
     * @brief Print the form to update a pool's teams.
     *
     * @param Schedule  $schedule           - Schedule to be edited
     * @param Pool[]    $pools              - Pools in schedule
     * @param Pool      $pool               - Pool being updated
     * @param int       $sessionId          - Session Identifier
     */
    private function _printUpdatePoolForm($schedule, $pools, $pool, $sessionId)
    {
        print "
            <br>
            <form method='post' action='" . self::SCHEDULE_SCHEDULES_PAGE . $this->m_urlParams . "'>";

        $poolSelector = [];
        foreach ($pools as $poolInSelector) {
            $poolSelector[$poolInSelector->id] = $poolInSelector->fullName;
        }

        $flight = $pool->flight;
        $name   = View_Base::FLIGHT_UPDATE_DATA . "[$flight->id][" . View_Base::NAME . "]";
        print "<strong style='color: " . View_Base::AQUA . "'>&nbspFlight:</strong>
               &nbsp<input style='width: 75px' type='text' name='$name' placeholder='Flight Name' value='$flight->name'>
               <br>&nbsp<strong style='color: " . View_Base::AQUA . "'>&nbspPool:</strong>&nbsp&nbsp&nbsp$pool->name
               <br>";

        $name       = View_Base::FLIGHT_UPDATE_DATA . "[$flight->id][" . View_Base::FLIGHT_SCHEDULE_GAMES . "]";
        $checked    = $flight->scheduleGames == 1 ? "checked" : "";
        print "
            &nbsp&nbsp&nbsp&nbsp<input type='checkbox' name='$name' value='checked' $checked> Schedule Games<br><br>";

        if ($pool->schedule->scheduleType == ScheduleOrm::SCHEDULE_TYPE_TOURNAMENT) {
            print "
                <strong style='color: " . View_Base::AQUA . "'>&nbspInclude Medal Round Games:</strong><br>&nbsp&nbsp&nbsp&nbsp";

            $this->printMedalRoundSelectorNoTable(
                $flight->id,
                $flight->include5th6thGame,
                $flight->includeSemiFinalGames,
                $flight->include3rd4thGame,
                $flight->includeChampionshipGame,
                "",
                2);

            print "<br><br>";
        }

        $selectedPoolName   = isset($pool->gamesAgainstPool) ? $pool->gamesAgainstPool->fullName : $pool->fullName;
        $selectorColor      = isset($pool->gamesAgainstPool) ? 'salmon' : 'lightgreen';
        $name               = View_Base::CROSS_POOL_UPDATE_DATA . "[$pool->id]";
        print "<strong style='color: " . View_Base::AQUA . "'>&nbspPlay Games Against:</strong>
               <br>
               &nbsp&nbsp&nbsp&nbsp";
        $this->displaySelectorNoTable('', $name, '', $poolSelector, $selectedPoolName, 120,'', $selectorColor);

        print "<br><br><strong style='alignment: left; color: " . self::AQUA . "'>&nbspTeamId, Pool, Seed:</strong><br>";

        $teams = Team::lookupByPool($pool);
        $seedSelector = [];
        $i = 1;
        $seedSelector[0] = 0;
        foreach ($teams as $team) {
            $seedSelector[$i] = $i;
            $i += 1;
        }
        foreach ($teams as $team) {
            print "
                &nbsp&nbsp&nbsp&nbsp
                $team->nameId ";

            print "&nbsp";
            $name = View_Base::TEAM_POOL_UPDATE_DATA . "[$team->id][" . View_Base::POOL_ID . "]";
            $this->displaySelectorNoTable('', $name, '', $poolSelector, $team->pool->fullName, 120);

            $name = View_Base::TEAM_POOL_UPDATE_DATA . "[$team->id][" . View_Base::SEED . "]";
            $this->displaySelectorNoTable('', $name, '', $seedSelector, $team->seed, 35);

            print "
            <br>";
        }

        // Print Update button and end form
        print "
                <br>&nbsp
                <input title='Update this schedules name, games and teams in pools' style='background-color: lightgreen' name='" . View_Base::SUBMIT . "' type='submit' value='" . View_Base::UPDATE_POOL . "'>
                <input type='hidden' id='scheduleId' name='scheduleId' value='$schedule->id'>
                <input type='hidden' id='sessionId' name='sessionId' value='$sessionId'>
            </form>";

    }

    /**
     * @brief Print schedule operation buttons
     *        - Populate
     *        - Clear
     *        - Publish/Unpublish
     *        - Delete
     *
     * @param Schedule  $schedule   - Schedule to be edited
     * @param bool      $gamesExist - true if games exist for schedule
     * @param int       $sessionId  - Session Identifier
     * @param string    $redipsDarkCell - tag cell as non-movable
     */
    private function _printScheduleButtons($schedule, $gamesExist, $sessionId, $redipsDarkCell)
    {
        $publishButton = "
                    <td colspan='1' $redipsDarkCell>
                        <input title='Publish schedule for everyone to see' style='border-color: black; background-color: lightgreen; font-size: medium' name='" . View_Base::SUBMIT . "' type='submit' value='" . View_Base::PUBLISH . "'>
                        <input type='hidden' id='scheduleId' name='scheduleId' value='$schedule->id'>
                        <input type='hidden' id='sessionId' name='sessionId' value='$sessionId'>
                    </td>";

        $unPublishButton = "
                    <td colspan='1' $redipsDarkCell>
                        <input title='Un-Publish schedule so you can modify' style='border-color: black; background-color: salmon; font-size: medium' name='" . View_Base::SUBMIT . "' type='submit' value='" . View_Base::UN_PUBLISH . "'>
                        <input type='hidden' id='scheduleId' name='scheduleId' value='$schedule->id'>
                        <input type='hidden' id='sessionId' name='sessionId' value='$sessionId'>
                    </td>";

        $clearButton = "
                    <td colspan='1' $redipsDarkCell>
                        <input title='Un-publish, Delete all games, but keep schedule name, number of games and pools' style='border-color: black; background-color: salmon; font-size: medium' name='" . View_Base::SUBMIT . "' type='submit' value='" . View_Base::CLEAR . "'>
                        <input type='hidden' id='scheduleId' name='scheduleId' value='$schedule->id'>
                        <input type='hidden' id='sessionId' name='sessionId' value='$sessionId'>
                    </td>";

        $deleteButton = "
                    <td colspan='1' $redipsDarkCell>
                        <input title='Delete this schedule, including pools and games' style='border-color: black; background-color: salmon; font-size: medium' name='" . View_Base::SUBMIT . "' type='submit' value='" . View_Base::DELETE . "'>
                        <input type='hidden' id='scheduleId' name='scheduleId' value='$schedule->id'>
                        <input type='hidden' id='sessionId' name='sessionId' value='$sessionId'>
                    </td>";

        $populateButton = "
                    <td colspan='1' $redipsDarkCell>
                        <input title='Populate games for teams in pools' style='border-color: black; background-color: lightgreen; font-size: medium' name='" . View_Base::SUBMIT . "' type='submit' value='" . View_Base::POPULATE . "'>
                        <input type='hidden' id='scheduleId' name='scheduleId' value='$schedule->id'>
                        <input type='hidden' id='sessionId' name='sessionId' value='$sessionId'>
                    </td>";

        $firstGameOfDay         = substr($schedule->startTime, 0, 5);
        $lastGameOfDay          = substr($schedule->endTime, 0, 5);
        $colspan                = ($schedule->published == 1) ? 1 : 3;
        $startSpan              = "<strong style='font-size: medium; color: " . View_Base::AQUA . "'>";
        $endSpan                = "</strong>";
        $daysOfWeek             = $schedule->daysOfWeek;
        $gameDays               = "";
        $gameDays               .= (isset($daysOfWeek[0]) and $daysOfWeek[0] == 1) ? 'Mon, ' : '';
        $gameDays               .= (isset($daysOfWeek[0]) and $daysOfWeek[1] == 1) ? 'Tues, ' : '';
        $gameDays               .= (isset($daysOfWeek[0]) and $daysOfWeek[2] == 1) ? 'Wed, ' : '';
        $gameDays               .= (isset($daysOfWeek[0]) and $daysOfWeek[3] == 1) ? 'Thur, ' : '';
        $gameDays               .= (isset($daysOfWeek[0]) and $daysOfWeek[4] == 1) ? 'Fri, ' : '';
        $gameDays               .= (isset($daysOfWeek[0]) and $daysOfWeek[5] == 1) ? 'Sat, ' : '';
        $gameDays               .= (isset($daysOfWeek[0]) and $daysOfWeek[6] == 1) ? 'Sun, ' : '';
        $gameDays               = substr($gameDays, 0, strlen($gameDays) - 2);
        print "
            <form method='post' action='" . self::SCHEDULE_SCHEDULES_PAGE . $this->m_urlParams . "'>
            <table id='buttonTable' valign='top' align='center' border='1' cellpadding='5' cellspacing='0'>
                <tr>
                    <td colspan='$colspan' $redipsDarkCell>
                        ${startSpan}Schedule: ${endSpan}$schedule->name&nbsp&nbsp&nbsp&nbsp
                        ${startSpan}Start: ${endSpan}$schedule->startDate&nbsp&nbsp&nbsp&nbsp
                        ${startSpan}End: ${endSpan}$schedule->endDate&nbsp&nbsp&nbsp&nbsp
                        ${startSpan}First Game Time: ${endSpan}$firstGameOfDay&nbsp&nbsp&nbsp&nbsp
                        ${startSpan}Last Game Time: ${endSpan}$lastGameOfDay&nbsp&nbsp&nbsp&nbsp
                        ${startSpan}Games Per Team: ${endSpan}$schedule->gamesPerTeam&nbsp&nbsp&nbsp&nbsp
                        ${startSpan}Game Days: ${endSpan}$gameDays
                    </td>
                </tr>
                <tr>";

        if ($schedule->published == 1) {
            print $unPublishButton;
        } else {
            if ($gamesExist) {
                print $clearButton;
            } else {
                print $populateButton;
            }
            print $publishButton;
            print $deleteButton;
        }

        print "
                </tr>
            </table>
            </form>";
    }

    /**
     * Print form to create a flight
     *
     * @param int       $scheduleId
     * @param Flight[]  $flights
     * @param int       $sessionId
     * @param string    $redipsDarkCell - tag cell as non-movable
     */
    private function _printCreateFlightForm($scheduleId, $flights, $sessionId, $redipsDarkCell)
    {
        $flightSelector   = [];
        foreach ($flights as $flight) {
            $flightSelector[$flight->id] = $flight->name;
        }

        print "
            <table id='viewTable'>
                <tr>
                    <td $redipsDarkCell colspan='2' style='color: " . View_Base::AQUA . "; font-size: medium'><strong>Create Flight</strong></td>
                </tr>";

        print "
            <form method='post' action='" . self::SCHEDULE_SCHEDULES_PAGE . $this->m_urlParams . "'>
                <tr>";

        $this->displayInput('', 'text', View_Base::FLIGHT_NAME, 'Flight Name', '', '', null, 1, false, 75, false, true);

        print "
                    <td $redipsDarkCell align='left' colspan='1' title='Create a new flight'>
                        <input style='background-color: lightgreen' name='" . View_Base::SUBMIT . "' type='submit' value='" . View_Base::CREATE_FLIGHT . "'>
                        <input type='hidden' id='" . View_Base::SCHEDULE_ID . "' name='" . View_Base::SCHEDULE_ID . "' value='$scheduleId'>
                        <input type='hidden' id='sessionId' name='sessionId' value='$sessionId'>
                    </td>
                </tr>
            </form>
            </table>";
    }

    /**
     * Print form to create a pool
     *
     * @param int       $scheduleId
     * @param Flight[]  $flights
     * @param int       $sessionId
     * @param string    $redipsDarkCell - Mar cell as non-draggable
     */
    private function _printCreatePoolForm($scheduleId, $flights, $sessionId, $redipsDarkCell)
    {
        $flightSelector = [];

        foreach ($flights as $flight) {
            $flightSelector[$flight->id] = $flight->name;
        }

        print "
            <table id='viewTable'>
            <form method='post' action='" . self::SCHEDULE_SCHEDULES_PAGE . $this->m_urlParams . "'>
                <tr>
                    <td $redipsDarkCell colspan='2' style='color: " . View_Base::AQUA . "; font-size: medium'><strong>Create Pool</strong></td>
                </tr>
                <tr>";

        $this->displaySelector('', View_Base::FLIGHT_ID, '', $flightSelector, '', null, false, 120, 'right', 'Select Flight');

        print "
                </tr>
                <tr>";

        $this->displayInput('', 'text', View_Base::POOL_NAME, 'Pool Name', '', '', null, 2, true, 75, false, true);

        print "
                </tr>
                <tr>
                    <td $redipsDarkCell align='left' colspan='1' title='Create a new pool'>
                        <input style='background-color: lightgreen' name='" . View_Base::SUBMIT . "' type='submit' value='" . View_Base::CREATE_POOL . "'>
                        <input type='hidden' id='" . View_Base::SCHEDULE_ID . "' name='" . View_Base::SCHEDULE_ID . "' value='$scheduleId'>
                        <input type='hidden' id='sessionId' name='sessionId' value='$sessionId'>
                    </td>
                </tr>
            </form>
            </table>";
    }

    /**
     * Print form to delete a pool
     *
     * @param int       $scheduleId
     * @param Pool[]    $pools
     * @param int       $sessionId
     * @param string    $redipsDarkCell - Mark cell as non-draggable
     */
    private function _printDeletePoolForm($scheduleId, $pools, $sessionId, $redipsDarkCell)
    {
        $poolSelector   = [];

        foreach ($pools as $pool) {
            $poolSelector[$pool->id] = $pool->fullName;
        }

        print "
            <form method='post' action='" . self::SCHEDULE_SCHEDULES_PAGE . $this->m_urlParams . "'>
            <table id='viewTable'>
                <tr>
                    <td $redipsDarkCell colspan='2' style='color: " . View_Base::AQUA . "; font-size: medium'><strong>Delete Pool</strong></td>
                </tr>
                <tr>";

        $this->displaySelector('', View_Base::POOL_ID, '', $poolSelector, '', null, false, 120, 'right', 'Select Pool');

        print "
                </tr>
                <tr>
                    <td $redipsDarkCell align='left' colspan='1' title='Delete pool'>
                        <input style='background-color: salmon' name='" . View_Base::SUBMIT . "' type='submit' value='" . View_Base::DELETE_POOL . "'>
                        <input type='hidden' id='" . View_Base::SCHEDULE_ID . "' name='" . View_Base::SCHEDULE_ID . "' value='$scheduleId'>
                        <input type='hidden' id='sessionId' name='sessionId' value='$sessionId'>
                    </td>
                </tr>
            </table>
            </form>";
    }

    /**
     * Print form to modify game times for a team
     *
     * @param int       $scheduleId
     * @param array     $teamSelector
     * @param array     $fieldSelector
     * @param array     $gameDateSelector - List of gameDateId => day
     * @param int       $sessionId
     * @param string    $redipsDarkCell - Mark cell as non-draggable
     */
    private function _printAlterTeamGames($scheduleId, $teamSelector, $fieldSelector, $gameDateSelector, $sessionId, $redipsDarkCell)
    {
        // Print controls and button to support moving a teams games during a date range to between a selected time
        // range and field.
        print "
            <table id='viewTable'>
            <form method='post' action='" . self::SCHEDULE_SCHEDULES_PAGE . $this->m_urlParams . "'>
            <tr>
                <td $redipsDarkCell colspan='2' style='color: " . View_Base::AQUA . "; font-size: medium' title='Alter games for a team based on date range, time range and field. Games will be locked.'><strong>Alter Team Game(s)</strong></td>
            </tr>";

        asort($teamSelector);
        $this->displaySelector('Team:', View_Base::TEAM_ID, '', $teamSelector, '', null, true, 150, 'left', 'Select Team');
        $this->displaySelector('From Date', View_Base::START_DATE, '', $gameDateSelector, '', null);
        $this->displaySelector('To Date', View_Base::END_DATE, '', $gameDateSelector, end($gameDateSelector), null);
        $this->printTimeSelector(View_Base::START_TIME, "From Time", "07:00:00", 1, null);
        $this->printTimeSelector(View_Base::END_TIME, "To Time", "19:00:00", 1, null);
        $this->displaySelector('Field:', View_Base::FIELD_ID, 0, $fieldSelector, '', null, true, 150, 'left');

        print "
                <tr>
                    <td $redipsDarkCell align='left' colspan='2' title='Alter games for a team based on date range, time range and field. Games will be locked.'>
                        <input style='background-color: orange' name='" . View_Base::SUBMIT . "' type='submit' value='" . View_Base::ALTER . "'>
                        <input type='hidden' id='" . View_Base::SCHEDULE_ID . "' name='" . View_Base::SCHEDULE_ID . "' value='$scheduleId'>
                        <input type='hidden' id='sessionId' name='sessionId' value='$sessionId'>
                    </td>
                </tr>
            </form>
            </table>";
    }

    /**
     * Print form to add a game
     *
     * @param Schedule      $schedule
     * @param Flight[]      $flights
     * @param GameTime[]    $gameTimes
     * @param int           $sessionId
     * @param string        $redipsDarkCell - Mark cells as no-draggable
     */
    private function _printAddGame($schedule, $flights, $gameTimes, $sessionId, $redipsDarkCell)
    {
        $scheduleId     = $schedule->id;
        $flightSelector = [];

        foreach ($flights as $flight) {
            $flightSelector[$flight->id] = $flight->name;
        }

        // Create team selector
        $teams = Team::lookupByDivision($schedule->division);
        $teamSelector       = [];
        $teamSelector[0]    = 'Set Later';
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
            <table id='viewTable'>
            <form method='post' action='" . self::SCHEDULE_SCHEDULES_PAGE . $this->m_urlParams . "'>
            <tr>
                <td $redipsDarkCell colspan='2' style='color: " . View_Base::AQUA . "; font-size: medium' title='Add a game'><strong>Add Game</strong></td>
            </tr>";

        asort($teamSelector);
        $this->displaySelector('Flight:', View_Base::FLIGHT_ID, '', $flightSelector, '', null, true, 150, 'left', 'Flight');
        $this->displaySelector('Home Team:', View_Base::HOME_TEAM_ID, '', $teamSelector, '', null, true, 150, 'left', 'Home Team');
        $this->displaySelector('Visiting Team:', View_Base::VISITING_TEAM_ID, '', $teamSelector, '', null, true, 150, 'left', 'Visiting Team');
        $this->displaySelector('Date/Time:', View_Base::GAME_TIME, '', $gameTimeSelector, '', null);
        $this->displayInput('Actual Start Time', 'text', View_Base::ACTUAL_START_TIME, '', '', null, null);

        $gameTitlesSelector[GameOrm::TITLE_NONE]            = GameOrm::TITLE_NONE;
        $gameTitlesSelector[GameOrm::TITLE_PLAYOFF]         = GameOrm::TITLE_PLAYOFF;
        $gameTitlesSelector[GameOrm::TITLE_QUARTER_FINAL]   = GameOrm::TITLE_QUARTER_FINAL;
        $gameTitlesSelector[GameOrm::TITLE_5TH_6TH]         = GameOrm::TITLE_5TH_6TH;
        $gameTitlesSelector[GameOrm::TITLE_3RD_4TH]         = GameOrm::TITLE_3RD_4TH;
        $gameTitlesSelector[GameOrm::TITLE_SEMI_FINAL]      = GameOrm::TITLE_SEMI_FINAL;
        $gameTitlesSelector[GameOrm::TITLE_CHAMPIONSHIP]    = GameOrm::TITLE_CHAMPIONSHIP;
        $this->displaySelector('Game Title:', View_Base::GAME_TITLE, '', $gameTitlesSelector, GameOrm::TITLE_NONE, null, true, 150, 'left');

        print "
                <tr>
                    <td $redipsDarkCell align='left' colspan='2' title='Add Game'>
                        <input style='background-color: lightgreen' name='" . View_Base::SUBMIT . "' type='submit' value='" . View_Base::ADD . "'>
                        <input type='hidden' id='" . View_Base::SCHEDULE_ID . "' name='" . View_Base::SCHEDULE_ID . "' value='$scheduleId'>
                        <input type='hidden' id='sessionId' name='sessionId' value='$sessionId'>
                    </td>
                </tr>
            </form>
            </table>";
    }

    /**
     * Print form to delete a game
     *
     * @param int       $scheduleId
     * @param array     $gameIds
     * @param int       $sessionId
     * @param string    $redipsDarkCell - Mark cells as non-draggable
     */
    private function _printDeleteGameForm($scheduleId, $gameIds, $sessionId, $redipsDarkCell)
    {
        // Print Move controls and button
        print "
            <table id='viewTable'>
            <form method='post' action='" . self::SCHEDULE_SCHEDULES_PAGE . $this->m_urlParams . "'>
            <tr>
                <td $redipsDarkCell colspan='2' style='color: " . View_Base::AQUA . "; font-size: medium' title='Delete game'><strong>Delete Game</strong></td>
            </tr>";

        asort($gameIds);
        $this->displaySelector('Game Id:', View_Base::GAME_ID, '', $gameIds, '', null, true, 150, 'left', 'Game Id');

        print "
                <tr>
                    <td $redipsDarkCell align='left' colspan='2' title='Delete game'>
                        <input style='background-color: salmon' name='" . View_Base::SUBMIT . "' type='submit' value='" . View_Base::DELETE_GAME . "'>
                        <input type='hidden' id='" . View_Base::SCHEDULE_ID . "' name='" . View_Base::SCHEDULE_ID . "' value='$scheduleId'>
                        <input type='hidden' id='sessionId' name='sessionId' value='$sessionId'>
                    </td>
                </tr>
            </form>
            </table>";
    }

    /**
     * @brief Print the schedule of games.
     *
     * @param Schedule  $schedule
     * @param Pool[]    $pools
     * @param array     $defaultGameTimes
     * @param array     $gameTimesByDateByFieldByTime
     * @param int       $sessionId
     * @param string    $redipsDarkCell - Mark selected cells as non-draggable
     * @param bool      $draggableDisplayed - True if displayed, false otherwise
     */
    private function _printGames($schedule, $pools, $defaultGameTimes, $gameTimesByDateByFieldByTime, $sessionId, $redipsDarkCell, $draggableDisplayed = false)
    {
        // Compute max Team rows
        $teamRowspan = 0;
        foreach ($gameTimesByDateByFieldByTime as $day => $gameTimeFieldData) {
            $teamRowspan += count($gameTimeFieldData);
        }

        // Print the game schedule
        foreach ($pools as $pool) {
            // Print table header
            print "
            <table id='dragTable' valign='top' align='center' border='1' cellpadding='5' cellspacing='0'>";

            $colspan    = 3 + count($defaultGameTimes);
            $headerRow  = $schedule->division->name . ' ' . $schedule->division->gender . ', Flight: ' . $pool->flight->name . ', ' . $pool->name;
            if ($pool->gamesAgainstPool->id != $pool->id) {
                $name = $pool->gamesAgainstPool->name;
                $headerRow .= "<font color='red'> (Cross-pool play with $name)</font>";
            }

            print "
                <thead>
                <tr>
                    <th colspan='$colspan' align='center' $redipsDarkCell>$headerRow</th>
                </tr>";

            print "
                <tr>
                    <th $redipsDarkCell>Teams</th>
                    <th $redipsDarkCell>Day</th>
                    <th $redipsDarkCell>Field</th>";

            foreach ($defaultGameTimes as $defaultGameTime) {
                $readableGameTime = ltrim(substr($defaultGameTime, 0, 5), "0");
                print "
                    <th width='150' nowrap $redipsDarkCell>$readableGameTime</th>";
            }

            print "
                </tr>
                </thead>";

            // Print pools
            print "
                        <tr>
                            <td valign='top' nowrap rowspan='$teamRowspan' $redipsDarkCell>";
            $this->_printUpdatePoolForm($schedule, $pools, $pool, $sessionId);
            print "
                            </td>";
            $justPrintedPools = true;

            // Print table rows
            foreach ($gameTimesByDateByFieldByTime as $day => $gameTimeFieldData) {
                ksort($gameTimeFieldData);
                $rowspan    = count($gameTimeFieldData);
                $loopIndex  = 0;
                foreach ($gameTimeFieldData as $fieldName => $gameTimeData) {
                    ksort($gameTimeData);

                    if ($loopIndex == 0) {
                        if (!$justPrintedPools) {
                            print "
                        <tr>";
                        }
                        print "
                            <td nowrap rowspan='$rowspan' $redipsDarkCell>$day</td>";
                    } else {
                        print "
                        <tr>";
                    }
                    $justPrintedPools = false;
                    $loopIndex += 1;

                    print "
                    <td nowrap $redipsDarkCell>$fieldName</td>";

                    foreach ($defaultGameTimes as $defaultGameTime) {
                        $game                   = null;
                        $gameTime               = null;
                        $cellId                 = '';
                        $actualStartTimeHTML    = '';
                        $lockFormHTML           = '<br>';
                        $title                  = '';
                        $bgColor                = 'white';
                        $redipsDragStart        = '';
                        $redipsDragEnd          = '';
                        $gameData               = '';


                        if (isset($gameTimeData[$defaultGameTime])) {
                            $gameTime               = $gameTimeData[$defaultGameTime];
                            $game                   = $gameTime->game;
                            $cellId                 = "id='$gameTime->id'";
                            if ($gameTime->startTime != $gameTime->actualStartTime) {
                                $actualStartTimeHTML = "<strong style='color: purple'>";
                                $actualStartTimeHTML .= substr($gameTime->actualStartTime, 0, 5);
                                $actualStartTimeHTML .= "</strong>";
                            }
                        }

                        $lockButtonHTML = "
                                    <button id='button$gameTime->id' type=\"button\"
                                        style='border: none; cursor: pointer; background-color: transparent; display: none'
                                        onclick='redips.toggleGameLock($gameTime->id)' data-bgcolor='white' data-gameid='0' data-sessionid='$sessionId'>
                                        <img id='lockButton$gameTime->id' src=\"/images/unlock.jpeg\" width='15px' height='15px'>
                                     </button>";

                        if (isset($game)) {
                            $values = View_AdminSchedules_Base::getDisplayLabels($game, true);
                            $homeCoachName = $values[View_Base::COACH_NAME];
                            $homeTeamId = $values[View_Base::TEAM_ID];

                            $values = View_AdminSchedules_Base::getDisplayLabels($game, false);
                            $visitingCoachName = $values[View_Base::COACH_NAME];
                            $visitingTeamId = $values[View_Base::TEAM_ID];

                            $gameData = isset(GameOrm::$abbreviatedTitles[$game->title]) ? GameOrm::$abbreviatedTitles[$game->title] . "&nbsp" : '';
                            $gameData .= "Id: " . $game->id;
                            $gameData .= "<br>";
                            $gameData .= $homeTeamId . " vs <br>" . $visitingTeamId;
                            $locked = $game->isLocked() ? "\nLOCKED" : "";
                            $title = $homeCoachName . " vs " . $visitingCoachName . "$locked";

                            // Change background color to
                            //     - lightgreen for games with this schedule
                            //     - lightblue for movable boys game
                            //     - lightyellow for movable girls game
                            //     - orange if game is locked (and disable drag)
                            //     - salmon if game is published (and disable drag)
                            if (($game->pool->id == $pool->id)
                                or ($game->title != '' and $game->flight->id == $pool->flight->id)) {
                                $bgColor = "lightgreen";
                            } else {
                                $bgColor = $game->flight->schedule->division->gender == Division::$BOYS ?
                                    "lightblue" :
                                    "lightyellow";
                            }

                            if ($game->flight->schedule->published != 1 and !$draggableDisplayed) {
                                $buttonImage = $game->isLocked() ? "/images/lock.jpeg" : "/images/unlock.jpeg";
                                $lockButtonHTML = "
                                    <button id='button$gameTime->id' type=\"button\"
                                        style='border: none; cursor: pointer; background-color: transparent'
                                        onclick='redips.toggleGameLock($gameTime->id)' data-bgcolor='$bgColor' data-gameid='$game->id' data-sessionid='$sessionId'>
                                        <img id='lockButton$gameTime->id' src=\"$buttonImage\" width='15px' height='15px'>
                                     </button>";

                                if ($game->isLocked()) {
                                    $bgColor = "orange";
                                }

                                $redipsDragStart    = "<div id='div$game->id' class='redips-drag' style='background-color: $bgColor'>";
                                $redipsDragEnd      = "</div>";
                            }
                        } else {
                            if (!$draggableDisplayed) {
                                $buttonImage = $gameTime->isLocked() ? "/images/lock.jpeg" : "/images/unlock.jpeg";
                                $lockButtonHTML = "
                                    <button id='button$gameTime->id' type=\"button\"
                                        style='border: none; cursor: pointer; background-color: transparent'
                                        onclick='redips.toggleGameTimeLock($gameTime->id)' data-bgcolor='$bgColor' data-gametimeid='$gameTime->id' data-sessionid='$sessionId'>
                                        <img id='lockButton$gameTime->id' src=\"$buttonImage\" width='15px' height='15px'>
                                     </button>";
                            }

                            if ($gameTime->isLocked()) {
                                $bgColor = "red";
                            }
                        }

                        $style = empty($redipsDragStart) ? "style='background-color: $bgColor'" : "";
                        print "
                                <td $cellId align='center' nowrap $style title='$title'>
                                    $actualStartTimeHTML
                                    $lockButtonHTML
                                    <br>
                                    ${redipsDragStart}$gameData${redipsDragEnd}
                                </td>";
                    }

                    print "
                </tr>";
                }
            }
            print "
            </table>
            <br>";

            $draggableDisplayed = true;
        }
    }
}