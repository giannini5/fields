<?php

/**
 * @brief Show the Admin Season page and get the user to select a season to administer or create a new season.
 */
class View_AdminPractice_Season extends View_AdminPractice_Base {
    /**
     * @brief Construct the View
     *
     * @param $controller - Controller that contains data used when rendering this view.
     */
    public function __construct($controller) {
        parent::__construct(self::ADMIN_SEASON_PAGE, $controller);
    }

    /**
     * @brief Render data for display on the page.
     */
    public function render()
    {
        $maxColumns = 4;

        print "
            <table bgcolor='lightyellow' valign='top' align='center' width='400' border='1' cellpadding='5' cellspacing='0'>
                <tr>
                    <td>";

        $this->_printCreateSeasonForm($maxColumns);

        print "
                    </td>
                </tr>
            </table>
            <br><br>";

        foreach ($this->m_controller->m_seasons as $season) {
            print "
            <table valign='top' align='center' width='400' border='1' cellpadding='5' cellspacing='0'>
                <tr>
                    <td>";

            $this->_printUpdateSeasonForm($maxColumns, $season);

            print "
                    </td>
                </tr>
            </table>
            <br><br>";
        }
    }

    /**
     * @brief Print the form to create a season.  Form includes the following
     *        - Season Name
     *        - Enabled radio button
     *
     * @param $maxColumns - Number of columns the form is covering
     */
    private function _printCreateSeasonForm($maxColumns) {
        $sessionId = $this->m_controller->getSessionId();

        // Print the start of the form to select a facility
        print "
            <table valign='top' align='center' border='0' cellpadding='5' cellspacing='0'>
            <form method='post' action='" . self::ADMIN_SEASON_PAGE . $this->m_urlParams . "'>";

        $errorString = (isset($this->m_controller->m_seasonId) or $this->m_controller->m_missingAttributes == 0) ? '' : $this->m_controller->m_name;

        $this->displayInput('Season Name:', 'text', Model_Fields_SeasonDB::DB_COLUMN_NAME, 'Season Name', $errorString);
        $this->displayCalendarDateSelector($maxColumns, View_Base::BEGIN_RESERVATION_DATE, 'Reservation Start', '2016-07-15');
        $this->displayCalendarSelector($maxColumns, '2015-01-01', '2016-01-01');
        $this->printTimeSelectors($maxColumns, '03:30:00', '07:00:00');
        $this->printDaySelector($maxColumns, NULL);
        $this->displayRadioSelector('Create/Login Allowed:', Model_Fields_SeasonDB::DB_COLUMN_CREATE_ALLOWED, array(0=>'No', 1=>'Yes'), 'No');
        $this->displayRadioSelector('Enabled:', Model_Fields_SeasonDB::DB_COLUMN_ENABLED, array(0=>'No', 1=>'Yes'), 'Yes');

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
     *        - Season Name
     *        - Enabled radio button
     *
     * @param $maxColumns - Number of columns the form is covering
     * @param $season - Season to be edited
     */
    private function _printUpdateSeasonForm($maxColumns, $season) {
        $sessionId = $this->m_controller->getSessionId();
        $errorString = ($this->m_controller->m_seasonId == $season->id and $this->m_controller->m_missingAttributes > 0) ? $this->m_controller->m_name : '';

        // Print the start of the form to select a facility
        $bgColor = $season->enabled ? "bgcolor='lightblue'" : "";
        print "
            <table $bgColor valign='top' align='center' border='0' cellpadding='5' cellspacing='0'>
            <form method='post' action='" . self::ADMIN_SEASON_PAGE . $this->m_urlParams . "'>";

        $this->displayInput('Season Name:', 'text', Model_Fields_SeasonDB::DB_COLUMN_NAME, 'Season Name', $errorString, $season->name);
        $this->displayCalendarDateSelector($maxColumns, View_Base::BEGIN_RESERVATION_DATE, 'Reservation Start', $season->beginReservationsDate);
        $this->displayCalendarSelector($maxColumns, $season->startDate, $season->endDate);
        $this->printTimeSelectors($maxColumns, $season->startTime, $season->endTime);
        $this->printDaySelector($maxColumns, NULL, $season->daysOfWeek);
        $this->displayRadioSelector('Create/Login Allowed:', Model_Fields_SeasonDB::DB_COLUMN_CREATE_ALLOWED, array(0=>'No', 1=>'Yes'), $season->createAllowed ? 'Yes' : 'No');
        $this->displayRadioSelector('Enabled:', Model_Fields_SeasonDB::DB_COLUMN_ENABLED, array(0=>'No', 1=>'Yes'), $season->enabled ? 'Yes' : 'No');

        // Print Submit button and end form
        print "
                <tr>
                    <td align='left'>
                        <input style='background-color: lightgreen' name='" . View_Base::SUBMIT . "' type='submit' value='" . View_Base::UPDATE . "'>
                        <input type='hidden' id='seasonId' name='seasonId' value='$season->id'>
                        <input type='hidden' id='sessionId' name='sessionId' value='$sessionId'>
                    </td>
                </tr>
            </form>
            </table>";
    }
}