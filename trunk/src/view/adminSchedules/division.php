<?php

use \DAG\Domain\Schedule\Team;
use \DAG\Domain\Schedule\Division;

/**
 * @brief Show the Division page and get the user to select a division to administer or create a new division.
 */
class View_AdminSchedules_Division extends View_AdminSchedules_Base {
    /**
     * @brief Construct the View
     *
     * @param Controller_Base $controller - Controller that contains data used when rendering this view.
     */
    public function __construct($controller) {
        parent::__construct(self::SCHEDULE_DIVISIONS_PAGE, $controller);
    }

    /**
     * @brief Render data for display on the page.
     */
    public function render()
    {
        $sessionId = $this->m_controller->getSessionId();
        $messageString  = $this->m_controller->m_messageString;

        if (!empty($messageString)) {
            print "
                <p style='color: green' align='center'><strong>$messageString</strong></p><br>";
        }

        $this->printDivisionSelectors();


        print "
            <table valign='top' align='center' border='1' cellpadding='5' cellspacing='0'>
            <form method='post' action='" . self::SCHEDULE_DIVISIONS_PAGE . $this->m_urlParams . "'>
                <thead>
                    <tr bgcolor='lightskyblue'>
                        <th>Division</th>
                        <th>Gender</th>
                        <th>DisplayOrder</th>
                        <th>Field Use Minutes</th>
                        <th title='Minutes from start of first game to start of second game for '>MinutesBetweenGames</th>
                        <th>Scoring Tracked</th>
                        <th title='For odd scheduling situations where teams play within pool then cross-pool you can set this value to 1 to give a singe view in standings'>Combine League Schedules</th>
                        <th>Teams</th>
                    </tr>
                </thead>";

        $divisions = [];
        if (isset($this->m_controller->m_season)) {
            $divisions = Division::lookupBySeason($this->m_controller->m_season);
        }

        foreach ($divisions as $division) {
            $teams = Team::lookupByDivision($division);
            print "
                    <tr>";

            $name = View_Base::DIVISION_UPDATE_DATA . "[$division->id][" . View_Base::NAME . "]";
            $this->displayInput('', 'string', $name, '', '', $division->name, null, 1, false, 90, false, true, 'right');

            print "
                    <td>$division->gender</td>";

            $name = View_Base::DIVISION_UPDATE_DATA . "[$division->id][" . View_Base::DISPLAY_ORDER . "]";
            $this->displayInput('', 'string', $name, '', '', $division->displayOrder, null, 1, false, 25, false, true, 'right');

            $name = View_Base::DIVISION_UPDATE_DATA . "[$division->id][" . View_Base::GAME_DURATION_MINUTES . "]";
            $this->displayInput('', 'string', $name, '', '', $division->gameDurationMinutes, null, 1, false, 25, false, true, 'right');

            $name = View_Base::DIVISION_UPDATE_DATA . "[$division->id][" . View_Base::MINUTES_BETWEEN_GAMES . "]";
            $this->displayInput('', 'string', $name, '', '', $division->minutesBetweenGames, null, 1, false, 25, false, true, 'right');

            $name = View_Base::DIVISION_UPDATE_DATA . "[$division->id][" . View_Base::SCORING_TRACKED . "]";
            $this->displayInput('', 'string', $name, '', '', $division->scoringTracked, null, 1, false, 25, false, true, 'right');

            $name = View_Base::DIVISION_UPDATE_DATA . "[$division->id][" . View_Base::COMBINE_LEAGUE_SCHEDULES . "]";
            $this->displayInput('', 'string', $name, '', '', $division->combineLeagueSchedules, null, 1, false, 25, false, true, 'right');

            print "
                        <td align='right'>" . count($teams) . "</td>
                    </tr>";
        }

        print "
                <tr>
                    <td align='left' colspan='6'>
                        <input style='background-color: yellow' name='" . View_Base::SUBMIT . "' type='submit' value='" . View_Base::UPDATE . "'>
                        <input type='hidden' id='sessionId' name='sessionId' value='$sessionId'>
                    </td>";

        print "
            </from>
            </table>
            ";
    }

    /**
     * Print selector to create a division
     */
    private function printDivisionSelectors()
    {
        $sessionId = $this->m_controller->getSessionId();

        print "
            <table valign='top' align='center' width='300' border='1' cellpadding='5' cellspacing='0'>
                <tr><td bgcolor='" . View_Base::CREATE_COLOR . "'>
                    <table valign='top' align='center' width='300' border='0' cellpadding='5' cellspacing='0'>
                        <tr>
                            <td valign='top'>";

        $this->printCreateDivision($sessionId);

        print "
                            </td>
                        </tr>
                    </table>
                </td></tr>
            </table><br><br>";
    }

    /**
     * @brief Print the form to create a division.  Form includes the following
     *        - Division Name
     *        - Enabled radio button
     *
     * @param $sessionId - Session identifier
     */
    private function printCreateDivision($sessionId)
    {
        print "
            <form method='post' action='" . self::SCHEDULE_DIVISIONS_PAGE . $this->m_urlParams . "'>
                <tr>
                    <td colspan='2'><strong> Create Division</strong></td>
                </tr>";

        $this->displayInput("Division Name:", 'text', View_Base::NAME, 'Division Name', '');
        $this->displaySelector('Gender:', View_Base::GENDER, '', ['Boys' => 'Boys', 'Girls' => 'Girls'], '', null, true, 140, 'left', 'Select Gender');
        $this->displayInput('Display Order:', 'string', View_Base::DISPLAY_ORDER, '', '', 200);
        $this->displayInput('Game Duration Minutes', 'string', View_Base::GAME_DURATION_MINUTES, '', '', 90);

        // Print Create button and end form
        print "
                <tr>
                    <td align='left'>
                        <input style='background-color: yellow' name='" . View_Base::SUBMIT . "' type='submit' value='" . View_Base::CREATE . "'>
                        <input type='hidden' id='sessionId' name='sessionId' value='$sessionId'>
                    </td>
                </tr>
            </form>";

    }
}