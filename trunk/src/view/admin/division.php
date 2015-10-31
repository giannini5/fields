<?php

/**
 * @brief Show the Admin Division page and get the user to select a division to administer or create a new division.
 */
class View_Admin_Division extends View_Admin_Base {
    /**
     * @brief Construct the View
     *
     * @param $controller - Controller that contains data used when rendering this view.
     */
    public function __construct($controller) {
        parent::__construct(self::ADMIN_DIVISION_PAGE, $controller);
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

        $this->_printCreateDivisionForm(4);

        print "
                    </td>
                </tr>
            </table>
            <br><br>";

        foreach ($this->m_controller->m_divisions as $division) {
            print "
            <table valign='top' align='center' width='400' border='1' cellpadding='5' cellspacing='0'>
                <tr>
                    <td>";

            $this->_printUpdateDivisionForm(4, $division);

            print "
                    </td>
                </tr>
            </table>
            <br><br>";
        }
    }

    /**
     * @brief Print the form to create a division.  Form includes the following
     *        - Division Name
     *        - Enabled radio button
     *
     * @param $maxColumns - Number of columns the form is covering
     */
    private function _printCreateDivisionForm($maxColumns) {
        $sessionId = $this->m_controller->getSessionId();

        // Print the start of the form to select a facility
        print "
            <table valign='top' align='center' border='0' cellpadding='5' cellspacing='0'>
            <form method='post' action='" . self::ADMIN_DIVISION_PAGE . $this->m_urlParams . "'>";

        $errorString = (isset($this->m_controller->m_divisionId) or $this->m_controller->m_missingAttributes == 0) ? '' : $this->m_controller->m_name;

        $this->displayInput('Division Name:', 'text', Model_Fields_DivisionDB::DB_COLUMN_NAME, 'Division Name', $errorString);
        $this->displayRadioSelector('Enabled:', Model_Fields_DivisionDB::DB_COLUMN_ENABLED, array(0=>'No', 1=>'Yes'), 'Yes');

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
     * @brief Print the form to update a division.  Form includes the following
     *        - Division Name
     *        - Enabled radio button
     *
     * @param $maxColumns - Number of columns the form is covering
     * @param $division - Division to be edited
     */
    private function _printUpdateDivisionForm($maxColumns, $division) {
        $sessionId = $this->m_controller->getSessionId();
        $errorString = ($this->m_controller->m_divisionId == $division->id and $this->m_controller->m_missingAttributes > 0) ? $this->m_controller->m_name : '';

        // Print the start of the form to select a facility
        print "
            <table valign='top' align='center' border='0' cellpadding='5' cellspacing='0'>
            <form method='post' action='" . self::ADMIN_DIVISION_PAGE . $this->m_urlParams . "'>";

        $this->displayInput('Division Name:', 'text', Model_Fields_DivisionDB::DB_COLUMN_NAME, 'Division Name', $errorString, $division->name);
        $this->displayRadioSelector('Enabled:', Model_Fields_DivisionDB::DB_COLUMN_ENABLED, array(0=>'No', 1=>'Yes'), $division->enabled ? 'Yes' : 'No');

        // Print Submit button and end form
        print "
                <tr>
                    <td align='left'>
                        <input style='background-color: yellow' name='" . View_Base::SUBMIT . "' type='submit' value='" . View_Base::UPDATE . "'>
                        <input type='hidden' id='divisionId' name='divisionId' value='$division->id'>
                        <input type='hidden' id='sessionId' name='sessionId' value='$sessionId'>
                    </td>
                </tr>
            </form>
            </table>";
    }
}