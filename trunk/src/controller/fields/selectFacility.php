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
            $view = new View_Fields_Login($this);
        } else {
            switch ($this->m_operation) {
                case View_Base::SELECT:
                    if ($this->createReservation()) {
                        $view = new View_Fields_ShowReservation($this);
                    } else {
                        $view = new View_Fields_SelectFacility($this);
                    }
                    break;

                case View_Base::FILTER:
                    $view = new View_Fields_SelectFacility($this);
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

        // All is good, create the reservation
        $this->m_reservations[] = Model_Fields_Reservation::Create($this->m_season, $this->m_field, $this->m_team, $this->m_startTime, $this->m_endTime, $daysSelectedString);

        return TRUE;
    }
}