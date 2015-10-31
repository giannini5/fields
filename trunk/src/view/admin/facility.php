<?php

/**
 * @brief Show the Admin Facility page and get the user to select a facility to administer or create a new facility.
 */
class View_Admin_Facility extends View_Admin_Base {
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
        $javaScriptClassIdentifier = 1;
        $expandContract = "expandContract$javaScriptClassIdentifier";
        $collapsible = "collapsible$javaScriptClassIdentifier";
        $maxColumns = 4;

        print "
            <table valign='top' align='center' width='400' border='1' cellpadding='5' cellspacing='0'>
                <tr>
                    <td>";

        $this->_printCreateFacilityForm($maxColumns, $expandContract, $collapsible);

        print "
                    </td>
                </tr>
            </table>
            <br><br>";

        foreach ($this->m_controller->m_facilities as $facility) {
            $javaScriptClassIdentifier += 1;
            $expandContract = "expandContract$javaScriptClassIdentifier";
            $collapsible = "collapsible$javaScriptClassIdentifier";

            print "
            <table valign='top' align='center' width='400' border='1' cellpadding='5' cellspacing='0'>
                <tr>
                    <td>";

            $this->_printUpdateFacilityForm($maxColumns, $facility, $expandContract, $collapsible);

            print "
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
        // Add one to the count of facilities for the create experience
        return count($this->m_controller->m_facilities) + 1;
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
     *        - Enabled radio button
     *
     * @param $maxColumns - Number of columns the form is covering
     * @param $expandContract - ExpandContract java script class
     * @param $collapsible - Collapsible java script class
     */
    private function _printCreateFacilityForm($maxColumns, $expandContract, $collapsible) {
        $sessionId = $this->m_controller->getSessionId();
        $locationData = $this->_getLocationData();

        // Print the start of the form to select a facility
        print "
            <table valign='top' align='center' border='0' cellpadding='5' cellspacing='0'>
                <tr class='$expandContract'>
                    <th align='center'colspan='$maxColumns'>Create New Facility</th>
                </tr>
            <form method='post' action='" . self::ADMIN_FACILITY_PAGE . $this->m_urlParams . "'>";

        $errorString = (isset($this->m_controller->m_facilityId) or $this->m_controller->m_missingAttributes == 0) ? '' : $this->m_controller->m_name;

        $this->displayInput('Facility Name:', 'text', Model_Fields_FacilityDB::DB_COLUMN_NAME, 'Facility Name', $errorString, '', $collapsible);
        $this->displayInput('Facility Address1:', 'text', Model_Fields_FacilityDB::DB_COLUMN_ADDRESS1, 'Facility Address1', '', '', $collapsible);
        $this->displayInput('Facility Address2:', 'text', Model_Fields_FacilityDB::DB_COLUMN_ADDRESS2, 'Facility Address2', '', '', $collapsible);
        $this->displayInput('Facility City:', 'text', Model_Fields_FacilityDB::DB_COLUMN_CITY, 'Facility City', '', '', $collapsible);
        $this->displayInput('Facility State:', 'text', Model_Fields_FacilityDB::DB_COLUMN_STATE, 'Facility State', '', '', $collapsible);
        $this->displayInput('Facility Zip Code:', 'text', Model_Fields_FacilityDB::DB_COLUMN_POSTAL_CODE, 'Facility Zip Code', '', '', $collapsible);
        $this->displayInput('Facility Contact Name:', 'text', Model_Fields_FacilityDB::DB_COLUMN_CONTACT_NAME, 'Facility Contact Name', '', '', $collapsible);
        $this->displayInput('Facility Contact Email:', 'text', Model_Fields_FacilityDB::DB_COLUMN_CONTACT_EMAIL, 'Facility Contact Email', '', '', $collapsible);
        $this->displayInput('Facility Contact Phone:', 'text', Model_Fields_FacilityDB::DB_COLUMN_CONTACT_PHONE, 'Facility Contact Phone', '', '', $collapsible);
        $this->displayInput('Facility Image:', 'text', Model_Fields_FacilityDB::DB_COLUMN_IMAGE, 'Facility Image', '', '', $collapsible);
        $this->displayMultiSelector('Locations', 'locationIds', array(), $locationData, 8, $collapsible);
        $this->displayRadioSelector('Enabled:', Model_Fields_FacilityDB::DB_COLUMN_ENABLED, array(0=>'No', 1=>'Yes'), 'Yes', $collapsible);

        // Print Create button and end form
        print "
                <tr class='$collapsible'>
                    <td align='left'>
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
     *        - Enabled radio button
     *
     * @param $maxColumns - Number of columns the form is covering
     * @param $facility - Facility to be edited
     * @param $expandContract - ExpandContract java script class
     * @param $collapsible - Collapsible java script class
     */
    private function _printUpdateFacilityForm($maxColumns, $facility, $expandContract, $collapsible) {
        $sessionId = $this->m_controller->getSessionId();
        $locationData = $this->_getLocationData();
        $errorString = ($this->m_controller->m_facilityId == $facility->id and $this->m_controller->m_missingAttributes > 0) ? $this->m_controller->m_name : '';
        $currentLocations = $this->_getLocationDataForFacility($facility->id);

        // Print the start of the form to select a facility
        print "
            <table valign='top' align='center' border='0' cellpadding='5' cellspacing='0'>
                <tr class='$expandContract'>
                    <th align='center'colspan='$maxColumns'>$facility->name</th>
                </tr>
            <form method='post' action='" . self::ADMIN_FACILITY_PAGE . $this->m_urlParams . "'>";

        $this->displayInput('Facility Name:', 'text', Model_Fields_FacilityDB::DB_COLUMN_NAME, 'Facility Name', $errorString, $facility->name, $collapsible);
        $this->displayInput('Facility Address1:', 'text', Model_Fields_FacilityDB::DB_COLUMN_ADDRESS1, 'Facility Address1', '', $facility->address1, $collapsible);
        $this->displayInput('Facility Address2:', 'text', Model_Fields_FacilityDB::DB_COLUMN_ADDRESS2, 'Facility Address2', '', $facility->address2, $collapsible);
        $this->displayInput('Facility City:', 'text', Model_Fields_FacilityDB::DB_COLUMN_CITY, 'Facility City', '', $facility->city, $collapsible);
        $this->displayInput('Facility State:', 'text', Model_Fields_FacilityDB::DB_COLUMN_STATE, 'Facility State', '', $facility->state, $collapsible);
        $this->displayInput('Facility Zip Code:', 'text', Model_Fields_FacilityDB::DB_COLUMN_POSTAL_CODE, 'Facility Zip Code', '', $facility->postalCode, $collapsible);
        $this->displayInput('Facility Contact Name:', 'text', Model_Fields_FacilityDB::DB_COLUMN_CONTACT_NAME, 'Facility Contact Name', '', $facility->contactName, $collapsible);
        $this->displayInput('Facility Contact Email:', 'text', Model_Fields_FacilityDB::DB_COLUMN_CONTACT_EMAIL, 'Facility Contact Email', '', $facility->contactEmail, $collapsible);
        $this->displayInput('Facility Contact Phone:', 'text', Model_Fields_FacilityDB::DB_COLUMN_CONTACT_PHONE, 'Facility Contact Phone', '', $facility->contactPhone, $collapsible);
        $this->displayInput('Facility Image:', 'text', Model_Fields_FacilityDB::DB_COLUMN_IMAGE, 'Facility Image', '', $facility->image, $collapsible);
        $this->displayMultiSelector('Locations', 'locationIds', $currentLocations, $locationData, 8, $collapsible);
        $this->displayRadioSelector('Enabled:', Model_Fields_FacilityDB::DB_COLUMN_ENABLED, array(0=>'No', 1=>'Yes'), $facility->enabled ? 'Yes' : 'No', $collapsible);

        // Print Submit button and end form
        print "
                <tr class='$collapsible'>
                    <td align='left'>
                        <input style='background-color: yellow' name='" . View_Base::SUBMIT . "' type='submit' value='" . View_Base::UPDATE . "'>
                        <input type='hidden' id='facilityId' name='facilityId' value='$facility->id'>
                        <input type='hidden' id='sessionId' name='sessionId' value='$sessionId'>
                    </td>
                </tr>
            </form>
            </table>";
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