<?php

/**
 * Class Controller_SelectFacility
 *
 * @brief On GET, render page to select facility.
 *        On POST, verify data and then continue to process reservation.
 */
class Controller_Fields_SelectFacility extends Controller_Fields_Base {
    public $m_newSelection;

    public function __construct() {
        parent::__construct();

        $this->m_newSelection = 0;

        if (isset($_SERVER['REQUEST_METHOD']) and $_SERVER['REQUEST_METHOD'] == 'POST') {
            $fieldId = $this->getPostAttribute(View_Base::FIELD_ID, null);
            if ($fieldId != null) {
                $this->m_field = Model_Fields_Field::LookupById($fieldId);
            }

            $this->m_startTime = $this->getPostAttribute(View_Base::START_TIME, null);
            $this->m_endTime = $this->getPostAttribute(View_Base::END_TIME, null);

            $this->m_daysSelected[View_Base::MONDAY] = $this->_isDaySelected(View_Base::MONDAY);
            $this->m_daysSelected[View_Base::TUESDAY] = $this->_isDaySelected(View_Base::TUESDAY);
            $this->m_daysSelected[View_Base::WEDNESDAY] = $this->_isDaySelected(View_Base::WEDNESDAY);
            $this->m_daysSelected[View_Base::THURSDAY] = $this->_isDaySelected(View_Base::THURSDAY);
            $this->m_daysSelected[View_Base::FRIDAY] = $this->_isDaySelected(View_Base::FRIDAY);
            $this->m_daysSelected[View_Base::SATURDAY] = $this->_isDaySelected(View_Base::SATURDAY);
            $this->m_daysSelected[View_Base::SUNDAY] = $this->_isDaySelected(View_Base::SUNDAY);

            $this->m_filterFacilityId = $this->getPostAttribute(View_Base::FILTER_FACILITY_ID, 0);
            $this->m_filterDivisionId = $this->getPostAttribute(View_Base::FILTER_DIVISION_ID, 0);
            $this->m_filterLocationId = $this->getPostAttribute(View_Base::FILTER_LOCATION_ID, 0);

        } elseif (isset($_SERVER['REQUEST_METHOD']) and $_SERVER['REQUEST_METHOD'] == 'GET') {
            $this->m_newSelection = $this->getGetAttribute(View_Base::NEW_SELECTION, 0);
        }

        // Default m_filterDivisionId to coaches division unless the filter operation has been selected
        if ($this->m_filterDivisionId == 0 and $this->m_operation != View_Base::FILTER) {
            $this->m_filterDivisionId = $this->m_coach->divisionId;
        }
    }

    /**
     * @brief Process the GET or POST
     */
    public function process() {
        // Re-direct to Login page if use is not authenticated
        if (!$this->m_isAuthenticated) {
            $this->getAuthenticated(View_Base::SELECT_FACILITY_PAGE);
            return;
        } else {
            switch ($this->m_operation) {
                case View_Base::SELECT:
                    if ($this->createReservation()) {
                        $this->m_filterFacilityId = 0;
                        $this->m_filterDivisionId = 0;
                        $this->m_filterTeamId = $this->m_team->id;

                        $view = new View_Fields_ShowAllReservations($this);
                    } else {
                        $view = new View_Fields_SelectFacility($this);
                    }
                    break;

                case View_Base::FILTER:
                    $view = new View_Fields_SelectFacility($this);
                    break;

                case View_Base::SIGN_OUT:
                    $this->signOut();
                    $view = new View_Fields_Login($this, View_Base::LOGIN_PAGE);
                    break;

                default:
                    if (count($this->m_reservations) > 0 and !$this->m_newSelection) {
                        $view = new View_Fields_ShowReservation($this);
                    } else {
                        $view = new View_Fields_SelectFacility($this);
                    }
                    break;
            }
        }

        $view->displayPage();
    }

    private function createReservation() {
        // All of the following must be TRUE or the reservation is denied:
        // 1. StartTime is less then EndTime
        // 2. Total time for all reservations for team is within limit allowed
        // 3. At least one day is selected
        // 4. Reservation does not overlap with another team's reservation
        // 5. At most two days are select (1 for teams that are only allowed to practice one day
        //    per week.
        // 6. Times selected are within times available for the field.

        // 1. StartTime is less then EndTime
        $startDateTime = DateTime::createFromFormat('Y-m-d H:i:s', "2015-06-01 $this->m_startTime" . ":00");
        $endDateTime = DateTime::createFromFormat('Y-m-d H:i:s', "2015-06-01 $this->m_endTime" . ":00");

        // Normalize the start and end time for proper comparison in below tests
        $this->m_startTime = $startDateTime->format('H:i:s');
        $this->m_endTime = $endDateTime->format('H:i:s');

        $diff = $startDateTime->diff($endDateTime);
        if ($diff->invert >= 1) {
            $this->m_createReservationError = "ERROR 1:<br>Start Time ($this->m_startTime) occurs on or after End Time ($this->m_endTime).";
            $this->m_createReservationError .= "<br>Please try again.";
            return FALSE;
        }

        // 2. Total time for all reservations for team is within limit allowed
        // TODO: check limit
        // $this->m_createReservationError = "ERROR 2:<br>Total reservation time would exceed limit for your team.";
        // $this->m_createReservationError .= "<br>Please try again.";

        // 3. Verify that at least one day is selected
        $daysSelected = 0;
        $daysSelectedString = '';
        foreach ($this->m_daysSelected as $day=>$selected) {
            $daysSelectedString .= $selected ? '1' : '0';
            $daysSelected += $selected ? 1 : 0;
        }
        if ($daysSelected <= 0) {
            $this->m_createReservationError = "ERROR 3:<br>No practices days were selected.";
            $this->m_createReservationError .= "<br>Please try again.";
            return FALSE;
        }

        // 4. Reservation does not overlap with another team's reservation
        $overlapReservation = Model_Fields_Reservation::getOverlapping($this->m_season, $this->m_field, $this->m_startTime, $this->m_endTime, $daysSelectedString);
        if ($overlapReservation != NULL) {
            $this->m_createReservationError = "ERROR 4:<br>Your selected days and times overlap with an existing reservation.";
            $this->m_createReservationError .= "<br>Please try again.";
            return FALSE;
        }

        // 5. Verify that at most two days are selected
        if ($daysSelected > 2) {
            $this->m_createReservationError = "ERROR 5:<br>Too many practice days were selected.  Two is the max.";
            $this->m_createReservationError .= "<br>Please try again.";
            return FALSE;
        }

        // 6. Times selected are within times available for the field.
        $fieldAvailability = Model_Fields_FieldAvailability::LookupByFieldId($this->m_field->id);
        if ($this->m_startTime < $fieldAvailability->startTime) {
            $this->m_createReservationError = "ERROR 6:<br>Invalid start time of $this->m_startTime.<br>Field is only available from $fieldAvailability->startTime to $fieldAvailability->endTime.";
            $this->m_createReservationError .= "<br>Please try again.";
            return FALSE;
        }
        if ($this->m_endTime > $fieldAvailability->endTime) {
            $this->m_createReservationError = "ERROR 6:<br>Invalid end time of $this->m_endTime.<br>Field is only available from $fieldAvailability->startTime to $fieldAvailability->endTime.";
            $this->m_createReservationError .= "<br>Please try again.";
            return FALSE;
        }

        // All is good, create the reservation
        $reservation = Model_Fields_Reservation::Create($this->m_season, $this->m_field, $this->m_team, $this->m_startTime, $this->m_endTime, $daysSelectedString);
        $this->m_reservations[] = $reservation;

        // Send confirmation email
        $this->_sendConfirmationEmail($reservation);

        return TRUE;
    }

    /**
     * @brief Send a reservation confirmation email from the Practice Field Coordinator to the Coach.
     *
     * @param $reservation
     */
    private function _sendConfirmationEmail($reservation) {
        $practiceFieldCoordinators = Model_Fields_PracticeFieldCoordinator::LookupByLeague($this->m_league);
        assertion(count($practiceFieldCoordinators) >= 1, "ERROR: Zero Practice Field Coordinators found for league: " . $this->m_league->name);

        $practiceFieldCoordinatorName = $practiceFieldCoordinators[0]->name;
        $fromAddress = $practiceFieldCoordinators[0]->email;
        $toAddress = $this->m_coach->email;
        $subject = 'AYSO Practice Field Approval';
        $coachName = $this->m_coach->name;
        $divisionName = $this->m_division->name;
        $gender = $this->m_team->gender;
        $facilityName = $this->m_facility->name;
        $fieldName = $this->m_field->name;
        $imageURL = $this->m_facility->image;
        $daysSelected = $this->getDaysSelectedString($reservation);
        $times = "$reservation->startTime - $reservation->endTime";
        $title = $this->m_league->name . " Practice Field Coordinator";
        $fieldAvailability = Model_Fields_FieldAvailability::LookupByFieldId($this->m_field->id);
        $startDate = $fieldAvailability->startDate;
        $endDate = $fieldAvailability->endDate;

        $headers = "From: $fromAddress\r\n";
        $headers .= "To: $toAddress\r\n";
        $headers .= "Reply-To: $fromAddress\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";

        $message = "
            <html>
                <body>
                    <p>
                        Hey $coachName ($divisionName-$gender),
                        <br>
                        <br>
                        Your team's practice field request has been approved.
                        <br>
                    </p>
                    <table border='0'>
                        <tr>
                            <td><b>Space:</b></td>
                            <td>$facilityName (<a href='$imageURL'>Field $fieldName</a>)</td>
                        </tr>
                        <tr>
                            <td><b>Days:</b></td>
                            <td>$daysSelected</td>
                        </tr>
                        <tr>
                            <td><b>Times:</b></td>
                            <td>$times</td>
                        </tr>
                        <tr>
                            <td><b>Start Date:</b></td>
                            <td>$startDate</td>
                        </tr>
                        <tr>
                            <td><b>End Date:</b></td>
                            <td>$endDate</td>
                        </tr>
                    </table>
                    <p>
                        <br>
                        $practiceFieldCoordinatorName
                        <br>
                        $title
                    </p>
                </body>
            </html>";

        $result = mail($toAddress, $subject, $message, $headers);
        $resultString = $result ? "Success" : "Failure";
        $this->m_reservationConfirmationMessage = "Confirmation email has been sent to $toAddress";
    }
}