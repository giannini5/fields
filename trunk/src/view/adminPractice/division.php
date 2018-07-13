<?php

/**
 * @brief Show the Admin Division page and get the user to select a division to administer or create a new division.
 */
class View_AdminPractice_Division extends View_AdminPractice_Base {
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
        $messageString = $this->m_controller->m_messageString;
        if (!empty($messageString)) {
            print "
                <p style='color: green' align='center'><strong>$messageString</strong></p><br>";
        }

        $sessionId = $this->m_controller->getSessionId();

        print "
            <table bgcolor='lightyellow' valign='top' align='center' width='400' border='1' cellpadding='5' cellspacing='0'>
                <tr>
                    <td>";

        $this->_printCreateDivisionForm(4);

        print "
                    </td>
                </tr>
            </table>
            <br><br>
            <form method='post' action='" . self::ADMIN_DIVISION_PAGE . $this->m_urlParams . "'>
            <table valign='top' align='center' width='400' border='1' cellpadding='5' cellspacing='0'>
                <tr>
                    <th>Division Name</th>
                    <th>Max Minutes Per Practice</th>
                    <th>Max Minutes Per Week</th>
                    <th>Enabled</th>
                </tr>";

        $divisions = Model_Fields_Division::GitList($this->m_controller->m_league, FALSE);
        foreach ($divisions as $division) {
            $this->_printUpdateDivisionForm(4, $division);
        }

        print "
                <tr>
                    <td align='left'>
                        <input style='background-color: lightgreen' name='" . View_Base::SUBMIT . "' type='submit' value='" . View_Base::UPDATE . "'>
                        <input type='hidden' id='sessionId' name='sessionId' value='$sessionId'>
                    </td>
                </tr>
            </table>
            </form>";
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
            <table top' align='center' border='0' cellpadding='5' cellspacing='0'>
            <form method='post' action='" . self::ADMIN_DIVISION_PAGE . $this->m_urlParams . "'>";

        $errorString = (isset($this->m_controller->m_divisionId) or $this->m_controller->m_missingAttributes == 0) ? '' : $this->m_controller->m_name;

        $this->displayInput('Division Name:', 'text', Model_Fields_DivisionDB::DB_COLUMN_NAME, 'Division Name', $errorString);
        $this->displayInput('Max Minutes Per Practice:', 'number', Model_Fields_DivisionDB::DB_COLUMN_MAX_MINUTES_PER_PRACTICE, 'Max Minutes Per Practice', $errorString);
        $this->displayInput('Max Minutes Per Week:', 'number', Model_Fields_DivisionDB::DB_COLUMN_MAX_MINUTES_PER_WEEK, 'Max Minutes Per Week', $errorString);
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
        print "<tr>";

        $name = View_Base::DIVISION_UPDATE_DATA . "[$division->id][" . Model_Fields_DivisionDB::DB_COLUMN_NAME. "]";
        $this->displayInput('', 'text', $name, 'Division Name', '', $division->name, NULL, 1, false, 135, false);

        $name = View_Base::DIVISION_UPDATE_DATA . "[$division->id][" . Model_Fields_DivisionDB::DB_COLUMN_MAX_MINUTES_PER_PRACTICE. "]";
        $this->displayInput('', 'number', $name, 'Max Minutes Per Practice', '', $division->maxMinutesPerPractice, NULL, 1, false, 135, false);

        $name = View_Base::DIVISION_UPDATE_DATA . "[$division->id][" . Model_Fields_DivisionDB::DB_COLUMN_MAX_MINUTES_PER_WEEK. "]";
        $this->displayInput('', 'number', $name, 'Max Minutes Per Week', '', $division->maxMinutesPerWeek, NULL, 1, false, 135, false);

        $name = View_Base::DIVISION_UPDATE_DATA . "[$division->id][" . Model_Fields_DivisionDB::DB_COLUMN_ENABLED. "]";
        $this->displayRadioSelector('', $name, array(0=>'No', 1=>'Yes'), $division->enabled ? 'Yes' : 'No', NULL, 1, false);

        print "</tr>";
    }
}