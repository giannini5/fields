<?php

use \DAG\Domain\Schedule\GameDate;

/**
 * @brief Show the GameDate page and get the user to select a season to administer or create a new season.
 */
class View_AdminSchedules_GameDate extends View_AdminSchedules_Base {
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
                <tr>
                    <td bgcolor='" . View_Base::CREATE_COLOR  . "'>";

        $this->_printCreateGameDateForm($sessionId);


        print "
                    </td>
                    <td bgcolor='" . View_Base::DELETE_COLOR  . "'>";

        $this->_printDeleteGameDates($sessionId);

        print "
                    </td>
                    <td bgcolor='" . View_Base::DELETE_COLOR  . "'>";

        $this->_printRemoveGameDatesByDivision($sessionId);

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

        $this->_printGameDates($gameDates);

        print "
                    </td>
                </tr>
            </table>";
    }

    /**
     * @brief Print the form to create a gameDate.  Form includes the following
     *        - GameDate Attributes
     *
     * @param int   $sessionId
     */
    private function _printCreateGameDateForm($sessionId) {
        // Print the start of the form to select a gameDate
        print "
            <table valign='top' align='center' border='0' cellpadding='5' cellspacing='0'>
                <tr>
                    <th align='center' colspan='2'>Create New GameDate</th>
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
     * @brief Print the form to delete the selected game dates
     *
     * @param int   $sessionId
     */
    private function _printDeleteGameDates($sessionId)
    {
        $gameDateSelector   = $this->getGameDateSelector();

        print "
            <table valign='top' align='center' border='0' cellpadding='5' cellspacing='0'>
                <tr bgcolor='" . View_Base::DELETE_COLOR  . "'>
                    <th align='center' colspan='1' nowrap>Delete Game Dates</th>
                </tr>
            <form method='post' action='" . self::SCHEDULE_GAME_DATE_PAGE . $this->m_urlParams . "'>";

        $this->displayMultiSelector('', View_Base::GAME_DATES, '', $gameDateSelector, count($gameDateSelector));

        // Print Delete button and end form
        print "
                <tr>
                    <td align='left'>
                        <input style='background-color: salmon' name='" . View_Base::SUBMIT . "' type='submit' value='" . View_Base::DELETE . "'>
                        <input type='hidden' id='sessionId' name='sessionId' value='$sessionId'>
                    </td>
                </tr>
            </form>
            </table>";
    }

    /**
     * @brief Print the form to remove game slots by selected division
     *
     * @param int   $sessionId
     */
    private function _printRemoveGameDatesByDivision($sessionId)
    {
        $divisionsSelector  = $this->getDivisionsSelector(true);
        $gameDateSelector   = $this->getGameDateSelector();

        print "
            <table valign='top' align='center' border='0' cellpadding='5' cellspacing='0'>
                <tr bgcolor='" . View_Base::DELETE_COLOR  . "'>
                    <th align='center' colspan='2' nowrap>Remove Game Dates for Specified Division(s)</th>
                </tr>
            <form method='post' action='" . self::SCHEDULE_GAME_DATE_PAGE . $this->m_urlParams . "'>";

        $this->displayMultiSelector('Divisions', View_Base::DIVISION_NAMES, '', $divisionsSelector, count($divisionsSelector));
        $this->displaySelector('Game Date:', View_Base::GAME_DATE, '', $gameDateSelector, '');

        // Print Delete button and end form
        print "
                <tr>
                    <td align='left'>
                        <input style='background-color: salmon' name='" . View_Base::SUBMIT . "' type='submit' value='" . View_Base::REMOVE . "'>
                        <input type='hidden' id='sessionId' name='sessionId' value='$sessionId'>
                    </td>
                </tr>
            </form>
            </table>";
    }

    /**
     * @brief Display the game dates
     *
     * @param $gameDates - GameDates to be displayed
     */
    private function _printGameDates($gameDates) {
        // Print the start of the form to select a gameDate
        print "
            <table valign='top' align='center' border='0' cellpadding='5' cellspacing='0'>
                <tr>
                    <th align='center'>Day</th>
                </tr>";

        foreach ($gameDates as $gameDate) {
            print "
                <tr>
                    <td>$gameDate->day</td>
                </tr>";
        }

        print "
            </table>";
    }
}