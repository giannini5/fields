<?php

/**
 * @brief Referee admin home page
 */
class View_AdminReferee_Home extends View_AdminReferee_Base {
    /** @var  Controller_AdminReferee_Base */
    protected $m_controller;

    /**
     * @brief Construct he View
     *
     * @param Controller_AdminReferee_Base $controller
     */
    public function __construct($controller) {
        $this->m_controller = $controller;
        parent::__construct(self::REFEREE_HOME_PAGE, $controller);
    }

    /**
     * @brief Render data for display on the page.
     */
    public function render()
    {
        if ($this->m_controller->m_isAuthenticated) {
            $this->renderHome();
        } else {
            $this->renderLogin();
        }
    }

    /**
     * @brief Render instructions for how to administer practice fields
     */
    public function renderHome() {
        $messageString  = $this->m_controller->m_messageString;

        if (!empty($messageString)) {
            print "
                <p style='color: green' align='center'><strong>$messageString</strong></p><br>";
        }

        $this->renderLoadRefereesFromFile();
        print "<br>";
        $this->renderLoadRefereesByTeamFromFile();
        print "<br>";
        $this->renderGenerateRefereeCrews();
    }

    /**
     * @brief Render HTML to load referees
     */
    public function renderLoadRefereesFromFile()
    {
        $sessionId = $this->m_controller->getSessionId();

        print "
            <table bgcolor='lightyellow' valign='top' style='margin-left:25px' width='700' border='1' cellpadding='5' cellspacing='0'>
                <tr>
                    <td colspan='3' nowrap ><strong style='color: blue; font-size: 18px'>Referees</strong><br><strong style='font-size: 16px'>Sample CSV file format</strong><br>
                    <p style='font-size: 12px'>
                        Approved,Last Seen,eAYSO Vol App,AYSO ID,Name,Years,Games,Badge,Phone,Email<br>
                        Y,2018,,58017703,Cornelia Alsheimer-Barthel,12,96,R,805-455-0119,cornelia_alsheimer@hotmail.com
                    </p>
                    </td>
                </tr>
                <tr>
                    <form enctype='multipart/form-data' method='POST' action='" . self::REFEREE_HOME_PAGE . $this->m_urlParams . "'>
                        <td nowrap>Select csv file to upload:</td>
                        <td>
                            <input type='file' name='fileToUpload' id='fileToUpload'>
                        </td>
                        <td>
                            <input style='background-color: yellow' type='" . View_Base::SUBMIT . "' value='" . View_Base::UPLOAD_REFEREE_FILE . "' name='" . View_Base::SUBMIT . "'>
                            <input type='hidden' id='sessionId' name='sessionId' value='$sessionId'>
                        </td>
                    </form>
                </tr>
            </table>
            ";
    }

    /**
     * @brief Render HTML to load referees
     */
    public function renderLoadRefereesByTeamFromFile()
    {
        $sessionId = $this->m_controller->getSessionId();

        print "
            <table bgcolor='lightyellow' valign='top' style='margin-left:25px' width='700' border='1' cellpadding='5' cellspacing='0'>
                <tr>
                    <td colspan='3' nowrap ><strong style='color: blue; font-size: 18px'>Referees By Team</strong><br><strong style='font-size: 16px'>Sample CSV file format</strong><br>
                    <p style='font-size: 12px'>
                        Division,Team#,TeamID,Coach,Referee1,Referee2,Referee3,Referee4,Referee5,Referee6,Referee7,Referee8,Referee9,Referee10<br>
                        B10U,1,B10U-01,Paul Atzberger,,,,,,,,,,<br>
                        B10U,2,B10U-02,Peter Benelli,Ivan Lorkovic,,,,,,,,,<br>
                        B10U,3,B10U-03,Dan Brennan,Ashutosh Chitnis,Bryan Bottorff,Brandon Smith,Jon Ohlgren,Josh Brennan,Dan Brennan,,,,<br>
                    </p>
                    </td>
                </tr>
                <tr>
                    <form enctype='multipart/form-data' method='POST' action='" . self::REFEREE_HOME_PAGE . $this->m_urlParams . "'>
                        <td nowrap>Select csv file to upload:</td>
                        <td>
                            <input type='file' name='fileToUpload' id='fileToUpload'>
                        </td>
                        <td>
                            <input style='background-color: yellow' type='" . View_Base::SUBMIT . "' value='" . View_Base::UPLOAD_REFBYTEAM_FILE . "' name='" . View_Base::SUBMIT . "'>
                            <input type='hidden' id='sessionId' name='sessionId' value='$sessionId'>
                        </td>
                    </form>
                </tr>
            </table>
            ";
    }

    /**
     * @brief Render HTML to generate referee teams
     */
    public function renderGenerateRefereeCrews()
    {
        $sessionId = $this->m_controller->getSessionId();

        print "
            <table bgcolor='lightyellow' valign='top' style='margin-left:25px' width='700' border='1' cellpadding='5' cellspacing='0'>
                <tr>
                    <td colspan='3' nowrap ><strong style='color: blue; font-size: 18px'>Generate Referee Crews</strong><br><strong style='font-size: 16px'>Notes</strong><br>
                    <p style='font-size: 12px'>
                        Referee crews consist of a center referee and two assistant referees that are allowed<br>
                        to referee for a division (10U Girs for example) and are all representing the same team<br>
                        for referee credits.  When you click the button below, existing referee teams are deleted<br>
                        and new teams are generated based on imported referees, their preferences and team affiliations.
                    </p>
                    </td>
                </tr>
                <tr>
                    <form enctype='multipart/form-data' method='POST' action='" . self::REFEREE_HOME_PAGE . $this->m_urlParams . "'>
                        <td nowrap>&nbsp</td>
                        <td nowrap>&nbsp</td>
                        <td>
                            <input style='background-color: yellow' type='" . View_Base::SUBMIT . "' value='" . View_Base::GENERATE_REF_CREWS . "' name='" . View_Base::SUBMIT . "'>
                            <input type='hidden' id='sessionId' name='sessionId' value='$sessionId'>
                        </td>
                    </form>
                </tr>
            </table>
            ";
    }

    /**
     * @brief Render sign-in screen
     */
    public function renderLogin() {
        print "
            <table align='center' valign='top' border='1' cellpadding='5' cellspacing='0'>
            <tr><td>
            <table align='center' valign='top' border='0' cellpadding='5' cellspacing='0'>";

        // Login To an Existing Account Form
        print "
            <form method='post' action='" . self::REFEREE_HOME_PAGE . $this->m_urlParams . "'>";

        print "
                <tr>
                    <td colspan='2' style='font-size:24px; color: darkblue'><b>Sign In</b></td>
                </tr>";

        $this->displayInput('Email Address:', 'text', Model_Fields_PracticeFieldCoordinatorDB::DB_COLUMN_EMAIL, 'email address', $this->m_controller->m_email);
        $this->displayInput('Password:', 'password', Model_Fields_PracticeFieldCoordinatorDB::DB_COLUMN_PASSWORD, 'password', $this->m_controller->m_password);

        print "
                <tr>
                    <td colspan='2' align='right'>
                        <input style='background-color: yellow' name='" . View_Base::SUBMIT . "' type='submit' value='" . View_Base::SUBMIT . "'>
                    </td>
                    <td>&nbsp</td>
                </tr>
            </form>";

        print "
            </table>
            </td></tr></table>";
    }
}