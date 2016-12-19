<?php

use \DAG\Domain\Schedule\Schedule;
use \DAG\Domain\Schedule\Division;
use \DAG\Domain\Schedule\Pool;
use \DAG\Domain\Schedule\Team;

/**
 * @brief Show the Schedule page and get the user to select a schedule to administer or create a new schedule.
 */
class View_Schedules_Schedule extends View_Schedules_Base {
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
        // $divisionsSelector  = $this->getDivisionsSelector(false, true, true);


        $messageString  = $this->m_controller->m_messageString;
        if (!empty($messageString)) {
            print "
                <p style='color: green' align='center'><strong>$messageString</strong></p><br>";
        }

        print "
            <table bgcolor='lightyellow' valign='top' align='center' width='400' border='1' cellpadding='5' cellspacing='0'>
                <tr>
                    <td>";

        $this->_printCreateScheduleForm($sessionId, $divisionsSelector);

        print "
                    </td>
                </tr>
            </table>
            <br><br>";

        $divisions = [];
        if (isset($this->m_controller->m_season)) {
            $divisions = Division::lookupBySeason($this->m_controller->m_season);
        }

        $schedules = [];
        foreach ($divisions as $division) {
            $schedules = array_merge($schedules, Schedule::lookupByDivision($division));
        }

        foreach ($schedules as $schedule) {
            print "
            <table valign='top' align='center' width='400' border='1' cellpadding='5' cellspacing='0'>
                <tr>
                    <td>";

            $this->_printUpdateScheduleForm($schedule, $sessionId, $divisionsSelector);

            print "
                    </td>
                </tr>
            </table>
            <br><br>";
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
            <form method='post' action='" . self::SCHEDULE_SCHEDULES_PAGE . $this->m_urlParams . "'>";

        $errorString = (isset($this->m_controller->m_scheduleId) or $this->m_controller->m_missingAttributes == 0) ? '' : $this->m_controller->m_name;

        $this->displayInput('Schedule Name:', 'text', View_Base::NAME, 'Schedule Name', $errorString);
        $this->displayMultiSelector('Divisions', View_Base::DIVISION_NAMES, '', $divisionsSelector, count($divisionsSelector));
        $this->displayInput('Games Per Team:', 'int', View_Base::GAMES_PER_TEAM, 'Games Per Team', $errorString);

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
     * @brief Print the form to update a schedule.  Form includes the following
     *        - Schedule Name
     *        - Division
     *
     * @param $schedule             - Schedule to be edited
     * @param $sessionId            - Session Identifier
     * @param $divisionsSelector    - List of divisionId => name
     */
    private function _printUpdateScheduleForm($schedule, $sessionId, $divisionsSelector) {
        $errorString = ($this->m_controller->m_scheduleId == $schedule->id and $this->m_controller->m_missingAttributes > 0) ? $this->m_controller->m_name : '';

        // Print the start of the form to select a facility
        print "
            <table valign='top' align='center' border='0' cellpadding='5' cellspacing='0'>
            <form method='post' action='" . self::SCHEDULE_SCHEDULES_PAGE . $this->m_urlParams . "'>
                <tr>";

        $divisionName = $schedule->division->name . " " . $schedule->division->gender;
        print "
                    <td nowrap><strong>$divisionName</strong></td>
                    <td nowrap align='right'><strong>Schedule Name:</strong></td>";

        $this->displayInput('', 'text', View_Base::NAME, 'Schedule Name', $errorString, $schedule->name, null, 1, false);

        print "
                </tr>
                    <td>&nbsp</td>
                    <td nowrap align='right'><strong>Games Per Team:</strong></td>";

        $this->displayInput('', 'int', View_Base::GAMES_PER_TEAM, 'Games Per Team', $errorString, $schedule->gamesPerTeam, null, 1, false);

        print "
                </tr>";

        $pools = Pool::lookupBySchedule($schedule);
        $poolSelector = [];
        foreach ($pools as $pool) {
            $poolSelector[$pool->id] = $pool->name;
        }

        foreach ($pools as $pool) {
            print "
                <tr>
                    <td align='left'><strong>$pool->name</strong></td>
                </tr>";

            $teams = Team::lookupByPool($pool);
            foreach ($teams as $team) {
                print "
                <tr>
                    <td>&nbsp</td>
                    <td>$team->name</td>";

                    $name = View_Base::TEAM_POOL_UPDATE_DATA . "[$team->id][" . View_Base::POOL_ID . "]";
                    $this->displaySelector('Pool:', $name, '', $poolSelector, $team->pool->name, null, false, 100);

                print "
                </tr>";
            }
        }

        print "<tr>";

        // Print Submit button and end form
        print "
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
            </table>";
    }
}