<?php

use \DAG\Domain\Schedule\Season;

/**
 * @brief Show the Season page and get the user to select a season to administer or create a new season.
 */
class View_AdminSchedules_Season extends View_AdminSchedules_Base {
    /**
     * @brief Construct the View
     *
     * @param Controller_Base   $controller - Controller that contains data used when rendering this view.
     */
    public function __construct($controller) {
        parent::__construct(self::SCHEDULE_SEASON_PAGE, $controller);
    }

    /**
     * @brief Render data for display on the page.
     */
    public function render()
    {
        $maxColumns = 4;
        $messageString  = $this->m_controller->m_messageString;
        if (!empty($messageString)) {
            print "
                <p style='color: green' align='center'><strong>$messageString</strong></p><br>";
        }

        print "
            <table bgcolor='" . View_Base::CREATE_COLOR  . "' valign='top' align='center' width='400' border='1' cellpadding='5' cellspacing='0'>
                <tr>
                    <td>";

        $this->_printCreateSeasonForm($maxColumns);

        print "
                    </td>
                </tr>
            </table>
            <br><br>";

        $seasons = Season::lookupByLeague($this->m_controller->m_league);
        foreach ($seasons as $season) {
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
     *        - First Day of Season
     *        - Last Day of Season
     *        - Start time of first game
     *        - End time of first game
     *        - Days of Week that games can be played
     *        - Enabled radio button
     *
     * @param $maxColumns - Number of columns the form is covering
     */
    private function _printCreateSeasonForm($maxColumns) {
        $sessionId = $this->m_controller->getSessionId();

        print "
            <table valign='top' align='center' border='0' cellpadding='5' cellspacing='0'>
            <form method='post' action='" . self::SCHEDULE_SEASON_PAGE . $this->m_urlParams . "'>";

        $errorString = (isset($this->m_controller->m_seasonId) or $this->m_controller->m_missingAttributes == 0) ? '' : $this->m_controller->m_name;

        $this->displayInput('Season Name:', 'text', View_Base::NAME, 'Season Name', $errorString);
        $this->displayCalendarDateSelector($maxColumns, View_Base::START_DATE, 'First Day of Season', '2017-09-09');
        $this->displayCalendarDateSelector($maxColumns, View_Base::END_DATE, 'Last Day of Season', '2017-11-12');
        $this->printGameTimeSelectors($maxColumns, '08:00:00', '17:00:00', null, 1, "First Game Of Day", "Last Game of Day");
        $this->printDaySelector($maxColumns, NULL, '0000011', "Game Days");
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
     *        - Season Name
     *        - First Day of Season
     *        - Last Day of Season
     *        - Start time of first game
     *        - End time of first game
     *        - Days of Week that games can be played
     *        - Enabled radio button
     *
     * @param $maxColumns - Number of columns the form is covering
     * @param $season - Season to be edited
     */
    private function _printUpdateSeasonForm($maxColumns, $season) {
        $sessionId = $this->m_controller->getSessionId();
        $errorString = ($this->m_controller->m_seasonId == $season->id and $this->m_controller->m_missingAttributes > 0) ? $this->m_controller->m_name : '';

        // Print the start of the form to select a facility
        $bgColor = $season->enabled ? "bgcolor='lightyellow'" : "";
        print "
            <table $bgColor valign='top' align='center' border='0' cellpadding='5' cellspacing='0'>
            <form method='post' action='" . self::SCHEDULE_SEASON_PAGE . $this->m_urlParams . "'>";

        $this->displayInput('Season Name:', 'text', View_Base::NAME, 'Season Name', $errorString, $season->name);
        $this->displayCalendarDateSelector($maxColumns, View_Base::START_DATE, 'First Day of Season', $season->startDate);
        $this->displayCalendarDateSelector($maxColumns, View_Base::END_DATE, 'Last Day of Season', $season->endDate);
        $this->printGameTimeSelectors($maxColumns, $season->startTime, $season->endTime, null, 1, "First Game Of Day", "Last Game of Day");
        $this->printDaySelector($maxColumns, NULL, $season->daysOfWeek, 'Game Days');
        $this->displayRadioSelector('Enabled:', View_Base::ENABLED, array(0=>'No', 1=>'Yes'), $season->enabled ? 'Yes' : 'No');

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