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
        print "
            <table valign='top' align='center' border='1' cellpadding='5' cellspacing='0'>
                <thead>
                    <tr bgcolor='lightskyblue'>
                        <th>Division</th>
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
                    <tr>
                        <td>$division->name $division->gender</td>
                        <td align='right'>" . count($teams) . "</td>
                    </tr>";
        }

        print "
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