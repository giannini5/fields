<?php

use \DAG\Domain\Schedule\Referee;
use \DAG\Domain\Schedule\Division;
use \DAG\Domain\Schedule\DivisionReferee;
use \DAG\Domain\Schedule\Family;

/**
 * @brief Show the Referee page and get the user to select a referee to administer or create a new referee.
 */
class View_AdminReferee_Referee extends View_AdminReferee_Base {
    /**
     * @brief Construct the View
     *
     * @param Controller_AdminReferee_Referee $controller - Controller that contains data used when rendering this view.
     */
    public function __construct($controller) {
        parent::__construct(self::REFEREE_REFEREES_PAGE, $controller);
    }

    /**
     * @brief Render data for display on the page.
     */
    public function render()
    {
        $filterDivisionId   = $this->m_controller->m_filterDivisionId;
        $filterRefereeId    = $this->m_controller->m_filterRefereeId;
        $sessionId          = $this->m_controller->getSessionId();

        $messageString  = $this->m_controller->m_messageString;
        if (!empty($messageString)) {
            print "
                <p style='color: green' align='center'><strong>$messageString</strong></p><br>";
        }

        $this->printRefereeSelectors($filterDivisionId, $filterRefereeId);

        if ($filterDivisionId != 0) {
            $division   = Division::lookupById($filterDivisionId);
            $referees   = DivisionReferee::lookupByDivision($division);
            usort($referees, "compareDivisionDisplayOrder");
        } else {
            $referees = Referee::lookupBySeason($this->m_controller->m_season);
            usort($referees, "compareName");
        }

        print "
            <br><br>
            <table valign='top' align='center' border='1' cellpadding='5' cellspacing='0'>
                <thead>
                    <tr bgcolor='lightskyblue'>
                        <th>Referee</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Badge</th>
                        <th>maxGamesPerDay</th>
                        <th>Notes</th>
                        <th>Family</th>
                        <th>Update</th>
                    </tr>
                </thead>";

        foreach ($referees as $referee) {
            if ($filterRefereeId != 0 and $referee->id != $filterRefereeId) {
                continue;
            }

            $rowspan = 1;

            print "
                <form method='post' action='" . self::REFEREE_REFEREES_PAGE . $this->m_urlParams . "'>
                <tr>";

            $this->displayInput('', 'text', View_Base::NAME, 'Name', '', $referee->name, null, 1, false, 120, false, false, 'left', $rowspan);
            $this->displayInput('', 'text', View_Base::EMAIL_ADDRESS, 'Email Address', '', $referee->email, null, 1, false, 180, false, false, 'left', $rowspan);
            $this->displayInput('', 'text', View_Base::PHONE1, 'Phone', '', $referee->phone, null, 1, false, 90, false, false, 'left', $rowspan);
            $this->printRefereeBadgeSelector($referee->badgeId, false);
            $this->displayInput('', 'numeric', View_Base::MAX_GAMES_PER_DAY, '', '', $referee->maxGamesPerDay, null, 1, false, 20, false, false, 'center', $rowspan);
            $this->displayInput('', 'text', View_Base::SPECIAL_INSTRUCTIONS, 'Special Instructions', '', $referee->specialInstructions, null, 1, false, 200, false, false, 'left', $rowspan);

            /** @var Family $family */
            $family = null;
            Family::findByPhone($this->m_controller->m_season, $referee->phone, $family);
            $familyData = isset($family) ? "$family->name ($family->phone1, $family->phone2)" : "&nbsp";
            print "
                    <td>$familyData</td>";

            print "
                    <td rowspan='$rowspan' align='left'>
                        <input style='background-color: lightgreen' name='" . View_Base::SUBMIT . "' type='submit' value='" . View_Base::UPDATE . "'>
                        <input type='hidden' id='" . self::REFEREE_ID . "' name='" . self::REFEREE_ID . "' value='$referee->id'>
                        <input type='hidden' id='" . self::FILTER_DIVISION_ID . "' name='" . self::FILTER_DIVISION_ID . "' value='$filterDivisionId'>
                        <input type='hidden' id='sessionId' name='sessionId' value='$sessionId'>
                    </td>
                </tr>";

            // Print remaining coach info
            print "
                </form>";
        }

        print "
            </table>
            ";
    }

    /**
     * @param int   $filterDivisionId
     * @param int   $filterRefereeId
     */
    private function printRefereeSelectors($filterDivisionId, $filterRefereeId)
    {
        $sessionId = $this->m_controller->getSessionId();

        print "
            <table valign='top' align='center' width='625' border='1' cellpadding='5' cellspacing='0'>
                <tr><td bgcolor='" . View_Base::CREATE_COLOR . "'>
                    <table valign='top' align='center' width='300' border='0' cellpadding='5' cellspacing='0'>
                        <tr>
                            <td valign='top'>";

        $this->printCreateReferee($sessionId);

        print "
                            </td>
                        </tr>
                    </table>
                    </td>
                    <td bgcolor='" . View_Base::VIEW_COLOR . "' valign='top'>
                    <table valign='top' align='center' width='200' border='0' cellpadding='5' cellspacing='0'>
                        <tr>
                            <td>";

        $this->printSelectReferee($sessionId, $filterDivisionId, $filterRefereeId);

        print "
                            </td>
                        </tr>
                    </table>
                    </td>
                </tr>
            </table>";
    }

    /**
     * @param int   $sessionId
     * @param int   $filterDivisionId
     * @param int   $filterRefereeId
     */
    private function printSelectReferee($sessionId, $filterDivisionId, $filterRefereeId)
    {
        print "
            <form method='post' action='" . self::REFEREE_REFEREES_PAGE . $this->m_urlParams . "'>
                <tr>
                    <td colspan='2'><strong> View Referees</strong></td>
                </tr>";

        $this->printDivisionSelector($filterDivisionId, true);
        $this->printRefereeSelector($filterRefereeId);

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
     */
    private function printCreateReferee($sessionId)
    {
        print "
            <form method='post' action='" . self::REFEREE_REFEREES_PAGE . $this->m_urlParams . "'>
                <tr>
                    <td colspan='2'><strong>Create Referee</strong></td>
                </tr>";

        $this->displayInput("Name:", 'text', View_Base::NAME, 'Name', '');
        $this->displayInput("Email:", 'text', View_Base::EMAIL_ADDRESS, 'Email', '');
        $this->displayInput("Phone:", 'text', View_Base::PHONE1, 'Phone', '');
        $this->printRefereeBadgeSelector(-1, true, true);

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