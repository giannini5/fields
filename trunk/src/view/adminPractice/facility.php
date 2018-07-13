<?php

/**
 * @brief Show the Admin Facility page and get the user to select a facility to administer or create a new facility.
 */
class View_AdminPractice_Facility extends View_AdminPractice_Base {
    /**
     * @brief Construct the View
     *
     * @param $controller - Controller that contains data used when rendering this view.
     */
    public function __construct($controller) {
        parent::__construct(self::ADMIN_FACILITY_PAGE, $controller);
    }

    /**
     * @brief Render data for display on the page.
     */
    public function render()
    {
        $sessionId  = $this->m_controller->getSessionId();
        $facilities = Model_Fields_Facility::LookupByLeague($this->m_controller->m_league);

        $messageString = $this->m_controller->m_messageString;
        if (!empty($messageString)) {
            print "
                <p style='color: green' align='center'><strong>$messageString</strong></p><br>";
        }

        print "
            <table valign='top' align='center' width='400' border='1' cellpadding='5' cellspacing='0'>
                <tr bgcolor='lightyellow'>
                    <td>";

        $this->_printCreateFacilityForm();

        print "
                    </td>
                </tr>
            </table>
            <br><br>";

        print "
            <table valign='top' align='center' width='400' border='1' cellpadding='5' cellspacing='0'>
                <tr>
                    <td>
            <form method='post' action='" . self::ADMIN_FACILITY_PAGE . $this->m_urlParams . "'>
            <table valign='top' align='center' border='1' cellpadding='5' cellspacing='0'>
                <tr>
                    <th align='center'colspan='12'>Update Existing Facilities</th>
                </tr>
                <tr>
                    <th rowspan='2'>Name</th>
                    <th colspan='4'>Address</th>
                    <th colspan='3'>Contact</th>
                    <th rowspan='2'>Image</th>
                    <th rowspan='2' title='AYSO sign-up approves request; disable if coach needs facilities approval'>Pre-Approved</th>
                    <th rowspan='2'>Locations</th>
                    <th rowspan='2'>Enabled</th>
                </tr>
                <tr>
                    <th>Address</th>
                    <th>City</th>
                    <th>State</th>
                    <th>Zip</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                </tr>";

        foreach ($facilities as $facility) {
            $this->_printUpdateFacilityForm($facility);
        }

        // Print Submit button and end form
        print "
                <tr>
                    <td colspan='12' align='left'>
                        <input style='background-color: lightgreen' name='" . View_Base::SUBMIT . "' type='submit' value='" . View_Base::UPDATE . "'>
                        <input type='hidden' id='sessionId' name='sessionId' value='$sessionId'>
                    </td>
                </tr>
            </table>
                    </td>
                </tr>
            </form>
            </table>";
    }

    /**
     * @brief Print the form to create a facility.  Form includes the following
     *        - Facility Name
     *        - Facility Address1
     *        - Facility Address2
     *        - Facility City
     *        - Facility State
     *        - Facility PostalCode
     *        - Facility ContactName
     *        - Facility ContactEmail
     *        - Facility ContactPhone
     *        - Facility Image
     *        - Facility PreApproved
     *        - Enabled radio button
     */
    private function _printCreateFacilityForm() {
        $sessionId = $this->m_controller->getSessionId();
        $locationData = $this->_getLocationData();

        // Print the start of the form to select a facility
        print "
            <table valign='top' align='center' border='1' cellpadding='5' cellspacing='0'>
                <tr>
                    <th align='center'colspan='12'>Create New Facility</th>
                </tr>
                <tr>
                    <th rowspan='2'>Name</th>
                    <th colspan='4'>Address</th>
                    <th colspan='3'>Contact</th>
                    <th rowspan='2'>Image</th>
                    <th rowspan='2' title='AYSO sign-up approves request; disable if coach needs facilities approval'>Pre-Approved</th>
                    <th rowspan='2'>Locations</th>
                    <th rowspan='2'>Enabled</th>
                </tr>
                <tr>
                    <th>Address</th>
                    <th>City</th>
                    <th>State</th>
                    <th>Zip</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                </tr>
            <form method='post' action='" . self::ADMIN_FACILITY_PAGE . $this->m_urlParams . "'>";

        $this->displayInput('', 'text', Model_Fields_FacilityDB::DB_COLUMN_NAME, 'Facility Name', '', '', null, 1, false, 135, false);
        $this->displayInput('', 'text', Model_Fields_FacilityDB::DB_COLUMN_ADDRESS1, 'Address', '', '', null, 1, false, 135, false);
        $this->displayInput('', 'text', Model_Fields_FacilityDB::DB_COLUMN_CITY, 'City', '', '', null, 1, false, 90, false);
        $this->displayInput('', 'text', Model_Fields_FacilityDB::DB_COLUMN_STATE, 'State', '', '', null, 1, false, 40, false);
        $this->displayInput('', 'text', Model_Fields_FacilityDB::DB_COLUMN_POSTAL_CODE, 'Zip Code', '', '', null, 1, false, 40, false);
        $this->displayInput('', 'text', Model_Fields_FacilityDB::DB_COLUMN_CONTACT_NAME, 'Contact Name', '', '', null, 1, false, 135, false);
        $this->displayInput('', 'text', Model_Fields_FacilityDB::DB_COLUMN_CONTACT_EMAIL, 'Contact Email', '', '', null, 1, false, 135, false);
        $this->displayInput('', 'text', Model_Fields_FacilityDB::DB_COLUMN_CONTACT_PHONE, 'Contact Phone', '', '', null, 1, false, 135, false);
        $this->displayInput('', 'text', Model_Fields_FacilityDB::DB_COLUMN_IMAGE, 'Field Image', '', '', null, 1, false, 135, false);
        $this->displayRadioSelector('', Model_Fields_FacilityDB::DB_COLUMN_PRE_APPROVED, array(0=>'No', 1=>'Yes'), 'Yes', null, 1, false);
        $this->displayMultiSelector('', 'locationIds', array(), $locationData, 8, null, 1, false);
        $this->displayRadioSelector('', Model_Fields_FacilityDB::DB_COLUMN_ENABLED, array(0=>'No', 1=>'Yes'), 'Yes', null, 1, false);

        // Print Create button and end form
        print "
                <tr>
                    <td colspan='12' align='left'>
                        <input style='background-color: yellow' name='" . View_Base::SUBMIT . "' type='submit' value='" . View_Base::CREATE . "'>
                        <input type='hidden' id='sessionId' name='sessionId' value='$sessionId'>
                    </td>
                </tr>
            </form>
            </table>";
    }

    /**
     * @brief Print the form to update a facility.  Form includes the following
     *        - Facility Name
     *        - Facility Address1
     *        - Facility Address2
     *        - Facility City
     *        - Facility State
     *        - Facility PostalCode
     *        - Facility ContactName
     *        - Facility ContactEmail
     *        - Facility ContactPhone
     *        - Facility Image
     *        - Facility PreApproved
     *        - Enabled radio button
     *
     * @param $facility - Facility to be edited
     */
    private function _printUpdateFacilityForm($facility) {
        // Print row to update a facility
        $locationData       = $this->_getLocationData();
        $currentLocations   = $this->_getLocationDataForFacility($facility->id);

        print "
                <tr>";

        $name = View_Base::FACILITY_UPDATE_DATA . "[$facility->id][" . Model_Fields_FacilityDB::DB_COLUMN_NAME . "]";
        $this->displayInput('', 'text', $name, 'Facility Name', '', $facility->name, null, 1, false, 135, false);

        $name = View_Base::FACILITY_UPDATE_DATA . "[$facility->id][" . Model_Fields_FacilityDB::DB_COLUMN_ADDRESS1 . "]";
        $this->displayInput('', 'text', $name, 'Facility Address1', '', $facility->address1, null, 1, false, 135, false);

        $name = View_Base::FACILITY_UPDATE_DATA . "[$facility->id][" . Model_Fields_FacilityDB::DB_COLUMN_CITY . "]";
        $this->displayInput('', 'text', $name, 'Facility City', '', $facility->city, null, 1, false, 90, false);

        $name = View_Base::FACILITY_UPDATE_DATA . "[$facility->id][" . Model_Fields_FacilityDB::DB_COLUMN_STATE . "]";
        $this->displayInput('', 'text', $name, 'Facility State', '', $facility->state, null, 1, false, 40, false);

        $name = View_Base::FACILITY_UPDATE_DATA . "[$facility->id][" . Model_Fields_FacilityDB::DB_COLUMN_POSTAL_CODE . "]";
        $this->displayInput('', 'text', $name, 'Facility Zip Code', '', $facility->postalCode, null, 1, false, 40, false);

        $name = View_Base::FACILITY_UPDATE_DATA . "[$facility->id][" . Model_Fields_FacilityDB::DB_COLUMN_CONTACT_NAME . "]";
        $this->displayInput('', 'text', $name, 'Facility Contact Name', '', $facility->contactName, null, 1, false, 135, false);

        $name = View_Base::FACILITY_UPDATE_DATA . "[$facility->id][" . Model_Fields_FacilityDB::DB_COLUMN_CONTACT_EMAIL . "]";
        $this->displayInput('', 'text', $name, 'Facility Contact Email', '', $facility->contactEmail, null, 1, false, 135, false);

        $name = View_Base::FACILITY_UPDATE_DATA . "[$facility->id][" . Model_Fields_FacilityDB::DB_COLUMN_CONTACT_PHONE . "]";
        $this->displayInput('', 'text', $name, 'Facility Contact Phone', '', $facility->contactPhone, null, 1, false, 135, false);

        $name = View_Base::FACILITY_UPDATE_DATA . "[$facility->id][" . Model_Fields_FacilityDB::DB_COLUMN_IMAGE . "]";
        $this->displayInput('', 'text', $name, 'Facility Image', '', $facility->image, null, 1, false, 135, false);

        $name = View_Base::FACILITY_UPDATE_DATA . "[$facility->id][" . Model_Fields_FacilityDB::DB_COLUMN_PRE_APPROVED . "]";
        $this->displayRadioSelector('', $name, array(0=>'No', 1=>'Yes'), $facility->preApproved ? 'Yes' : 'No', null, 1, false);

        $name = View_Base::FACILITY_UPDATE_DATA . "[$facility->id][" . View_Base::LOCATION_IDS . "]";
        $this->displayMultiSelector('', $name, $currentLocations, $locationData, 8, null, 1, false);

        $name = View_Base::FACILITY_UPDATE_DATA . "[$facility->id][" . Model_Fields_FacilityDB::DB_COLUMN_ENABLED . "]";
        $this->displayRadioSelector('', $name, array(0=>'No', 1=>'Yes'), $facility->enabled ? 'Yes' : 'No', null, 1, false);

        print "
                </tr>";
    }

    /**
     * @brief Get location data that can be used in a selector drop down
     *
     * @return array of locationId=>locationName
     */
    private function _getLocationData() {
        $locations = $this->m_controller->getLocations();
        $locationData = array();

        foreach ($locations as $location) {
            $locationData[$location->id] = $location->name;
        }

        return $locationData;
    }

    /**
     * @brief Get location data that should be pre-selected in a selector drop down
     *
     * @param $facilityId - Model_Field_Facility identifier
     *
     * @return array of locationIds
     */
    private function _getLocationDataForFacility($facilityId) {
        $locations = $this->m_controller->getFacilityLocations($facilityId);
        $locationData = array();

        foreach ($locations as $location) {
            $locationData[] = $location->id;
        }

        return $locationData;
    }
}