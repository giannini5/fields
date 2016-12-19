<?php

use \DAG\Domain\Schedule\Family;
use \DAG\Domain\Schedule\Coach;
use \DAG\Domain\Schedule\AssistantCoach;
use \DAG\Domain\Schedule\Team;


/**
 * @brief Show the Player page and get the user to select a player to administer or create a new player.
 */
class View_Schedules_Family extends View_Schedules_Base {
    /**
     * @brief Construct the View
     *
     * @param $controller - Controller that contains data used when rendering this view.
     */
    public function __construct($controller) {
        parent::__construct(self::SCHEDULE_FAMILY_PAGE, $controller);
    }

    /**
     * @brief Render data for display on the page.
     */
    public function render()
    {
        print "
            <br><br>
            <table valign='top' align='center' border='1' cellpadding='5' cellspacing='0'>
                <thead>
                    <tr bgcolor='lightskyblue'>
                        <th>Family</th>
                        <th>Team</th>
                        <th>Coach</th>
                        <th>Assistant Coach</th>
                    </tr>
                </thead>";

        $families = [];
        if (isset($this->m_controller->m_season)) {
            $families = Family::lookupBySeason($this->m_controller->m_season);
        }

        foreach ($families as $family) {
            $coaches = Coach::lookupByFamily($family);
            $assistantCoaches = AssistantCoach::lookupByFamily($family);
            $teams = [];

            foreach ($coaches as $coach) {
                $team = Team::lookupById($coach->team->id);
                if (!isset($teams[$team->name])) {
                    $teams[$team->name] = $team;
                }
            }
            foreach ($assistantCoaches as $assistantCoach) {
                $team = Team::lookupById($assistantCoach->team->id);
                if (!isset($teams[$team->name])) {
                    $teams[$team->name] = $team;
                }
            }

            print "
                <tr>
                    <td>
                        <strong>$family->id</strong><br>
                        &nbsp$family->phone1<br>
                        &nbsp$family->phone2
                    </td>";

            ksort($teams);
            print "
                    <td>";
            foreach ($teams as $teamName => $team) {
                print "<strong>$team->id</strong><br>&nbsp$teamName<br>";
            }
            if (count($teams) == 0) {
                print "&nbsp";
            }
            print "
                    </td>";

            print "
                    <td>";
            foreach ($coaches as $coach) {
                $phoneNumbers = $coach->phone1 . ", " . $coach->phone2;
                print "<strong>" . $coach->team->id . ": $coach->name</strong><br>&nbsp$phoneNumbers<br>";
            }
            if (count($coaches) == 0) {
                print "&nbsp";
            }
            print "
                    </td>";

            print "
                    <td>";
            foreach ($assistantCoaches as $assistantCoach) {
                $phoneNumbers = $assistantCoach->phone1 . ", " . $assistantCoach->phone2;
                print "<strong>" . $assistantCoach->team->id . ": $assistantCoach->name</strong><br>&nbsp$phoneNumbers<br>";
            }
            if (count($assistantCoaches) == 0) {
                print "&nbsp";
            }
            print "
                    </td>";

            print "
                </tr>";
        }

        print "
            </table>
            ";
    }
}