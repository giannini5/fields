<?php

use \DAG\Domain\Schedule\AssistantCoach;
use \DAG\Domain\Schedule\Coach;
use \DAG\Domain\Schedule\Team;
use \DAG\Domain\Schedule\Division;

/**
 * @brief Show the Team page and get the user to select a team to administer or create a new team.
 */
class View_Schedules_Team extends View_Schedules_Base {
    /**
     * @brief Construct the View
     *
     * @param $controller - Controller that contains data used when rendering this view.
     */
    public function __construct($controller) {
        parent::__construct(self::SCHEDULE_TEAMS_PAGE, $controller);
    }


    /**
     * @brief Render data for display on the page.
     */
    public function render()
    {
        $this->renderLoadFromFile();
        print "<br><br>";
        $this->renderTeams();
    }

    /**
     * @brief Render HTML to load teams, coaches and assistant coaches from a file
     */
    public function renderLoadFromFile()
    {
        $sessionId = $this->m_controller->getSessionId();

        print "
            <table bgcolor='lightyellow' valign='top' align='center' width='400' border='1' cellpadding='5' cellspacing='0'>
                <tr>
                    <td colspan='3' nowrap><strong>Sample CSV file format</strong><br>
                        Approved,Team,Type,eAYSO Vol App,AYSO ID,Name,Phone,Cell,Email,Certifications<br>
                        New,U12G-2,Coach,,,Walid Afifi,805-679-1812,805-679-1810,w-afifi@comm.ucsb.edu,\"Needs training\"<br>
                        New,U12B-5,Coach,,,David Aguilar,805-284-2045,805-259-9680,davidoaguilar@gmail.com,\"Needs training\"<br>
                        Yes,U6B-29,Coach,,58302620,Gerardo Aldana,805-637-0256,,soccercoachga@gmail.com,\"U-6 Coach,Needs training\"</td>
                </tr>
                <tr>
                    <form enctype='multipart/form-data' method='POST' action='" . self::SCHEDULE_TEAMS_PAGE . $this->m_urlParams . "'>
                        <td nowrap>Select csv file to upload:</td>
                        <td>
                            <input type='file' name='fileToUpload' id='fileToUpload'>
                        </td>
                        <td>
                            <input style='background-color: yellow' type='" . View_Base::SUBMIT . "' value='" . View_Base::UPLOAD_FILE . "' name='" . View_Base::SUBMIT . "'>
                            <input type='hidden' id='sessionId' name='sessionId' value='$sessionId'>
                        </td>
                    </form>
                </tr>
            </table>
            ";
    }

    /**
     * @brief Render HTML to show teams, coaches and assistant coaches that have been loaded
     */
    public function renderTeams()
    {
        print "
            <table valign='top' align='center' width='400' border='1' cellpadding='5' cellspacing='0'>
                <thead>
                    <tr>
                        <th>Team</th>
                        <th>Coach</th>
                        <th>Assistant Coaches</th>
                    </tr>
                </thead>";

        $divisions = Division::lookupBySeason($this->m_controller->m_season);
        foreach ($divisions as $division) {
            $teams = Team::lookupByDivision($division);
            foreach ($teams as $team) {
                Coach::findCoachForTeam($team, $coach);
                $coachName = isset($coach) ? $coach->name : '&nbsp';
                $assistantCoaches = AssistantCoach::lookupByTeam($team);
                $assistantCoachesCount = count($assistantCoaches) == 0 ? 1 : count($assistantCoaches);

                print "
                    <tr>
                        <td rowspan='$assistantCoachesCount'>$team->name</td>
                        <td rowspan='$assistantCoachesCount'>$coachName</td>";

                if (count($assistantCoaches) == 0) {
                    print "
                        <td>&nbsp</td>
                    </tr>";
                } else {
                    $count = 0;
                    foreach ($assistantCoaches as $assistantCoach) {
                        $newRow = $count == 0 ? '' : '<tr>';

                        print "
                    $newRow
                        <td>$assistantCoach->name</td>
                    </tr>";

                        $count += 1;
                    }
                }
            }
        }

        print "
            </table>
            ";
    }

    /**
     * @brief Print the form to create a team.  Form includes the following
     *        - Team Name
     *        - Enabled radio button
     *
     * @param $maxColumns - Number of columns the form is covering
     */
    private function _printCreateTeamForm($maxColumns) {
        // TODO
    }

    /**
     * @brief Print the form to update a season.  Form includes the following
     *        - Team Name
     *        - Enabled radio button
     *
     * @param $maxColumns - Number of columns the form is covering
     * @param $season - Team to be edited
     */
    private function _printUpdateTeamForm($maxColumns, $season) {
        // TODO
    }
}