<?php

/**
 * @brief Select the Facility/Field for the reservation.
 *
 * @param $controller - Controller that contains data used when rendering this view.
 */
class View_Fields_SelectField extends View_Fields_Base {
    const LONG_NAME     = 'long';
    const SHORT_NAME    = 'short';

    private $m_days;
    private $m_times;

    /**
     * @brief Construct the Select Facility View
     *
     * @param $controller - Controller that contains data used when rendering this view.
     */
    public function __construct($controller) {
        parent::__construct(self::SELECT_FIELD_PAGE, $controller);

        $this->m_days = array();
        $this->m_times = array();

        $this->m_days[View_Base::MONDAY][self::LONG_NAME]       = View_Base::MONDAY;
        $this->m_days[View_Base::MONDAY][self::SHORT_NAME]      = 'Mon';
        $this->m_days[View_Base::TUESDAY][self::LONG_NAME]      = View_Base::TUESDAY;
        $this->m_days[View_Base::TUESDAY][self::SHORT_NAME]     = 'Tues';
        $this->m_days[View_Base::WEDNESDAY][self::LONG_NAME]    = View_Base::WEDNESDAY;
        $this->m_days[View_Base::WEDNESDAY][self::SHORT_NAME]   = 'Wed';
        $this->m_days[View_Base::THURSDAY][self::LONG_NAME]     = View_Base::THURSDAY;
        $this->m_days[View_Base::THURSDAY][self::SHORT_NAME]    = 'Thur';
        $this->m_days[View_Base::FRIDAY][self::LONG_NAME]       = View_Base::FRIDAY;
        $this->m_days[View_Base::FRIDAY][self::SHORT_NAME]      = 'Fri';

        $this->m_times['3:00'] = '3:00 - 3:30';
        $this->m_times['3:30'] = '3:30 - 4:00';
        $this->m_times['4:00'] = '4:00 - 4:30';
        $this->m_times['4:30'] = '4:30 - 5:00';
        $this->m_times['5:00'] = '5:00 - 5:30';
        $this->m_times['5:30'] = '5:30 - 6:00';
        $this->m_times['6:00'] = '6:00 - 6:30';
        $this->m_times['6:30'] = '6:30 - 7:00';
    }

    /**
     * @brief Render data for display on the page.
     */
    public function render() {
        $facilities = $this->m_controller->getFacilities();
        $filterFacilityId = $this->m_controller->m_filterFacilityId;
        $filterDivisionId = $this->m_controller->m_filterDivisionId;
        $filterLocationId = $this->m_controller->m_filterLocationId;

        if ($filterDivisionId == 0 and $this->m_controller->m_operation != View_Base::FILTER) {
            $filterDivisionId = $this->m_controller->m_coach->divisionId;
        }


        if (count($facilities) == 0) {
            print "<p>Uh, sorry, no facilities found - better call Dave!";
            return;
        }

        $this->_printReservationError();
        $this->_printFacilitySelectors($facilities, $filterFacilityId, $filterDivisionId, $filterLocationId);
        // print "<p>&nbsp;</p>";
        print "<br>";

        $javaScriptClassIdentifier = 0;
        foreach ($facilities as $facility) {
            $javaScriptClassIdentifier += 1;

            // skip this facility if facility filter enabled and facility does not match the filter
            if ($filterFacilityId != 0) {
                if ($facility->id != $filterFacilityId) {
                    continue;
                }
            }


            // skip this facility if geographic area filter enabled and facility is not in the selected area
            if ($filterLocationId != 0) {
                if (!$facility->isInLocation($filterLocationId)) {
                    continue;
                }
            }

            // skip this facility if division filter enabled and facility does not support teams in this division
            if ($filterDivisionId != 0) {
                if (!$facility->hasFieldsInDivision($filterDivisionId)) {
                    continue;
                }
            }

            print "<div class='accordion'>";
/*
            print "
            <table valign='top' align='center' width='400' border='1' cellpadding='5' cellspacing='0'>
                <tr>
                    <td>";
*/

            $this->_printSelectFieldForm(4, $facility, $filterDivisionId,
                "expandContract$javaScriptClassIdentifier", "collapsible$javaScriptClassIdentifier");

/*
            print "
                    </td>
                </tr>
            </table>
            <br>";
*/
            print "</div>";
        }
    }

    /**
     * @brief Print the error seen with the last reservation attempt (no op if no error)
     */
    private function _printReservationError() {
        if (! empty($this->m_controller->m_createReservationError)) {
            $errorString = $this->m_controller->m_createReservationError;

            print "
            <table valign='top' align='center' width='625' border='0' cellpadding='5' cellspacing='0'>
                <tr>
                    <td><h1 align='left'><font color='red' size='4'>$errorString</font></h1></td>
                </tr>
            </table>";
        }
    }

    /**
     * @brief Print the filtering selectors
     *
     * @param $facilities - List of facilities for filtering
     * @param $filterFacilityId - Show selected facility or All if none selected
     * @param $filterDivisionId - Show selected division or All if non selected
     * @param $filterLocationId - Show selected geographicArea or All if non selected
     */
    private function _printFacilitySelectors($facilities, $filterFacilityId, $filterDivisionId, $filterLocationId) {
        $sessionId = $this->m_controller->getSessionId();

        print "
            <table valign='top' align='center' width='625' border='1' cellpadding='5' cellspacing='0'>
            <tr><td>
            <table valign='top' align='center' width='625' border='0' cellpadding='5' cellspacing='0'>
            <form method='post' action='" . self::SELECT_FIELD_PAGE . $this->m_urlParams . "'>";

        print $this->printFacilitySelector($facilities, $filterFacilityId);
        print $this->printDivisionSelector($filterDivisionId);
        print $this->printGeographicSelector($filterLocationId);

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
     * @brief Print the form to select a field.  Form includes the following
     *        - Name and address of the facility
     *        - Field layout map
     *        - Field assignments
     *        - Selectors for start time, end time and days
     *
     * @param $maxColumns - Number of columns the form is covering
     * @param $facility - Facility that contains the fields
     * @param $filterDivisionId - Filter out fields that are not allowed for the specified division
     * @param $expandContract - Expand contract java script class
     * @param $collapsible - Collapsible java script class
     */
    private function _printSelectFieldForm($maxColumns, $facility, $filterDivisionId, $expandContract, $collapsible) {
        $sessionId = $this->m_controller->getSessionId();

        // Print the start of the form to select a facility
        print "<h2 style='text-decoration: underline'><b>$facility->name</b></h2>";
        print "<div class='pane'>";
        print "
            <table id='viewTable' class='table' valign='top' align='center' border='0' cellpadding='5' cellspacing='0'>
            <form method='post' action='" . self::SELECT_FIELD_PAGE . $this->m_urlParams . "'>";

        $this->_printFacilityInfo($maxColumns, $facility, $expandContract, $collapsible);

        if ($filterDivisionId == 0) {
            $fields = $this->m_controller->getFields($facility, TRUE);
        } else {
            $fields = $facility->getFieldsInDivision($filterDivisionId, TRUE);
        }

        // print "<tr class='$collapsible'><td>&nbsp</td></tr>";
        $this->_printFieldSelector($maxColumns, $fields, NULL);
        $this->printTimeSelectors($maxColumns, '03:30:00', '07:00:00', NULL);
        $this->printDaySelector($maxColumns, NULL);

        // Print Submit button and end form
        print "
                <tr>
                    <td align='left'>
                        <input style='background-color: yellow' name='" . View_Base::SUBMIT . "' type='submit' value='" . View_Base::SELECT . "'>
                        <input type='hidden' id='facilityId' name='facilityId' value='$facility->id'>
                        <input type='hidden' id='sessionId' name='sessionId' value='$sessionId'>
                    </td>
                </tr>
                <tr>
                    <td>&nbsp</td>
                </tr>
            </form>";

        $this->_printFieldsAssigned($maxColumns, $fields, $collapsible);

        print "
            </table>";

        print "</div>";

    }

    /**
     * @brief Print the Name and address and field layout for the facility
     *
     * @param $maxColumns - For colspan of field assignments table
     * @param $facility   - Facility that contains the fields
     * @param $expandContract - Expand contract java script class
     * @param $collapsible - Collapsible java script class
     */
    private function _printFacilityInfo($maxColumns, $facility, $expandContract, $collapsible) {
        $result = strpos($facility->image, 'http://');
        $image = is_bool($result) ? 'images/' . $facility->image : $facility->image;

        print "
                <tr>
                    <td align='left'colspan='$maxColumns'>
                        <font size='3'>
                            $facility->address1 $facility->address2<br>
                            $facility->city, $facility->state, $facility->postalCode<br>
                        </font>
                    </td>
                </tr>";

        if (!$facility->preApproved) {
            print "
                <tr>
                    <td align='left'colspan='$maxColumns'>
                        <font color='red' size='3'>
                        After you complete your selection below you will receive a confirmation email with additional instructions to fill out a form, pay a fee
                        and get final approval from the $facility->name field manager.
                        </font>
                    </td>
                </tr>";
        }

        print "
                <tr>
                    <td colspan='$maxColumns'>
                        <img src='$image' alt='$image' width='600' height='300'>
                    </td>
                </tr>";
    }

    /**
     * @brief Print the field assignment table for the specified field
     *
     * @param $maxColumns - For colspan of field assignments table
     * @param $fields - List of fields
     * @param $collapsible - Collapsible CSS
     */
    private function _printFieldsAssigned($maxColumns, $fields, $collapsible) {
        print "
            <tr>
                <td colspan='$maxColumns'>
                    <table valign='top' align='center' border='1' cellpadding='5' cellspacing='0'>
                        <tr>
                            <td>Available</td>
                            <td bgcolor='blue'><font color='white'>Reserved</font></td>
                            <td bgcolor='salmon'>No Permit</td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td colspan='$maxColumns'>
                <table valign='top' align='center' border='1' cellpadding='5' cellspacing='0'>
                    <thead>
                        <tr>
                            <th>Field</th>
                            <th>Time</th>";

        foreach ($this->m_days as $day=>$dayData) {
            print "
                            <th>" . $dayData[self::SHORT_NAME] . "</th>
                ";
        }

        print "
                        </tr>
                    </thead>";

        foreach ($fields as $field) {
            $reservations = $this->m_controller->getReservationsForField($field);
            $fieldAvailability = Model_Fields_FieldAvailability::LookupByFieldId($field->id, FALSE);

            if (!isset($fieldAvailability)) {
                $colSpan = count($this->m_days) + 1;
                print "
                    <tr>
                        <td align='center'>$field->name</td>
                        <td bgcolor='red' colspan='$colSpan'>Uh Oh!!!  Administrator needs to set the field availability</td>
                    </tr>";

                continue;
            }

            $times = $this->_getTimes($fieldAvailability);
            // $times = $this->m_times;
            $timeRowSpan = count($times);

            print "
                    <tr>
                        <td align='center' rowspan='$timeRowSpan'>$field->name</td>";
            $rowStarted = true;

            foreach ($times as $time=>$timeRange) {
                if (!$rowStarted) {
                    print "
                    <tr>";
                }
                print "
                        <td>$timeRange</td>";

                foreach ($this->m_days as $day => $dayData) {
                    $bgColor = $this->_getAssignmentBackgroundColor($reservations, $day, $time, $fieldAvailability);
                    print "
                        <td bgcolor='$bgColor'>&nbsp;</td>";
                }

                print "
                    </tr>";
                $rowStarted = false;
            }
        }

        print "
                </table>
                </td>
            </tr>";
    }

    /**
     * @brief Get the list of times that the field is available that can be used for selection
     *
     * @param $fieldAvailability - Model_Fields_FieldAvailability instance
     *
     * @return array() of (time=>timeRange) values.  For example: ('3:00' => '3:00 - 3:30')
     */
    private function _getTimes($fieldAvailability) {
        $times = array();
        $startTime = $fieldAvailability->startTime;
        $endTime = $fieldAvailability-> endTime;

        assertion($startTime < $endTime, "startTime: $startTime must be less than endTime: $endTime");

        $dateTime = DateTime::createFromFormat('Y-m-d H:i:s', "2015-06-01 $startTime");
        $startHour = sprintf('%d', $dateTime->format('H'));
        $startMinute = sprintf('%d', $dateTime->format('i'));

        $dateTime = DateTime::createFromFormat('Y-m-d H:i:s', "2015-06-01 $endTime");
        $endHour = sprintf('%d', $dateTime->format('H'));
        $endMinute = sprintf('%d', $dateTime->format('i'));

        $currentHour = $startHour;
        while ($currentHour <= $endHour) {
            $currentMinute = ($currentHour == $startHour ? $startMinute : 0);
            $untilMinute = ($currentHour == $endHour ? $endMinute : 60);

            while ($currentMinute < $untilMinute) {
                $time = sprintf("%d:%02d", $currentHour, $currentMinute);
                if ($currentMinute + 15 < 60) {
                    $timeRange = sprintf("%d:%02d - %d:%02d", $currentHour, $currentMinute, $currentHour, $currentMinute + 15);
                }
                else {
                    $timeRange = sprintf("%d:%02d - %d:%02d", $currentHour, $currentMinute, $currentHour + 1, 0);
                }

                $times[$time] = $timeRange;
                $currentMinute = $currentMinute + 15;
            }
            $currentHour = $currentHour + 1;
        }

        return $times;
    }

    /**
     * @brief Print drop-down field selector list
     *
     * @param $maxColumns - For colspan of field assignments table
     * @param $fields - List of fields
     * @param $collapsible - Collapsible CSS
     */
    private function _printFieldSelector($maxColumns, $fields, $collapsible) {
        $fieldSectionHTML = '';
        foreach ($fields as $field) {
            // Populate the fields drop down
            $fieldSectionHTML .= '<option value="' . $field->id . '"';
            $fieldSectionHTML .= ' ';
            $fieldSectionHTML .= '>' . $field->name . ' </option>';
        }

        print "
                <tr>
                    <td><font color='" . View_Base::AQUA . "'><b>Field:&nbsp</b></font></td>
                    <td><select name=\"fieldId\">" . $fieldSectionHTML . "</select></td>
                </tr>";
    }

    /**
     * @brief Return blue if slot is reserved; white otherwise
     *
     * @param $reservations - List of reservations for a specific field
     * @param $day - Day being checked
     * @param $time - Start time being checked
     * @param $fieldAvailability - Days/Times that the field is available
     *
     * @return blue if slot reserved; salmon if slot not available; white if slot is available
     */
    private function _getAssignmentBackgroundColor($reservations, $day, $time, $fieldAvailability) {
        // Check to see if field is available
        $dayIndex = $this->_getIndexForDay($day);
        if (!$fieldAvailability->isFieldAvailable($dayIndex)) {
            return 'salmon';
        }

        // Check to see if field is reserved
        foreach ($reservations as $index=>$reservation) {
            if ($this->_isReservationOnDay($reservation, $day)) {
                if ($this->_isReservationOnTime($reservation, $time)) {
                    return 'blue';
                }
            }
        }

        return 'white';
    }

    /**
     * @brief Return TRUE if the reservation occurs on specified day
     *
     * @param $reservation - Reservation being tested
     * @param $day - Day being checked
     *
     * @return TRUE if reservation on day; FALSE otherwise
     */
    private function _isReservationOnDay($reservation, $day) {
        $index = $this->_getIndexForDay($day);
        return $reservation->daysOfWeek[$index] == '1';
    }

    /**
     * @brief Return the index for the specified day
     *
     * @param $day - Day being checked
     *
     * @return Index for the specified day (NULL if day not found)
     */
    private function _getIndexForDay($day) {
        $index = 0;
        foreach ($this->m_days as $dayIndex=>$dayData) {
            if ($dayData[self::LONG_NAME] == $day) {
                return $index;
            }

            $index += 1;
        }

        return NULL;
    }

    /**
     * @brief Return TRUE if reservation overlaps with time
     *
     * @param $reservation - Reservation being checked
     * @param $time - Start time being checked (HH:MM)
     *
     * @return TRUE if time overlaps with reservation; FALSE otherwise
     */
    private function _isReservationOnTime($reservation, $time) {
        // Normalize the time to be check
        $dateTime = DateTime::createFromFormat('Y-m-d H:i:s', "2015-06-01 $time" . ":00");
        $timeToCheck = $dateTime->format('H:i:s');

        // Return TRUE if the reservation's start occurs on or before timeToCheck
        // and the reservation's end time occurs after the timeToCheck
        if ($reservation->startTime <= $timeToCheck and
            $reservation->endTime > $timeToCheck) {
            return TRUE;
        }

        return FALSE;
    }
}