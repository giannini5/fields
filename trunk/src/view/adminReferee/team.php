<?php

use \DAG\Domain\Schedule\Division;
use \DAG\Domain\Schedule\Team;
use \DAG\Domain\Schedule\Coach;
use \DAG\Domain\Schedule\TeamReferee;
use \DAG\Domain\Schedule\RefereeCrew;

/**
 * @brief Show Team referees
 */
class View_AdminReferee_Team extends View_AdminReferee_Base {
    /** @var  View_AdminReferee_Team */
    public $m_controller;
    /**
     * @brief Construct the View
     *
     * @param Controller_AdminReferee_Team $controller - Controller that contains data used when rendering this view.
     */
    public function __construct($controller) {
        $this->m_controller = $controller;
        parent::__construct(self::REFEREE_TEAM_PAGE, $controller);
    }

    /**
     * @brief Render data for display on the page.
     */
    public function render()
    {
        $filterDivisionId   = $this->m_controller->m_filterDivisionId;
        $sessionId          = $this->m_controller->getSessionId();

        $messageString  = $this->m_controller->m_messageString;
        if (!empty($messageString)) {
            print "
                <p style='color: green' align='center'><strong>$messageString</strong></p><br>";
        }

        $division = $filterDivisionId != 0 ? Division::lookupById($filterDivisionId) : null;
        $this->printSelectors(isset($division) ? $division->nameWithGender : 0);

        if (isset($division)) {
            $division = Division::lookupById($filterDivisionId);

            print "
            <br><br>";

            $this->printTeamReferees($division);
        }
    }

    /**
     * @param int   $filterDivisionId
     */
    private function printSelectors($filterDivisionId)
    {
        $sessionId = $this->m_controller->getSessionId();

        print "
            <table valign='top' align='center' width='350' border='1' cellpadding='5' cellspacing='0'>
                <tr><td bgcolor='" . View_Base::VIEW_COLOR . "'>
                    <table valign='top' align='center' width='350' border='0' cellpadding='5' cellspacing='0'>
                    <form method='post' action='" . self::REFEREE_TEAM_PAGE . $this->m_urlParams . "'>
                        <tr>
                            <td valign='top'>";

        $this->printDivisionSelectorByName($filterDivisionId, true, true, false, false);

        print "
                            </td>
                            <td align='left'>
                                <input style='background-color: yellow' name='" . View_Base::SUBMIT . "' type='submit' value='" . View_Base::SUBMIT . "'>
                                <input type='hidden' id='sessionId' name='sessionId' value='$sessionId'>
                            </td>
                        </tr>
                    </form>
                    </table>
                    </td>
                </tr>
            </table>";
    }

    /**
     * @param Division  $division
     */
    private function printTeamReferees($division)
    {
        $teams = Team::lookupByDivision($division);

        print "
            <table valign='top' align='center' border='1' cellpadding='5' cellspacing='0'>
                <thead>
                    <tr bgcolor='lightskyblue'>
                        <th rowspan='2'>Team Id</th>
                        <th rowspan='2'>Team Name</th>
                        <th rowspan='2'>Coach</th>
                        <th rowspan='2' title='Divisions that referee crew (composed of team referees) is qualified to ref'>Crew</th>
                        <th colspan='4'>Referees</th>
                    </tr>
                    <tr bgcolor='lightskyblue'>
                        <th>Name</th>
                        <th>Badge</th>
                        <th>email</th>
                        <th>phone</th>
                    </tr>
                </thead>";

        $bgColor = 'lightyellow';
        foreach ($teams as $team) {
            $coach          = Coach::lookupByTeam($team);
            $teamReferees   = TeamReferee::lookupByTeam($team);
            $refereeCrews  = RefereeCrew::lookupByTeam($team);
            $rowspan        = count($teamReferees) > 0 ? count($teamReferees) : 1;
            $bgColor        = $bgColor == 'lightyellow' ? 'white' : 'lightyellow';
            print "
                    <tr bgcolor='$bgColor'>
                        <td rowspan='$rowspan'>$team->nameId</td>
                        <td rowspan='$rowspan'>$team->name</td>
                        <td rowspan='$rowspan'>$coach->shortName</td>";

            print "
                        <td rowspan='$rowspan'>";
            $anyPrinted = false;
            foreach ($refereeCrews as $refereeCrew) {
                $divisionName = $refereeCrew->division->nameWithGender;
                if ($anyPrinted) {
                    print "<br>";
                }
                print "$divisionName";
                $anyPrinted = true;
            }
            if (count($refereeCrews) == 0) {
                print "&nbsp";
            }
            print "
                        </td>";

            $anyPrinted = false;
            foreach ($teamReferees as $teamReferee) {
                if ($anyPrinted) {
                    print "
                    <tr bgcolor='$bgColor'>";
                }

                $referee    = $teamReferee->referee;
                $anyPrinted = true;
                print "
                        <td>$referee->name</td>
                        <td>$referee->badge</td>
                        <td>$referee->email</td>
                        <td>$referee->phone</td>
                    </tr>";
            }

            if (!$anyPrinted) {
                print "
                        <td>&nbsp</td>
                        <td>&nbsp</td>
                        <td>&nbsp</td>
                        <td>&nbsp</td>
                    </tr>";
            }
        }

        print "
            </table>";
    }
}