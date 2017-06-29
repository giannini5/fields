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
     * @param $controller - Controller that contains data used when rendering this view.
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

        print "
            <table valign='top' align='center' border='1' cellpadding='5' cellspacing='0'>
            <form method='post' action='" . self::SCHEDULE_DIVISIONS_PAGE . $this->m_urlParams . "'>
                <thead>
                    <tr bgcolor='lightskyblue'>
                        <th>Division</th>
                        <th>Gender</th>
                        <th>DisplayOrder</th>
                        <th>Field Use Minutes</th>
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
            $this->displayInput('', 'string', $name, '', '', $division->name, null, 1, false, 75, false, true, 'right');

            print "
                    <td>$division->gender</td>";

            $name = View_Base::DIVISION_UPDATE_DATA . "[$division->id][" . View_Base::DISPLAY_ORDER . "]";
            $this->displayInput('', 'string', $name, '', '', $division->displayOrder, null, 1, false, 75, false, true, 'right');

            $name = View_Base::DIVISION_UPDATE_DATA . "[$division->id][" . View_Base::GAME_DURATION_MINUTES . "]";
            $this->displayInput('', 'string', $name, '', '', $division->gameDurationMinutes, null, 1, false, 75, false, true, 'right');

            print "
                        <td align='right'>" . count($teams) . "</td>
                    </tr>";
        }

        print "
                <tr>
                    <td align='left' colspan='5'>
                        <input style='background-color: yellow' name='" . View_Base::SUBMIT . "' type='submit' value='" . View_Base::UPDATE . "'>
                        <input type='hidden' id='sessionId' name='sessionId' value='$sessionId'>
                    </td>";

        print "
            </from>
            </table>
            ";
    }

    /**
     * @brief Print the form to create a division.  Form includes the following
     *        - Division Name
     *        - Enabled radio button
     *
     * @param $maxColumns - Number of columns the form is covering
     */
    private function _printCreateDivisionForm($maxColumns) {
        // TODO
    }

    /**
     * @brief Print the form to update a season.  Form includes the following
     *        - Division Name
     *        - Enabled radio button
     *
     * @param $maxColumns - Number of columns the form is covering
     * @param $season - Division to be edited
     */
    private function _printUpdateDivisionForm($maxColumns, $season) {
        // TODO
    }
}