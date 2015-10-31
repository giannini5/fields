<?php

/**
 * @brief Show the Admin Location page and get the user to select a location to administer or create a new location.
 */
class View_Admin_Location extends View_Admin_Base {
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
        print "
            <table valign='top' align='center' width='400' border='1' cellpadding='5' cellspacing='0'>
                <tr>
                    <td>";

        $this->_printCreateLocationForm(4);

        print "
                    </td>
                </tr>
            </table>
            <br><br>";

        foreach ($this->m_controller->m_locations as $location) {
            print "
            <table valign='top' align='center' width='400' border='1' cellpadding='5' cellspacing='0'>
                <tr>
                    <td>";

            $this->_printUpdateLocationForm(4, $location);

            print "
                    </td>
                </tr>
            </table>
            <br><br>";
        }
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
        $sessionId = $this->m_controller->getSessionId();
        $errorString = ($this->m_controller->m_locationId == $location->id and $this->m_controller->m_missingAttributes > 0) ? $this->m_controller->m_name : '';

        // Print the start of the form to select a facility
        print "
            <table valign='top' align='center' border='0' cellpadding='5' cellspacing='0'>
            <form method='post' action='" . self::ADMIN_LOCATION_PAGE . $this->m_urlParams . "'>";

        $this->displayInput('Location Name:', 'text', Model_Fields_LocationDB::DB_COLUMN_NAME, 'Location Name', $errorString, $location->name);

        // Print Submit button and end form
        print "
                <tr>
                    <td align='left'>
                        <input style='background-color: yellow' name='" . View_Base::SUBMIT . "' type='submit' value='" . View_Base::UPDATE . "'>
                        <input type='hidden' id='locationId' name='locationId' value='$location->id'>
                        <input type='hidden' id='sessionId' name='sessionId' value='$sessionId'>
                    </td>
                </tr>
            </form>
            </table>";
    }
}