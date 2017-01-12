<?php

use \DAG\Domain\Schedule\Facility;

/**
 * @brief Show the Facility page and get the user to select a season to administer or create a new season.
 */
class View_AdminSchedules_Facility extends View_AdminSchedules_Base {
    /**
     * @brief Construct the View
     *
     * @param $controller - Controller that contains data used when rendering this view.
     */
    public function __construct($controller) {
        parent::__construct(self::SCHEDULE_FACILITIES_PAGE, $controller);
    }

    /**
     * @brief Render data for display on the page.
     */
    public function render()
    {
        $sessionId      = $this->m_controller->getSessionId();
        $messageString  = $this->m_controller->m_messageString;

        if (!empty($messageString)) {
            print "
                <p style='color: green' align='center'><strong>$messageString</strong></p><br>";
        }

        print "
            <table valign='top' align='center' width='400' border='1' cellpadding='5' cellspacing='0'>
                <tr bgcolor='" . View_Base::CREATE_COLOR  . "'>
                    <td>";

        $this->_printCreateFacilityForm($sessionId);

        print "
                    </td>
                </tr>
            </table>
            <br><br>";

        $facilities = [];
        if (isset($this->m_controller->m_season)) {
            $facilities = Facility::lookupBySeason($this->m_controller->m_season);
        }

        foreach ($facilities as $facility) {
            print "
            <table valign='top' align='center' width='400' border='1' cellpadding='5' cellspacing='0'>
                <tr>
                    <td>";

            $this->_printUpdateFacilityForm($sessionId, $facility);

            print "
                    </td>
                </tr>
            </table>
            <br><br>";
        }
    }

    /**
     * @brief Print the form to create a facility.  Form includes the following
     *        - Facility Attributes
     *        - Enabled radio button
     *
     * @param $sessionId
     */
    private function _printCreateFacilityForm($sessionId) {
        // Print the start of the form to select a facility
        print "
            <table valign='top' align='center' border='0' cellpadding='5' cellspacing='0'>
                <tr>
                    <th align='center'>Create New Facility</th>
                </tr>
            <form method='post' action='" . self::SCHEDULE_FACILITIES_PAGE . $this->m_urlParams . "'>";

        $this->displayInput('Facility Name:', 'text', View_Base::NAME, 'Facility Name', '');
        $this->displayInput('Facility Address1:', 'text', View_Base::ADDRESS1, 'Facility Address1', '');
        $this->displayInput('Facility Address2:', 'text', View_Base::ADDRESS2, 'Facility Address2', '');
        $this->displayInput('Facility City:', 'text', View_Base::CITY, 'Facility City', '');
        $this->displayInput('Facility State:', 'text', View_Base::STATE, 'Facility State', '');
        $this->displayInput('Facility Zip Code:', 'text', View_Base::POSTAL_CODE, 'Facility Zip Code', '');
        $this->displayInput('Facility Contact Name:', 'text', View_Base::CONTACT_NAME, 'Facility Contact Name', '');
        $this->displayInput('Facility Contact Email:', 'text', View_Base::CONTACT_EMAIL, 'Facility Contact Email', '');
        $this->displayInput('Facility Contact Phone:', 'text', View_Base::CONTACT_PHONE, 'Facility Contact Phone', '');
        $this->displayInput('Facility Image:', 'text', View_Base::IMAGE, 'Facility Image', '');
        $this->displayRadioSelector('Enabled:', View_Base::ENABLED, array(0=>'No', 1=>'Yes'), 'Yes');

        // Print Create button and end form
        print "
                <tr>
                    <td align='left'>
                        <input style='background-color: yellow' name='" . View_Base::SUBMIT . "' type='submit' value='" . View_Base::CREATE . "'>
                        <input type='hidden' id='sessionId' name='sessionId' value='$sessionId'>
                    </td>
                </tr>
            </form>
            </table>";
    }

    /**
     * @brief Print the form to update a season.  Form includes the following
     *        - Facility Attributes
     *        - Enabled radio button
     *
     * @param $sessionId
     * @param $facility - Facility to be updated
     */
    private function _printUpdateFacilityForm($sessionId, $facility) {
        // Print the start of the form to select a facility
        print "
            <table valign='top' align='center' border='0' cellpadding='5' cellspacing='0'>
                <tr>
                    <th align='center'>$facility->name</th>
                </tr>
            <form method='post' action='" . self::SCHEDULE_FACILITIES_PAGE . $this->m_urlParams . "'>";

        $this->displayInput('Facility Name:', 'text', View_Base::NAME, 'Facility Name', '', $facility->name);
        $this->displayInput('Facility Address1:', 'text', View_Base::ADDRESS1, 'Facility Address1', '', $facility->address1);
        $this->displayInput('Facility Address2:', 'text', View_Base::ADDRESS2, 'Facility Address2', '', $facility->address2);
        $this->displayInput('Facility City:', 'text', View_Base::CITY, 'Facility City', '', $facility->city);
        $this->displayInput('Facility State:', 'text', View_Base::STATE, 'Facility State', '', $facility->state);
        $this->displayInput('Facility Zip Code:', 'text', View_Base::POSTAL_CODE, 'Facility Zip Code', '', $facility->postalCode);
        $this->displayInput('Facility Contact Name:', 'text', View_Base::CONTACT_NAME, 'Facility Contact Name', '', $facility->contactName);
        $this->displayInput('Facility Contact Email:', 'text', View_Base::CONTACT_EMAIL, 'Facility Contact Email', '', $facility->contactEmail);
        $this->displayInput('Facility Contact Phone:', 'text', View_Base::CONTACT_PHONE, 'Facility Contact Phone', '', $facility->contactPhone);
        $this->displayInput('Facility Image:', 'text', View_Base::IMAGE, 'Facility Image', '', $facility->image);
        $this->displayRadioSelector('Enabled:', View_Base::ENABLED, array(0=>'No', 1=>'Yes'), $facility->enabled ? 'Yes' : 'No');

        // Print Submit button and end form
        print "
                <tr>
                    <td align='left'>
                        <input style='background-color: lightgreen' name='" . View_Base::SUBMIT . "' type='submit' value='" . View_Base::UPDATE . "'>
                        <input type='hidden' id='facilityId' name='facilityId' value='$facility->id'>
                        <input type='hidden' id='sessionId' name='sessionId' value='$sessionId'>
                    </td>
                </tr>
            </form>
            </table>";
    }
}