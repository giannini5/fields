<?php

use \DAG\Domain\Schedule\AssistantCoach;
use \DAG\Domain\Schedule\Coach;
use \DAG\Domain\Schedule\Team;
use \DAG\Domain\Schedule\Division;
use \DAG\Domain\Schedule\Player;

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
                        <th>Team</th>
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

                $assistantCoaches = AssistantCoach::lookupByTeam($team);
                $assistantCoachCount = count($assistantCoaches);
                $rowspan = 1; // $assistantCoachCount > 0 ? $assistantCoachCount : 1;

                print "
                    <tr>
                        <td rowspan='$rowspan'>$team->name</td>
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
     * @param $filterDivisionId
     * @param $filterTeamId
     * @param $filterCoachId
     * @param $showPlayers
     */
    private function printTeamSelectors($filterDivisionId, $filterTeamId, $filterCoachId, $showPlayers)
    {
        $sessionId = $this->m_controller->getSessionId();

        print "
            <table valign='top' align='center' width='625' border='1' cellpadding='5' cellspacing='0'>
                <tr><td>
                    <table valign='top' align='center' width='625' border='0' cellpadding='5' cellspacing='0'>
                        <form method='post' action='" . self::SCHEDULE_TEAMS_PAGE . $this->m_urlParams . "'>";

        $this->printDivisionSelector($filterDivisionId);
        $this->printTeamSelector($filterTeamId);
        $this->printCoachSelector($filterCoachId);
        $this->printCheckboxSelector(View_Base::SHOW_PLAYERS, "Show Players", $showPlayers);

        // Print Filter button and end form
        print "
                        <tr>
                            <td align='left'>
                                <input style='background-color: yellow' name='" . View_Base::SUBMIT . "' type='submit' value='" . View_Base::FILTER . "'>
                                <input type='hidden' id='sessionId' name='sessionId' value='$sessionId'>
                            </td>
                        </tr>
                        </form>
                    </table>
                </td></tr>
            </table>";
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