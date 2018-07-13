<?php

/**
 * @brief Show the Admin Location page and get the user to select a location to administer or create a new location.
 */
class View_AdminPractice_Location extends View_AdminPractice_Base {
    /**
     * @brief Construct the View
     *
     * @param $controller - Controller that contains data used when rendering this view.
     */
    public function __construct($controller) {
        parent::__construct(self::ADMIN_LOCATION_PAGE, $controller);
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

        print "
            <table bgcolor='lightyellow' valign='top' align='center' width='400' border='1' cellpadding='5' cellspacing='0'>
                <tr>
                    <td>";

        $this->_printCreateLocationForm(4);

        print "
                    </td>
                </tr>
            </table>
            <br><br>";

        $sessionId = $this->m_controller->getSessionId();
        print "
        <form method='post' action='" . self::ADMIN_LOCATION_PAGE . $this->m_urlParams . "'>
        <table valign='top' align='center' width='400' border='1' cellpadding='5' cellspacing='0'>
            <tr>
                <th>Location Name</th>
            </tr>";

        $locations = Model_Fields_Location::GetLocations($this->m_controller->m_league->id);
        foreach ($locations as $location) {
            print "
            <tr>";

            $this->_printUpdateLocationForm(4, $location);

            print "
            </tr>";
        }

        print "
            <tr>
                <td align='left'>
                    <input style='background-color: lightgreen' name='" . View_Base::SUBMIT . "' type='submit' value='" . View_Base::UPDATE . "'>
                    <input type='hidden' id='sessionId' name='sessionId' value='$sessionId'>
                </td>
            </tr>
        </table>
        </form>
        <br><br>";
    }

    /**
     * @brief Print the form to create a location.  Form includes the following
     *        - Location Name
     *
     * @param $maxColumns - Number of columns the form is covering
     */
    private function _printCreateLocationForm($maxColumns) {
        $sessionId = $this->m_controller->getSessionId();

        // Print the start of the form to select a facility
        print "
            <table valign='top' align='center' border='0' cellpadding='5' cellspacing='0'>
            <form method='post' action='" . self::ADMIN_LOCATION_PAGE . $this->m_urlParams . "'>";

        $errorString = (isset($this->m_controller->m_locationId) or $this->m_controller->m_missingAttributes == 0) ? '' : $this->m_controller->m_name;

        $this->displayInput('Location Name:', 'text', Model_Fields_LocationDB::DB_COLUMN_NAME, 'Location Name', $errorString);

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
     * @brief Print the form to update a location.  Form includes the following
     *        - Location Name
     *
     * @param $maxColumns - Number of columns the form is covering
     * @param $location - Location to be edited
     */
    private function _printUpdateLocationForm($maxColumns, $location) {
        $name = View_Base::LOCATION_UPDATE_DATA . "[$location->id][" . Model_Fields_LocationDB::DB_COLUMN_NAME. "]";
        $this->displayInput('', 'text', $name, 'Location Name', '', $location->name, NULL, 1, false, 235, false);
    }
}