<?php

use \DAG\Domain\Schedule\AssistantCoach;
use \DAG\Domain\Schedule\Coach;
use \DAG\Domain\Schedule\Team;
use \DAG\Domain\Schedule\Division;

/**
 * @brief Show the Team page and get the user to select a team to administer or create a new team.
 */
class View_AdminSchedules_Team extends View_AdminSchedules_Base {
    /**
     * @brief Construct the View
     *
     * @param Controller_Base $controller - Controller that contains data used when rendering this view.
     */
    public function __construct($controller) {
        parent::__construct(self::SCHEDULE_TEAMS_PAGE, $controller);
    }

    /**
     * @brief Render data for display on the page.
     */
    public function render()
    {
        $filterDivisionId   = $this->m_controller->m_filterDivisionId;
        $filterTeamId       = $this->m_controller->m_filterTeamId;
        $filterCoachId      = $this->m_controller->m_filterCoachId;
        $showPlayers        = $this->m_controller->m_showPlayers;
        $sessionId          = $this->m_controller->getSessionId();

        $messageString  = $this->m_controller->m_messageString;
        if (!empty($messageString)) {
            print "
                <p style='color: green' align='center'><strong>$messageString</strong></p><br>";
        }

        $this->printTeamSelectors($filterDivisionId, $filterTeamId, $filterCoachId, $showPlayers);

        print "
            <br><br>
            <table valign='top' align='center' border='1' cellpadding='5' cellspacing='0'>
                <thead>
                    <tr bgcolor='lightskyblue'>
                        <th>Division</th>
                        <th>Team</th>
                        <th>TeamId</th>
                        <th>Region</th>
                        <th>City</th>
                        <th>Coach</th>
                        <th>Assistant Coach</th>";
/*
        if ($showPlayers) {
            print "
                        <th>Players</th>";
        }
*/

        print "
                    </tr>
                </thead>";

        $divisions = [];
        if (isset($this->m_controller->m_season)) {
            $divisions = Division::lookupBySeason($this->m_controller->m_season);
        }

        foreach ($divisions as $division) {
            if ($filterDivisionId != 0 and $division->id != $filterDivisionId) {
                continue;
            }

            $teams = Team::lookupByDivision($division);

            foreach ($teams as $team) {
                if ($filterTeamId != 0 and $team->id != $filterTeamId) {
                    continue;
                }

                $coach      = null;
                Coach::findCoachForTeam($team, $coach);
                if ($filterCoachId != 0 and (!isset($coach) or $coach->id != $filterCoachId)) {
                    continue;
                }

                $assistantCoaches       = AssistantCoach::lookupByTeam($team);
                $assistantCoachCount    = count($assistantCoaches);
                $rowspan                = 4; // $assistantCoachCount > 0 ? $assistantCoachCount : 1;
                $divisionName           = $team->division->nameWithGender;

                print "
                    <form method='post' action='" . self::SCHEDULE_TEAMS_PAGE . $this->m_urlParams . "'>
                    <tr>
                        <td rowspan='$rowspan'>$divisionName</td>";

                $this->displayInput('', 'text', View_Base::NAME, 'Team Name', '', $team->name, null, 1, false, 70, false, false, 'left', $rowspan);
                $this->displayInput('', 'text', View_Base::NAME_ID, 'Team Name Id', '', $team->nameId, null, 1, false, 70, false, false, 'left', $rowspan);
                $this->displayInput('', 'text', View_Base::REGION, 'Region', '', $team->region, null, 1, false, 30, false, false, 'left', $rowspan);
                $this->displayInput('', 'text', View_Base::CITY, 'City', '', $team->city, null, 1, false, 90, false, false, 'left', $rowspan);
                $this->displayInput('', 'text', View_Base::COACH_NAME, 'Coach Name', '', $coach->name, null, 1, false, 90, false, false, 'left', 1);

                if ($assistantCoachCount == 0) {
                    print "
                        <td rowspan='$rowspan'>&nbsp</td>";
                } else {
                    print "
                        <td rowspan='$rowspan'>";
                    foreach ($assistantCoaches as $assistantCoach) {
                        $assistantCoachInfo = "$assistantCoach->name<br>E: $assistantCoach->email<br>H: $assistantCoach->phone1<br>C: $assistantCoach->phone2";
                        print "
                        $assistantCoachInfo<br><br>";
                    }
                    print "
                        </td>";
                }

                print "
                        <td rowspan='$rowspan' align='left'>
                            <input style='background-color: lightgreen' name='" . View_Base::SUBMIT . "' type='submit' value='" . View_Base::UPDATE . "'>
                            <input type='hidden' id='" . self::TEAM_ID . "' name='" . self::TEAM_ID . "' value='$team->id'>
                            <input type='hidden' id='" . self::FILTER_DIVISION_ID . "' name='" . self::FILTER_DIVISION_ID . "' value='$filterDivisionId'>
                            <input type='hidden' id='sessionId' name='sessionId' value='$sessionId'>
                        </td>
                    </tr>";

                // Print remaining coach info
                $email  = $coach->email;
                $phone1 = $coach->phone1;
                $phone2 = $coach->phone2;
                $this->displayInput('', 'text', View_Base::EMAIL_ADDRESS, 'Coach Email', '', $email, null, 1, true, 90, false);
                $this->displayInput('', 'text', View_Base::PHONE1, 'Home Phone', '', $phone1, null, 1, true, 90, false);
                $this->displayInput('', 'text', View_Base::PHONE2, 'Cell Phone', '', $phone2, null, 1, true, 90, false);
                print "
                    </form>";

/*
                if ($showPlayers) {
                    $players = Player::lookupByTeam($team);
                    print "
                        <td>";
                    foreach ($players as $player) {
                        print "
                        $player->name<br>";
                    }
                    print "
                        </td>";
                }
*/
            }
        }

        print "
            </table>
            ";
    }

    /**
     * @param int   $filterDivisionId
     * @param int   $filterTeamId
     * @param int   $filterCoachId
     * @param bool  $showPlayers
     */
    private function printTeamSelectors($filterDivisionId, $filterTeamId, $filterCoachId, $showPlayers)
    {
        $sessionId = $this->m_controller->getSessionId();

        print "
            <table valign='top' align='center' width='625' border='1' cellpadding='5' cellspacing='0'>
                <tr><td bgcolor='" . View_Base::CREATE_COLOR . "'>
                    <table valign='top' align='center' width='300' border='0' cellpadding='5' cellspacing='0'>
                        <tr>
                            <td valign='top'>";

        $this->printCreateTeam($sessionId, $filterDivisionId);

        print "
                            </td>
                        </tr>
                    </table>
                    </td>
                    <td bgcolor='" . View_Base::VIEW_COLOR . "' valign='top'>
                    <table valign='top' align='center' width='200' border='0' cellpadding='5' cellspacing='0'>
                        <tr>
                            <td>";

        $this->printSelectTeam($sessionId, $filterDivisionId, $filterTeamId, $filterCoachId, $showPlayers);

        print "
                            </td>
                        </tr>
                    </table>
                    </td>
                    <td bgcolor='" . View_Base::VIEW_COLOR . "' valign='top'>
                    <table valign='top' align='center' width='200' border='0' cellpadding='5' cellspacing='0'>
                        <tr>
                            <td>";

        $this->printSwapTeams($sessionId, $filterDivisionId, $filterTeamId, $filterCoachId, $showPlayers);

        print "
                            </td>
                        </tr>
                    </table>
                </td></tr>
            </table>";
    }

    /**
     * @param int   $sessionId
     * @param int   $filterDivisionId
     * @param int   $filterTeamId
     * @param int   $filterCoachId
     * @param bool  $showPlayers
     */
    private function printSelectTeam($sessionId, $filterDivisionId, $filterTeamId, $filterCoachId, $showPlayers)
    {
        print "
            <form method='post' action='" . self::SCHEDULE_TEAMS_PAGE . $this->m_urlParams . "'>
                <tr>
                    <td colspan='2'><strong> View Teams</strong></td>
                </tr>";

        $this->printDivisionSelector($filterDivisionId, true);
        $this->printTeamSelector($filterTeamId);
        $this->printCoachSelector($filterCoachId);
        // $this->printCheckboxSelector(View_Base::SHOW_PLAYERS, "Show Players", $showPlayers, 2);

        // Print Filter button and end form
        print "
                <tr>
                    <td align='left'>
                        <input style='background-color: yellow' name='" . View_Base::SUBMIT . "' type='submit' value='" . View_Base::FILTER . "'>
                        <input type='hidden' id='sessionId' name='sessionId' value='$sessionId'>
                    </td>
                </tr>
            </form>";
    }

    /**
     * @param int   $sessionId
     * @param int   $filterDivisionId
     */
    private function printCreateTeam($sessionId, $filterDivisionId)
    {
        print "
            <form method='post' action='" . self::SCHEDULE_TEAMS_PAGE . $this->m_urlParams . "'>
                <tr>
                    <td colspan='2'><strong> Create Team</strong></td>
                </tr>";

        $this->printDivisionSelector($filterDivisionId, true);
        $this->displayInput("Team Name:", 'text', View_Base::NAME, 'Team Name', '');
        $this->displayInput("Team Name Id:", 'text', View_Base::NAME_ID, 'Team NameId', '');
        $this->displayInput("Region:", 'text', View_Base::REGION, 'Region', '', '122');
        $this->displayInput("City:", 'text', View_Base::CITY, 'City', '', 'Santa Barbara');
        $this->displayInput("Coach Name:", 'text', View_Base::COACH_NAME, 'Name', '');
        $this->displayInput("Coach Home Email:", 'text', View_Base::EMAIL_ADDRESS, 'Email', '');
        $this->displayInput("Coach Home Phone:", 'text', View_Base::PHONE1, 'Home Phone', '');
        $this->displayInput("Coach Cell Phone:", 'text', View_Base::PHONE2, 'Cell Phone', '');

        // Print Create button and end form
        print "
                <tr>
                    <td align='left'>
                        <input style='background-color: yellow' name='" . View_Base::CREATE . "' type='submit' value='" . View_Base::CREATE . "'>
                        <input type='hidden' id='sessionId' name='sessionId' value='$sessionId'>
                    </td>
                </tr>
            </form>";
    }

    /**
     * @param int   $sessionId
     * @param int   $filterDivisionId
     * @param int   $filterTeamId
     * @param int   $filterCoachId
     * @param bool  $showPlayers
     */
    private function printSwapTeams($sessionId, $filterDivisionId, $filterTeamId, $filterCoachId, $showPlayers)
    {
        print "
            <form method='post' action='" . self::SCHEDULE_TEAMS_PAGE . $this->m_urlParams . "'>
                <tr>
                    <td colspan='2' title='Swap two teams such that team 1 now plays team 2s game schedule and vice versa'><strong>Swap Teams</strong></td>
                </tr>";

        $this->printTeamSelector(null, false, "Select Team", "Team 1", View_Base::SWAP_TEAM_ID1);
        $this->printTeamSelector(null, false, "Select Team", "Team 2", View_Base::SWAP_TEAM_ID2);

        // Print Filter button and end form
        print "
                <tr>
                    <td align='left' colspan='2'>
                        <input style='background-color: yellow' name='" . View_Base::SUBMIT . "' type='submit' value='" . View_Base::SWAP . "'>
                        <input type='hidden' id='sessionId' name='sessionId' value='$sessionId'>
                    </td>
                </tr>
            </form>";
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