<?php

/**
 * @brief Show the Team Rank page
 */
class View_AdminScoring_TeamRank extends View_AdminScoring_Base
{
    /**
     * @brief Construct the View
     *
     * @param Controller_Base $controller - Controller that contains data used when rendering this view.
     */
    public function __construct($controller)
    {
        parent::__construct(self::SCORING_TEAM_RANK_PAGE, $controller);
    }

    /**
     * @brief Render data for display on the page.
     */
    public function renderPage()
    {
        $sessionId          = $this->m_controller->getSessionId();

        $messageString = $this->m_controller->m_messageString;
        if (!empty($messageString)) {
            print "
                <p style='color: green' align='center'><strong>$messageString</strong></p><br>";
        }

        $this->renderLoadTeamRanksFromFile();
    }

    public function renderLoadTeamRanksFromFile()
    {
        $sessionId = $this->m_controller->getSessionId();

        print "
            <table bgcolor='lightyellow' valign='top' style='margin-left:25px' width='700' border='1' cellpadding='5' cellspacing='0'>
                <tr>
                    <td colspan='3' nowrap><strong style='color: blue; font-size: 18px'>Update Team Ranks</strong><br>
                    <strong style='font-size: 16px'>Sample CSV file format</strong><br>
                    <p style='font-size: 12px'>
                        teamUUID,rank<br>
                        0007533E-F36B-1410-8752-00FFFFFFFFFF,1000<br>
                        0032513E-F36B-1410-8752-00FFFFFFFFFF,998<br>
                        </p>
                    </td>
                </tr>
                <tr>
                    <form enctype='multipart/form-data' method='POST' action='" . self::SCORING_TEAM_RANK_PAGE . $this->m_urlParams . "'>
                        <td nowrap>Select csv file to upload:</td>
                        <td>
                            <input type='file' name='fileToUpload' id='fileToUpload'>
                        </td>
                        <td>
                            <input style='background-color: yellow' type='" . View_Base::SUBMIT . "' value='" . View_Base::UPLOAD_FILE . "' name='" . View_Base::SUBMIT . "'>
                            <input type='hidden' id='sessionId' name='sessionId' value='$sessionId'>
                        </td>
                    </form>
                </tr>
            </table>
        ";
    }
}