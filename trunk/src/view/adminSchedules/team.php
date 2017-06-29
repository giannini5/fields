<?php

use \DAG\Domain\Schedule\AssistantCoach;
use \DAG\Domain\Schedule\Coach;
use \DAG\Domain\Schedule\Team;
use \DAG\Domain\Schedule\Division;
use \DAG\Domain\Schedule\Player;

/**
 * @brief Show the Team page and get the user to select a team to administer or create a new team.
 */
class View_AdminSchedules_Team extends View_AdminSchedules_Base {
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
        $filterDivisionId   = $this->m_controller->m_filterDivisionId;
        $filterTeamId       = $this->m_controller->m_filterTeamId;
        $filterCoachId      = $this->m_controller->m_filterCoachId;
        $showPlayers        = $this->m_controller->m_showPlayers;

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

        if ($showPlayers) {
            print "
                        <th>Players</th>";
        }

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

                $coachInfo  = '&nbsp';
                $coach      = null;
                if (Coach::findCoachForTeam($team, $coach)) {
                    $coachInfo = "$coach->name<br>E: $coach->email<br>H: $coach->phone1<br>C: $coach->phone2";
                }

                if ($filterCoachId != 0 and (!isset($coach) or $coach->id != $filterCoachId)) {
                    continue;
                }

                $assistantCoaches       = AssistantCoach::lookupByTeam($team);
                $assistantCoachCount    = count($assistantCoaches);
                $rowspan                = 1; // $assistantCoachCount > 0 ? $assistantCoachCount : 1;
                $divisionName           = $team->division->nameWithGender;

                print "
                    <tr>
                        <td rowspan='$rowspan'>$divisionName</td>
                        <td rowspan='$rowspan'>$team->name</td>
                        <td rowspan='$rowspan'>$team->nameId</td>
                        <td rowspan='$rowspan'>$team->region</td>
                        <td rowspan='$rowspan'>$team->city</td>
                        <td rowspan='$rowspan'>$coachInfo</td>";

                if ($assistantCoachCount == 0) {
                    print "
                        <td>&nbsp</td>";
                } else {
                    print "
                        <td>";
                    foreach ($assistantCoaches as $assistantCoach) {
                        $assistantCoachInfo = "$assistantCoach->name<br>E: $assistantCoach->email<br>H: $assistantCoach->phone1<br>C: $assistantCoach->phone2";
                        print "
                        $assistantCoachInfo<br><br>";
                    }
                    print "
                        </td>";
                }

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

                print "
                    </tr>";
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
            <table bgcolor='" . View_Base::VIEW_COLOR . "' valign='top' align='center' width='625' border='1' cellpadding='5' cellspacing='0'>
                <tr><td>
                    <table valign='top' align='center' width='300' border='0' cellpadding='5' cellspacing='0'>
                        <tr>
                            <td valign='top'>";

        $this->printSelectTeam($sessionId, $filterDivisionId, $filterTeamId, $filterCoachId, $showPlayers);

        print "
                            </td>
                        </tr>
                    </table>
                    </td>
                    <td valign='top'>
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
        $this->printCheckboxSelector(View_Base::SHOW_PLAYERS, "Show Players", $showPlayers, 2);

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

        $this->printTeamSelector(null, false, "Select Team", "Team 1");
        $this->printTeamSelector(null, false, "Select Team", "Team 2");

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