<?php

/**
 * @brief Show the Admin Field page and get the user to select a field to administer or create a new field.
 */
class View_Admin_Field extends View_Admin_Base {
    /**
     * @brief Construct the View
     *
     * @param $controller - Controller that contains data used when rendering this view.
     */
    public function __construct($controller) {
        parent::__construct(self::ADMIN_FIELD_PAGE, $controller);
    }

    /**
     * @brief Render data for display on the page.
     */
    public function render()
    {
        $javaScriptClassIdentifier = 0;

        foreach ($this->m_controller->m_fields as $facilityId=>$fields) {
            $javaScriptClassIdentifier += 1;
            $expandContract = "expandContract$javaScriptClassIdentifier";
            $collapsible = "collapsible$javaScriptClassIdentifier";
            $maxColumns = 4;

            print "
            <table valign='top' align='center' width='400' border='1' cellpadding='5' cellspacing='0'>
                <tr>
                    <td>";

            $facility = $this->m_controller->m_facilitiesById[$facilityId];

            $this->_printCreateFieldForm($maxColumns, $facility, $expandContract, $collapsible);
            $this->_printUpdateFieldForm($maxColumns, $facility, $fields, $collapsible);

            print "
                        </table>
                    </td>
                </tr>
            </table>
            <br><br>";
        }
    }

    /**
     * @brief Return the count of classes that need to be created to support collapsing tables.
     *
     * @return int $collapsibleCount
     */
    public function getCollapsibleCount() {
        return count($this->m_controller->m_facilities);
    }

    /**
     * @brief Print the form to create a field.  Form includes the following
     *        - Facility
     *        - Field Name
     *        - Enabled radio button
     *
     * @param $maxColumns - Number of columns the form is covering
     * @param $collapsible - Collapsible java script class
     */
    private function _printCreateFieldForm($maxColumns, $facility, $expandContract, $collapsible) {
        $sessionId = $this->m_controller->getSessionId();
        $divisionData = $this->_getDivisionData();
        $season = $this->m_controller->m_season;

        // Print the start of the form to select a facility
        print "
            <table valign='top' align='center' border='0' cellpadding='5' cellspacing='0'>
                <tr class='$expandContract'>
                    <th align='center'colspan='$maxColumns'>
                        $facility->name
                    </th>
                </tr>";

        print "
                <form method='post' action='" . self::ADMIN_FIELD_PAGE . $this->m_urlParams . "'>";

        $this->displayInput('Field Name:', 'text', Model_Fields_FieldDB::DB_COLUMN_NAME, 'Field Name new', '', '', $collapsible);
        $this->displayMultiSelector('Divisions', View_Base::DIVISION_IDS, array(), $divisionData, 10, $collapsible);
        $this->displayCalendarSelector($maxColumns, $season->startDate, $season->endDate, $collapsible);
        $this->printTimeSelectors($maxColumns, $season->startTime, $season->endTime, $collapsible);
        $this->printDaySelector($maxColumns, $collapsible);
        $this->displayRadioSelector('Enabled:', Model_Fields_FieldDB::DB_COLUMN_ENABLED, array(0=>'No', 1=>'Yes'), 'Yes', $collapsible);

        // Print Create button and end form
        print "
                    <tr class='$collapsible'>
                        <td align='left'>
                            <input style='background-color: yellow' name='" . View_Base::SUBMIT . "' type='submit' value='" . View_Base::CREATE . "'>
                            <input type='hidden' id='facilityId' name='facilityId' value='$facility->id'>
                            <input type='hidden' id='sessionId' name='sessionId' value='$sessionId'>
                        </td>
                    </tr>
                </form>
            </table>";
    }

    /**
     * @brief Print the form to update a field for a facility.  Form includes the following
     *        - Facility
     *        - Fields
     *
     * @param $maxColumns - Number of columns the form is covering
     * @param $field - Field to be edited
     * @param $collapsible - Collapsible java script class
     */
    private function _printUpdateFieldForm($maxColumns, $facility, $fields, $collapsible) {
        $sessionId = $this->m_controller->getSessionId();
        $divisionData = $this->_getDivisionData();

        // Print a form for each facility that can be edited
        foreach ($fields as $field) {
            $currentDivisions = $this->_getDivisionDataForFacilityField($facility->id, $field->id);
            $fieldAvailability = Model_Fields_FieldAvailability::LookupByFieldId($field->id, FALSE);
            $startDate = isset($fieldAvailability) ? $fieldAvailability->startDate : '';
            $endDate = isset($fieldAvailability) ? $fieldAvailability->endDate : '';
            $startTime = isset($fieldAvailability) ? $fieldAvailability->startTime : '';
            $endTime = isset($fieldAvailability) ? $fieldAvailability->endTime : '';

            print "
                    </td>
                </tr>";

            print "
                <tr class='$collapsible'>
                    <td>
                        <table valign='top' align='center' border='0' cellpadding='5' cellspacing='0'>
                            <tr class='$collapsible'>
                                <td align='left'>&nbsp;</td>
                            </tr>
                            <form method='post' action='" . self::ADMIN_FIELD_PAGE . $this->m_urlParams . "'>";

            $this->displayInput('Field Name:', 'text', Model_Fields_FieldDB::DB_COLUMN_NAME, 'Field Name', '', $field->name, $collapsible);
            $this->displayMultiSelector('Divisions', View_Base::DIVISION_IDS, $currentDivisions, $divisionData, 10, $collapsible);
            $this->displayCalendarSelector($maxColumns, $startDate, $endDate, $collapsible);
            $this->printTimeSelectors($maxColumns, $startTime, $endTime, $collapsible);
            $this->printDaySelector($maxColumns, $collapsible, $fieldAvailability->daysOfWeek);
            $this->displayRadioSelector('Enabled:', Model_Fields_FieldDB::DB_COLUMN_ENABLED, array(0=>'No', 1=>'Yes'), $field->enabled ? 'Yes' : 'No', $collapsible);

            // Print Submit button and end form
            print "
                            <tr class='$collapsible'>
                                <td align='left'>
                                    <input style='background-color: yellow' name='" . View_Base::SUBMIT . "' type='submit' value='" . View_Base::UPDATE . "'>
                                    <input type='hidden' id='facilityId' name='facilityId' value='$facility->id'>
                                    <input type='hidden' id='fieldId' name='fieldId' value='$field->id'>
                                    <input type='hidden' id='sessionId' name='sessionId' value='$sessionId'>
                                </td>";
            /*
                                <td align='left'>
                                    <input style='background-color: yellow' name='" . View_Base::SUBMIT . "' type='submit' value='" . View_Base::DELETE . "'>
                                    <input type='hidden' id='facilityId' name='facilityId' value='$facility->id'>
                                    <input type='hidden' id='fieldId' name='fieldId' value='$field->id'>
                                    <input type='hidden' id='sessionId' name='sessionId' value='$sessionId'>
                                </td>
            Delete not working so well right now because of cascading problems.
            */

            print "
                            </tr>
                            </form>
                        </table>";
        }
    }

    /**
     * @brief Get division data that can be used in a selector drop down
     *
     * @return array of divisionId=>divisionName
     */
    private function _getDivisionData() {
        $divisions = $this->m_controller->m_divisions;
        $divisionData = array();

        foreach ($divisions as $division) {
            $divisionData[$division->id] = $division->name;
        }

        return $divisionData;
    }

    /**
     * @brief Get division data that should be pre-selected in a selector drop down
     *
     * @param $facilityId - Model_Field_Facility identifier
     * @param $fieldId - Model_Field_Field identifier
     *
     * @return array of divisionIds
     */
    private function _getDivisionDataForFacilityField($facilityId, $fieldId) {
        $divisions = $this->m_controller->getFacilityFieldDivisions($facilityId, $fieldId);
        $divisionData = array();

        foreach ($divisions as $division) {
            $divisionData[] = $division->id;
        }

        return $divisionData;
    }
}