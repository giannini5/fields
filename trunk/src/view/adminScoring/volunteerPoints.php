<?php

use \DAG\Domain\Schedule\Coach;
use \DAG\Domain\Schedule\Division;
use \DAG\Domain\Schedule\Team;

/**
 * @brief Show the Volunteer Points page
 */
class View_AdminScoring_VolunteerPoints extends View_AdminScoring_Base
{
    /**
     * @brief Construct the View
     *
     * @param Controller_Base $controller - Controller that contains data used when rendering this view.
     */
    public function __construct($controller)
    {
        parent::__construct(self::SCORING_VOLUNTEER_POINTS_PAGE, $controller);
    }

    /**
     * @brief Render data for display on the page.
     */
    public function renderPage()
    {
        $sessionId          = $this->m_controller->getSessionId();
        $divisionsSelector  = $this->getDivisionsSelector(true, false, true, true);

        $messageString = $this->m_controller->m_messageString;
        if (!empty($messageString)) {
            print "
                <p style='color: green' align='center'><strong>$messageString</strong></p><br>";
        }

        print "
            <table valign='top' align='center' width='400' border='1' cellpadding='5' cellspacing='0'>
                <tr>
                    <td valign='top' bgcolor='" . View_Base::VIEW_COLOR . "'>";

        $this->_printEnterVolunteerPoints($sessionId, $divisionsSelector);

        print "
                    </td>
                </tr>
            </table>
            <br><br>";

        $this->printUpdateDivisionVolunteerPointsForm($sessionId, $this->m_controller->m_division);
    }

    /**
     * @brief Print the form to enter volunteer points for teams by division.  Form includes the following
     *        - List of Divisions
     *
     * @param int   $sessionId          - Session Identifier
     * @param array $divisionsSelector  - List of divisionId => name
     */
    private function _printEnterVolunteerPoints($sessionId, $divisionsSelector)
    {
        print "
            <table valign='top' align='center' border='0' cellpadding='5' cellspacing='0'>
                <tr>
                    <th nowrap align='left' colspan='2'>Enter/Update Volunteer Points</th>
                </tr>
            <form method='post' action='" . self::SCORING_VOLUNTEER_POINTS_PAGE . $this->m_urlParams . "'>";

        $this->displaySelector('Division:', View_Base::DIVISION_NAME, '', $divisionsSelector, $this->m_controller->m_divisionName);

        // Print Enter button and end form
        print "
                <tr>
                    <td align='left'>
                        <input style='background-color: yellow' name='" . View_Base::SUBMIT . "' type='submit' value='" . View_Base::ENTER . "'>
                        <input type='hidden' id='" . View_Base::SCORING_TYPE . "' name='" . View_Base::SCORING_TYPE . "' value='" . Controller_AdminScoring_VolunteerPoints::VOLUNTEER_POINTS . "'>
                        <input type='hidden' id='sessionId' name='sessionId' value='$sessionId'>
                    </td>
                </tr>
            </form>
            </table>";
    }

    /**
     * @param int       $sessionId
     * @param Division  $division
     */
    private function printUpdateDivisionVolunteerPointsForm($sessionId, $division)
    {
        if (!isset($division)) {
            return;
        }

        $divisionName   = $division->nameWithGender;
        $teams          = Team::lookupByDivision($division);
        $scoringType    = Controller_AdminScoring_VolunteerPoints::VOLUNTEER_POINTS;
        $bgColor        = 'lightskyblue';

        print "
            <table valign='top' align='center' border='1' cellpadding='5' cellspacing='0'>
                <tr bgcolor='$bgColor'>
                    <th colspan='3'>$divisionName</th>
                </tr>
                <tr bgcolor='$bgColor'>
                    <th>Team Id</th>
                    <th>Coach</th>
                    <th>Volunteer Points</th>
                </tr>
                <form method='post' action='" . self::SCORING_VOLUNTEER_POINTS_PAGE . $this->m_urlParams . "'>";

        foreach ($teams as $team) {
            $coach = Coach::lookupByTeam($team);
            print "
                <tr>
                    <td>$team->nameId</td>
                    <td>$coach->shortName</td>";

            $name = View_Base::VOLUNTEER_POINTS_DATA . "[$team->id]";
            $this->displayInput('', 'number', $name, '', '', $team->volunteerPoints, null, 1, false, 50, false, true, 'center');

            print "
                </tr>";
        }

        print "
                <tr>
                    <td colspan='3' align='left'>
                        <input style='background-color: yellow' name='" . View_Base::SUBMIT . "' type='submit' value='" . View_Base::UPDATE . "'>
                        <input type='hidden' id='" . View_Base::SCORING_TYPE . "' name='" . View_Base::SCORING_TYPE . "' value='$scoringType'>
                        <input type='hidden' id='" . View_Base::DIVISION_NAME . "' name='" . View_Base::DIVISION_NAME . "' value='$divisionName'>
                        <input type='hidden' id='sessionId' name='sessionId' value='$sessionId'>
                    </td>
                </tr>
            </form>
            </table>";
    }
}