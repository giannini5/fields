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

            $this->m_daysSelected[View_Base::MONDAY]    = $this->_isDaySelected(View_Base::MONDAY);
            $this->m_daysSelected[View_Base::TUESDAY]   = $this->_isDaySelected(View_Base::TUESDAY);
            $this->m_daysSelected[View_Base::WEDNESDAY] = $this->_isDaySelected(View_Base::WEDNESDAY);
            $this->m_daysSelected[View_Base::THURSDAY]  = $this->_isDaySelected(View_Base::THURSDAY);
            $this->m_daysSelected[View_Base::FRIDAY]    = $this->_isDaySelected(View_Base::FRIDAY);
            $this->m_daysSelected[View_Base::SATURDAY]  = $this->_isDaySelected(View_Base::SATURDAY);
            $this->m_daysSelected[View_Base::SUNDAY]    = $this->_isDaySelected(View_Base::SUNDAY);

            $this->m_filterFacilityId = $this->getPostAttribute(View_Base::FILTER_FACILITY_ID, 0);
            $this->m_filterDivisionId = $this->getPostAttribute(View_Base::FILTER_DIVISION_ID, 0);
            $this->m_filterLocationId = $this->getPostAttribute(View_Base::FILTER_LOCATION_ID, 0);
            $this->m_filterTeamId     = $this->getPostAttribute(View_Base::FILTER_TEAM_ID, 0);
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
            $this->getAuthenticated(View_Base::SELECT_FIELD_PAGE);
            return;
        } else {
            switch ($this->m_operation) {
                case View_Base::SELECT:
                    if ($this->createReservation()) {
                        $this->m_filterFacilityId   = 0;
                        $this->m_filterDivisionId   = 0;
                        $this->m_filterTeamId       = $this->m_filterTeamId == 0 ? $this->m_team->id : $this->m_filterTeamId;

                        $view = new View_Fields_ShowReservation($this);
                    } else {
                        // $view = new View_Fields_SelectFacility($this);
                        $view = new View_Fields_SelectField($this);
                    }
                    break;

                case View_Base::FILTER:
                    // $view = new View_Fields_SelectFacility($this);
                    $view = new View_Fields_SelectField($this);
                    break;

                case View_Base::SIGN_OUT:
                    $this->signOut();
                    $view = new View_Fields_Login($this, View_Base::LOGIN_PAGE);
                    break;

                default:
                    if (count($this->m_reservations) > 0 and !$this->m_newSelection) {
                        $view = new View_Fields_ShowReservation($this);
                    } else {
                        // $view = new View_Fields_SelectFacility($this);
                        $view = new View_Fields_SelectField($this);
                    }
                    break;
            }
        }

        $view->displayPage();
    }

    /**
     * @param bool $adminOverride - default to false.  When true then override rule 7
     *
     * @return bool
     */
    public function createReservation($adminOverride = false) {
        // All of the following must be TRUE or the reservation is denied:
        // 0. Season is open for practice field reservations
        // 1. StartTime is less then EndTime
        // 2. At least one day is selected
        // 3. At most two days are select (1 for teams that are only allowed to practice one day
        //    per week.
        // 4. Times selected are within times available for the field.
        // 5. Total time for all reservations for team is within limit allowed
        // 6. Reservation does not overlap with another team's reservation
        // 7. Division is allowed to practice at selected field

        // If adminOverride enabled then update team, coach, division and reservations

        if ($adminOverride) {
            $this->m_team       = Model_Fields_Team::LookupById($this->m_filterTeamId);
            $this->m_coach      = $this->m_team->m_coach;
            $this->m_division   = Model_Fields_Division::LookupById($this->m_coach->divisionId);
            $this->_getReservations();
        }

        // 0. Make sure we are taking reservations
        if (!$this->m_season->okayToReserveField()) {
            $beginDateTime                  = DateTime::createFromFormat('Y-m-d H:i:s', $this->m_season->beginReservationsDate);
            $beginDateString                = $beginDateTime->format('m-d-Y');
            $this->m_createReservationError =  "The earliest you can use this tool to select a field for practice is<br>$beginDateString.";
            return false;
        }

        // 1. StartTime is less then EndTime
        $startDateTime  = DateTime::createFromFormat('Y-m-d H:i:s', "2015-06-01 $this->m_startTime" . ":00");
        $endDateTime    = DateTime::createFromFormat('Y-m-d H:i:s', "2015-06-01 $this->m_endTime" . ":00");

        // Normalize the start and end time for proper comparison in below tests
        $this->m_startTime  = $startDateTime->format('H:i:s');
        $this->m_endTime    = $endDateTime->format('H:i:s');

        $diff = $startDateTime->diff($endDateTime);
        if ($diff->invert >= 1) {
            $this->m_createReservationError = "ERROR 1:<br>Start Time ($this->m_startTime) occurs on or after End Time ($this->m_endTime).";
            $this->m_createReservationError .= "<br>Please try again.";
            return false;
        }

        // 2. Verify that at least one day is selected and that the day selected is available for reserving
        $fieldAvailability  = Model_Fields_FieldAvailability::LookupByFieldId($this->m_field->id);
        $daysSelected       = 0;
        $dayOfWeekIndex     = 0;
        $daysSelectedString = '';
        foreach ($this->m_daysSelected as $day=>$selected) {
            $daysSelectedString .= $selected ? '1' : '0';
            $daysSelected       += $selected ? 1 : 0;
            if ($selected) {
                if (!$fieldAvailability->isFieldAvailable($dayOfWeekIndex)) {
                    $this->m_createReservationError = "ERROR 2:<br>Permit not available on day selected.";
                    $this->m_createReservationError .= "<br>Please try again.";
                    return FALSE;
                }
            }
            $dayOfWeekIndex += 1;
        }
        if ($daysSelected <= 0) {
            $this->m_createReservationError = "ERROR 2:<br>No practices days were selected.";
            $this->m_createReservationError .= "<br>Please try again.";
            return FALSE;
        }

        // 3. Verify that at most two days are selected
        if ($daysSelected > 2) {
            $this->m_createReservationError = "ERROR 3:<br>Too many practice days were selected.  Two is the max.";
            $this->m_createReservationError .= "<br>Please try again.";
            return FALSE;
        }

        // 4. Times selected are within times available for the field.
        if ($this->m_startTime < $fieldAvailability->startTime) {
            $this->m_createReservationError = "ERROR 4a:<br>Invalid start time of $this->m_startTime.<br>Field is only available from $fieldAvailability->startTime to $fieldAvailability->endTime.";
            $this->m_createReservationError .= "<br>Please try again.";
            return FALSE;
        }
        if ($this->m_endTime > $fieldAvailability->endTime) {
            $this->m_createReservationError = "ERROR 4b:<br>Invalid end time of $this->m_endTime.<br>Field is only available from $fieldAvailability->startTime to $fieldAvailability->endTime.";
            $this->m_createReservationError .= "<br>Please try again.";
            return FALSE;
        }

        // 5a. Total time for reservation for team is within limit allowed per practice
        $currentReservationMinutesPerPractice = ($diff->h * 60 + $diff->i);
        if ($currentReservationMinutesPerPractice > $this->m_team->m_division->maxMinutesPerPractice) {
            $hoursPerPractice = $this->m_team->m_division->maxMinutesPerPractice / 60;
            $this->m_createReservationError = "ERROR 5a:<br>You can practice at most $hoursPerPractice hour(s) per practice.";
            $this->m_createReservationError .= "<br>Please try again.";
            return FALSE;
        }

        // 5b. Total time for all reservations for team is within limit allowed
        $currentReservationMinutesPerWeek = $currentReservationMinutesPerPractice * $daysSelected;
        if (!$this->_isUnderTimeLimit($currentReservationMinutesPerWeek)) {
            $hoursPerWeek = $this->m_team->m_division->maxMinutesPerWeek / 60;
            $this->m_createReservationError = "ERROR 5b:<br>You can practice at most $hoursPerWeek hour(s) per week.  Click on Reservations tab to cancel existing reservation or create a shorter reservation.";
            $this->m_createReservationError .= "<br>Please try again.";
            return FALSE;
        }

        // 6. Reservation does not overlap with another team's reservation
        $overlapReservation = Model_Fields_Reservation::getOverlapping($this->m_season, $this->m_field, $this->m_startTime, $this->m_endTime, $daysSelectedString);
        if ($overlapReservation != NULL) {
            $this->m_createReservationError = "ERROR 6:<br>Your selected days and times overlap with an existing reservation.";
            $this->m_createReservationError .= "<br>Please try again.";
            return FALSE;
        }

        // 7. Team's division is allowed to practice at the selected field
        if (!$adminOverride) {
            $divisionField = Model_Fields_DivisionField::LookupByDivisionField($this->m_team->m_division->id, $this->m_facility->id, $this->m_field->id);
            if (!isset($divisionField)) {
                $divisionName = $this->m_team->m_division->name;
                $this->m_createReservationError = "ERROR 7:<br>Select field is not currently availabe for the $divisionName division.";
                $this->m_createReservationError .= "<br>Please try again.";
                return FALSE;
            }
        }

        // All is good, create the reservation
        $reservation = Model_Fields_Reservation::Create($this->m_season, $this->m_field, $this->m_team, $this->m_startTime, $this->m_endTime, $daysSelectedString);
        $this->m_reservations[] = $reservation;

        // Send confirmation email
        $this->_sendConfirmationEmail($reservation);

        return TRUE;
    }

    /**
     * @brief Determine if the total practice time is within the allowed practice time for the division.
     *
     * @param $currentReservationMinutes - Minutes for current reservation
     *
     * @return bool TRUE if total practice time is <= allowed practice time
     */
    private function _isUnderTimeLimit($currentReservationMinutes) {
        // Get existing reservations for team
        $reservations = Model_Fields_Reservation::LookupByTeam($this->m_season, $this->m_team, FALSE);

        // Compute total minutes of reservation time for team
        $minutes = $currentReservationMinutes;
        foreach ($reservations as $reservation) {
            $startDateTime  = DateTime::createFromFormat('Y-m-d H:i:s', "2015-06-01 $reservation->startTime");
            $endDateTime    = DateTime::createFromFormat('Y-m-d H:i:s', "2015-06-01 $reservation->endTime");
            $diff           = $startDateTime->diff($endDateTime);
            $numberOfDays   = $reservation->getNumberOfDays();
            $minutes        += ($diff->h * 60 + $diff->i) * $numberOfDays;
        }

        // Verify total time is under the weekly minute limit for team
        return $minutes <= $this->m_team->m_division->maxMinutesPerWeek;
    }

    /**
     * @brief Send a reservation confirmation email from the Practice Field Coordinator to the Coach.
     *
     * @param $reservation
     */
    private function _sendConfirmationEmail($reservation) {
        $practiceFieldCoordinators = Model_Fields_PracticeFieldCoordinator::LookupByLeague($this->m_league);
        assertion(count($practiceFieldCoordinators) >= 1, "ERROR: Zero Practice Field Coordinators found for league: " . $this->m_league->name);

        $practiceFieldCoordinatorName   = $practiceFieldCoordinators[0]->name;
        $fromAddress                    = $practiceFieldCoordinators[0]->email;
        $toAddress                      = $this->m_coach->email;
        $preApproved                    = $this->m_facility->preApproved == 1;
        $subject                        = $preApproved ? 'AYSO Practice Field Approval' : 'AYSO Practice Field Requires School Approval';
        $intro                          = $preApproved ? "Your team's practice field request has been approved." : "Your team's practice field request requires school approval.";
        $coachName                      = $this->m_coach->name;
        $divisionName                   = $this->m_division->name;
        $gender                         = $this->m_team->gender;
        $facilityName                   = $this->m_facility->name;
        $fieldName                      = $this->m_field->name;
        $imageURL                       = $this->getImageURL($this->m_facility->image);
        $daysSelected                   = $this->getDaysSelectedString($reservation);
        $times                          = "$reservation->startTime - $reservation->endTime";
        $title                          = $this->m_league->name . " Practice Field Coordinator";
        $fieldAvailability              = Model_Fields_FieldAvailability::LookupByFieldId($this->m_field->id);
        $startDate                      = $fieldAvailability->startDate;
        $endDate                        = $fieldAvailability->endDate;
        $contactName                    = $this->m_facility->contactName;
        $contactEmail                   = $this->m_facility->contactEmail;
        $contactPhone                   = $this->m_facility->contactPhone;

        $headers = "From: region122@webyouthsoccer.com\r\n";
        $headers .= "Cc: $fromAddress\r\n";
        $headers .= "Reply-To: $fromAddress\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";

        $message = "
            <html>
                <body>
                    <p>
                        Hey $coachName ($divisionName-$gender),
                        <br>
                        <br>
                        $intro
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
                    </table>";

        if (!$preApproved) {
            $message .= "
                    <p>
                        <font color='red'>
                            <strong>Next Step:</strong> Contact $contactName, $contactEmail, $contactPhone to fill out requested paperwork and pay any fees.
                            Reply to this message with your receipt for any fees paid along with your name and address and AYSO will reimburse you for the fees.
                            Be sure to take this next step ASAP since the school makes the final decision on who gets to use their fields.
                        </font>
                    </p>";
        }

        $message .= "
                    <p>
                        <br>
                        $practiceFieldCoordinatorName
                        <br>
                        $title
                        <br><br>
                        During practice, only park in designated parking areas.  Please make sure no one drives onto the field, parks illegally or uses roads designated for emergency vehicles.
                    </p>
                </body>
            </html>";

//        $result = mail($toAddress, $subject, $message, $headers);
//        $resultString = $result ? "." : "";
        $resultString = "";
        if ($preApproved) {
//            $this->m_reservationConfirmationMessage = "Confirmation email has been sent to {$toAddress}$resultString";
            $this->m_reservationConfirmationMessage = $message;
        } else {
            $this->m_reservationConfirmationMessage .= "<font color='red'>Your reservation requires school approval.  See email that was sent to $toAddress for next steps$resultString</font>";
        }
    }

    /**
     * Return a URL for the Image so that it can be clicked on in an email for display.
     *
     * @param  string   $image    - Facility image
     * @return string   $imageURL - URL to facility image
     */
    private function getImageURL($image) {
        $imageURL = $image;

        $result = strpos($image, 'http://');
        if (is_bool($result)) {
//            $imageURL = $_SERVER['HTTP_HOST'] . "/image?image=$image";
            $imageURL = "/image?image=$image";
        }

        return $imageURL;
    }
}
