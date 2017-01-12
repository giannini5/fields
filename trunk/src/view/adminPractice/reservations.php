<?php

/**
 * @brief Show the all reservations (if any) for all teams by division.
 */
class View_AdminPractice_Reservations extends View_AdminPractice_Base {
    /**
     * @brief Construct the View
     *
     * @param $controller - Controller that contains data used when rendering this view.
     */
    public function __construct($controller) {
        parent::__construct(self::ADMIN_RESERVATIONS_PAGE, $controller);
    }

    /**
     * @brief Render data for display on the page.
     */
    public function render() {
        $facilities = $this->m_controller->getFacilities();
        $filterFacilityId = $this->m_controller->m_filterFacilityId;
        $filterDivisionId = $this->m_controller->m_filterDivisionId;
        $filterTeamId = $this->m_controller->m_filterTeamId;

        $this->_printReservationSelectors($facilities, $filterFacilityId, $filterDivisionId, $filterTeamId);
        print "<h1>&nbsp;</h1>";

        $reservations = $this->_getReservations($filterFacilityId, $filterDivisionId, $filterTeamId);
        $boyTeamCount = $this->getTeamCount($reservations, 'B');
        $girlTeamCount = $this->getTeamCount($reservations, 'G');
        $teamCount = $boyTeamCount + $girlTeamCount;

        print "
            <table align='center' valign='top' border='1' cellpadding='5' cellspacing='0'>
                <tr bgcolor='lightskyblue'>
                    <td colspan=9 align='center' style='font-size:24px'><font color='darkblue'><b>Reservations</b></font><br><font size='2'>$teamCount (Boys: $boyTeamCount, Girls: $girlTeamCount)</font></td>
                </tr>
                <tr bgcolor='lightskyblue'>
                    <th>Division</th    >
                    <th>Coach</th>
                    <th>Email</th>
                    <th>Phone Number</th>
                    <th>Facility</th>
                    <th>Field</th>
                    <th>Days</th>
                    <th>Times</th>
                </tr>";

        foreach ($reservations as $reservation) {
            $division = $reservation->m_team->m_division->name . $reservation->m_team->gender;
            $coach = $reservation->m_team->m_coach->name;
            $email = $reservation->m_team->m_coach->email;
            $phone = $reservation->m_team->m_coach->phone;
            $facility = $reservation->m_field->m_facility->name;
            $field = "Field " . $reservation->m_field->name;
            $days = $this->m_controller->getDaysSelectedString($reservation);
            $times = "$reservation->startTime - $reservation->endTime";

            print "
                <tr>
                    <td>$division</td>
                    <td>$coach</td>
                    <td>$email</td>
                    <td>$phone</td>
                    <td>$facility</td>
                    <td>$field</td>
                    <td>$days</td>
                    <td>$times</td>
                </tr>";
        }

        print "
            </table>";
    }

    /**
     * @brief Print the filtering selectors
     *
     * @param $facilities - List of facilities for filtering
     * @param $filterFacilityId - Show selected facility or All if none selected
     * @param $filterDivisionId - Show selected division or All if non selected
     * @param $filterTeamId - Show selected geographicArea or All if non selected
     */
    private function _printReservationSelectors($facilities, $filterFacilityId, $filterDivisionId, $filterTeamId) {
        $sessionId = $this->m_controller->getSessionId();

        print "
            <table valign='top' align='center' width='625' border='1' cellpadding='5' cellspacing='0'>
            <tr><td>
            <table valign='top' align='center' width='625' border='0' cellpadding='5' cellspacing='0'>
            <form method='post' action='" . self::ADMIN_RESERVATIONS_PAGE . $this->m_urlParams . "'>";

        $this->printFacilitySelector($facilities, $filterFacilityId);
        $this->printDivisionSelector($filterDivisionId);
        $this->printTeamSelector($filterTeamId);

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
     * @brief Return a list of reservations based on filter
     *
     * @param $filterFacilityId - Only include this facilities if filter enabled
     * @param $filterDivisionId - Only include this divisions if filter enabled
     * @param $filterTeamId - Only include this team if filter enabled
     *
     * @return array $reservations
     */
    private function _getReservations($filterFacilityId, $filterDivisionId, $filterTeamId) {
        return $this->m_controller->getFilteredReservations($filterFacilityId, $filterDivisionId, $filterTeamId);
    }
}