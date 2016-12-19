<?php

use \DAG\Domain\Schedule\GameDate;

/**
 * @brief Show the GameDate page and get the user to select a season to administer or create a new season.
 */
class View_Schedules_GameDate extends View_Schedules_Base {
    /**
     * @brief Construct the View
     *
     * @param $controller - Controller that contains data used when rendering this view.
     */
    public function __construct($controller) {
        parent::__construct(self::SCHEDULE_GAME_DATE_PAGE, $controller);
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
                <tr bgcolor='lightyellow'>
                    <td>";

        $this->_printCreateGameDateForm($sessionId);

        print "
                    </td>
                </tr>
            </table>
            <br><br>";

        $gameDates = [];
        if (isset($this->m_controller->m_season)) {
            $gameDates = GameDate::lookupBySeason($this->m_controller->m_season);
        }

        print "
            <table valign='top' align='center' width='400' border='1' cellpadding='5' cellspacing='0'>
                <tr>
                    <td>";

        $this->_printUpdateGameDateForm($sessionId, $gameDates);

        print "
                    </td>
                </tr>
            </table>";
    }

    /**
     * @brief Print the form to create a gameDate.  Form includes the following
     *        - GameDate Attributes
     *
     * @param $sessionId
     */
    private function _printCreateGameDateForm($sessionId) {
        // Print the start of the form to select a gameDate
        print "
            <table valign='top' align='center' border='0' cellpadding='5' cellspacing='0'>
                <tr>
                    <th align='center'>Create New GameDate</th>
                </tr>
            <form method='post' action='" . self::SCHEDULE_GAME_DATE_PAGE . $this->m_urlParams . "'>";

        $this->displayInput('GameDate Day:', 'text', View_Base::DAY, 'YYYY-MM-DD', '');

        // Print Delete button and end form
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
     *        - GameDate Attributes
     *
     * @param $sessionId
     * @param $gameDates - GameDates to be updated
     */
    private function _printUpdateGameDateForm($sessionId, $gameDates) {
        // Print the start of the form to select a gameDate
        print "
            <table valign='top' align='center' border='0' cellpadding='5' cellspacing='0'>
                <tr>
                    <th align='center'>Day</th>
                    <th align='center'>&nbsp</th>
                </tr>";

        foreach ($gameDates as $gameDate) {
            print "
                <tr>
                <form method='post' action='" . self::SCHEDULE_GAME_DATE_PAGE . $this->m_urlParams . "'>
                    <td>$gameDate->day</td>
                    <td align='left'>
                        <input style='background-color: lightgreen' name='" . View_Base::SUBMIT . "' type='submit' value='" . View_Base::DELETE . "'>
                        <input type='hidden' id='gameDateId' name='gameDateId' value='$gameDate->id'>
                        <input type='hidden' id='sessionId' name='sessionId' value='$sessionId'>
                    </td>
                </form>
                </tr>";
        }

        print "
            </table>";
    }
}