<?php

/**
 * @brief Show the Admin Field page and get the user to select a field to administer or create a new field.
 */
class View_AdminPractice_Field extends View_AdminPractice_Base {
    /**
     * @brief Construct the View
     *
     * @param Controller_Base $controller - Controller that contains data used when rendering this view.
     */

    private $facilityAnchorId = 0;

    public function __construct($controller) {
        parent::__construct(self::ADMIN_FIELD_PAGE, $controller);
    }

    /**
     * @brief Render data for display on the page.
     */
    public function render()
    {
        $messageString = $this->m_controller->m_messageString;
        if (!empty($messageString)) {
            print "
                <p style='color: green' align='center'><strong>$messageString</strong></p><br>";
        }

        $facilities = Model_Fields_Facility::LookupByLeague($this->m_controller->m_league);
        foreach ($facilities as $facility) {
            $this->facilityAnchorId += 1;
            $this->_printCreateFieldForm($facility);

            $fields = Model_Fields_Field::LookupByFacility($facility);
            $this->_printUpdateFieldsForm($facility, $fields);

            print "
            <br><br>";
        }
    }

    /**
     * @brief Print the form to create a field.  Form includes the following
     *        - Facility
     *        - Field Name
     *        - Enabled radio button
     *
     * @param Facility  - Facility that contains fields
     */
    private function _printCreateFieldForm($facility) {
        $sessionId      = $this->m_controller->getSessionId();
        $divisionData   = $this->_getDivisionData();

        print "
            <form method='post' action='" . self::ADMIN_FIELD_PAGE . $this->m_urlParams . "#$this->facilityAnchorId'>
            <table valign='top' align='center' border='1' cellpadding='5' cellspacing='0' bgcolor='lightyellow'>
                <tr>
                    <th colspan='8' align='center'><a name='$this->facilityAnchorId'>$facility->name</a></th>
                </tr>";

        if ($this->m_controller->m_facilityName == $facility->name) {
            $errorString    = $this->m_controller->m_errorString;
            $messageString  = $this->m_controller->m_messageString;

            if (!empty($errorString)) {
                print "
                <tr>
                    <th colspan='8' align='center' style='color: red'><strong>$errorString</strong></th>
                </tr>";
            }
            else if (!empty($messageString)) {
                print "
                <tr>
                    <th colspan='8' align='center' style='color: green'><strong>$messageString</strong></th>
                </tr>";
            }
        }

        print "
                <tr>
                    <th>Field Name</th>
                    <th>Divisions</th>
                    <th>From</th>
                    <th>To</th>
                    <th>Start Time</th>
                    <th>End time</th>
                    <th>Days</th>
                    <th>Enabled</th>
                </tr>
                <tr>";

            $this->displayInput('', 'text', Model_Fields_FieldDB::DB_COLUMN_NAME, 'Field Name', '', '', null, 1, false, 70, false);
            $this->displayMultiSelector('', View_Base::DIVISION_IDS, [], $divisionData, 10, null, 1, false);
            $this->displayCalendarDateSelector(1, View_Base::START_DATE, '', '', null, 1, 70, false);
            $this->displayCalendarDateSelector(1, View_Base::END_DATE, '', '', null, 1, 70, false);
            $this->printPracticeTimeSelector('', View_Base::START_TIME, '', false);
            $this->printPracticeTimeSelector('', View_Base::END_TIME, '', false);
            $this->printDaySelector(1, null, '', '', false, false);
            $this->displayRadioSelector('', Model_Fields_FieldDB::DB_COLUMN_ENABLED, array(0 => 'No', 1 => 'Yes'), '', null, 1, false);

        print "
                </tr>
                <tr>
                    <td align='left' colspan='8'>
                        <input style='background-color: yellow' name='" . View_Base::SUBMIT . "' type='submit' value='" . View_Base::CREATE . "'>
                        <input type='hidden' id='facilityId' name='facilityId' value='$facility->id'>
                        <input type='hidden' id='sessionId' name='sessionId' value='$sessionId'>
                    </td>
                </tr>
            </table>
            </form>";
    }

    /**
     * @brief Print the form to update a field for a facility.  Form includes the following
     *        - Facility
     *        - Fields
     *
     * @param Model_Fields_Facility $facility - Facility that contains fields
     * @param Model_Fields_Field[]  $fields   - Fields to be updated
     */
    private function _printUpdateFieldsForm($facility, $fields) {
        $sessionId      = $this->m_controller->getSessionId();
        $divisionData   = $this->_getDivisionData();

        print "
            <form method='post' action='" . self::ADMIN_FIELD_PAGE . $this->m_urlParams . "#$this->facilityAnchorId'>
            <table valign='top' align='center' border='1' cellpadding='5' cellspacing='0'>
                <tr>
                    <th>Field Name</th>
                    <th>Divisions</th>
                    <th>From</th>
                    <th>To</th>
                    <th>Start Time</th>
                    <th>End time</th>
                    <th>Days</th>
                    <th>Enabled</th>
                </tr>";

        // Print a form for each facility that can be edited
        foreach ($fields as $field) {
            $currentDivisions   = $this->_getDivisionDataForFacilityField($facility->id, $field->id);
            $fieldAvailability  = Model_Fields_FieldAvailability::LookupByFieldId($field->id, FALSE);
            $startDate          = isset($fieldAvailability) ? $fieldAvailability->startDate : '';
            $endDate            = isset($fieldAvailability) ? $fieldAvailability->endDate : '';
            $startTime          = isset($fieldAvailability) ? $fieldAvailability->startTime : '';
            $endTime            = isset($fieldAvailability) ? $fieldAvailability->endTime : '';

            print "
                <tr>";

            $name = View_Base::FIELD_UPDATE_DATA . "[$field->id][" . Model_Fields_FieldDB::DB_COLUMN_NAME . "]";
            $this->displayInput('', 'text', $name, 'Field Name', '', $field->name, null, 1, false, 70, false);

            $name = View_Base::FIELD_UPDATE_DATA . "[$field->id][" . View_Base::DIVISION_IDS . "]";
            $this->displayMultiSelector('', $name, $currentDivisions, $divisionData, 10, null, 1, false);

            $name = View_Base::FIELD_UPDATE_DATA . "[$field->id][" . View_Base::START_DATE . "]";
            $this->displayCalendarDateSelector(1, $name, '', $startDate, null, 1, 70, false);

            $name = View_Base::FIELD_UPDATE_DATA . "[$field->id][" . View_Base::END_DATE . "]";
            $this->displayCalendarDateSelector(1, $name, '', $endDate, null, 1, 70, false);

            $name = View_Base::FIELD_UPDATE_DATA . "[$field->id][" . View_Base::START_TIME . "]";
            $this->printPracticeTimeSelector($startTime, $name, '', false);

            $name = View_Base::FIELD_UPDATE_DATA . "[$field->id][" . View_Base::END_TIME . "]";
            $this->printPracticeTimeSelector($endTime, $name, '', false);

            $name = View_Base::FIELD_UPDATE_DATA . "[$field->id]";
            $this->printDaySelector(1, null, $fieldAvailability->daysOfWeek, '', false, false, 7, 1, 1, $name);

            $name = View_Base::FIELD_UPDATE_DATA . "[$field->id][" . Model_Fields_FieldDB::DB_COLUMN_ENABLED . "]";
            $this->displayRadioSelector('', $name, array(0 => 'No', 1 => 'Yes'), $field->enabled ? 'Yes' : 'No', null, 1, false);

            print "
                </tr>";
        }

        // Print Submit button and end form
        print "
                <tr>
                    <td align='left' colspan='8'>
                        <input style='background-color: lightgreen' name='" . View_Base::SUBMIT . "' type='submit' value='" . View_Base::UPDATE . "'>
                        <input type='hidden' id='facilityId' name='facilityId' value='$facility->id'>
                        <input type='hidden' id='sessionId' name='sessionId' value='$sessionId'>
                    </td>
                </tr>
            </table>
            </form>";
    }

    /**
     * @brief Get division data that can be used in a selector drop down
     *
     * @return [] - [divisionId=>divisionName]
     */
    private function _getDivisionData() {
        $divisions      = Model_Fields_Division::GitList($this->m_controller->m_league);
        $divisionData   = [];

        foreach ($divisions as $division) {
            $divisionData[$division->id] = $division->name;
        }

        return $divisionData;
    }

    /**
     * @brief Get division data that should be pre-selected in a selector drop down
     *
     * @param int $facilityId   - Model_Field_Facility identifier
     * @param int $fieldId      - Model_Field_Field identifier
     *
     * @return Model_Fields_DivisionField[]
     */
    private function _getDivisionDataForFacilityField($facilityId, $fieldId) {
        $divisionFields     = Model_Fields_DivisionField::GetFacilityFieldDivisions($facilityId, $fieldId);
        $divisionFieldData  = [];

        foreach ($divisionFields as $divisionField) {
            $divisionFieldData[] = $divisionField->id;
        }

        return $divisionFieldData;
    }
}