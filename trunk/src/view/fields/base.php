<?php

/**
 * @brief: Abstract base class for all field request views.
 */
abstract class View_Fields_Base extends View_Base {

    const REQUIRE_PASSWORD = FALSE;

    /**
     * @brief: Construct a new instance of this base class.
     *
     * @param: $page - Name of the page being constructed.  Must be defined as a const
     *                 in the above list.
     * @param $controller - Controller that contains data used when rendering this view.
     */
    public function __construct($page, $controller) {
        parent::__construct($page, $controller);
    }

    /**
     * @brief: Print out HTML to display this page.  Derived classes must implement
     *         the "render()" method to print out their page content.
     */
    public function displayPage()
    {
        $sessionId = $this->m_controller->getSessionId();
        $headerButton = $this->m_controller->getHeaderButtonToShow();
        // $nextPage = $this->m_pageName;
        $nextPage = self::WELCOME_PAGE;
        $headerImage = "images/aysoLogo.jpeg";
        $coachName = $this->m_controller->getCoachName();
        $divisionName = $this->m_controller->getDivisionName();
        $gender = $this->m_controller->getGender();
        $coachInfo = $coachName != '' ? "<font color='black'>$coachName<br>Team: $divisionName-$gender</font>" : '';
        $headerTitle = "<font color='darkblue'>AYSO Region 122:<br></font>Practice Field Reservations";
        $facilityCount = count($this->m_controller->getFacilities());
        $showLoginButton = ($headerButton == self::CREATE_ACCOUNT
            and !$this->m_controller->m_isAuthenticated
            and ($this->m_pageName == self::WELCOME_PAGE or $this->m_pageName == self::SHOW_RESERVATION_PAGE)
            and $this->m_controller->m_operation != self::SIGN_IN) ? TRUE : FALSE;

        $hideButtons = $headerButton == View_Base::NO_BUTTON;

        print "
            <!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Strict//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd'>
            <html xmlns='http://www.w3.org/1999/xhtml' lang='en' xml:lang='en'>";

        print "
            <head>
                <title>Practice Fields</title>
                <script type='text/JavaScript' src='../js/scw.js'></script>";

        $this->m_styles->render($facilityCount);

        print "
            </head>

            <body bgcolor='#FFFFFF'>
                <div id='wrap'>
                <table valign='top' border='0' style='width: 100%;' cellpadding='10' cellspacing=''>
                    <tr>
                        <td width='50'><img src='$headerImage' alt='Organization Icon' width='75' height='75'></td>
                        <td align='left'><h1>$headerTitle</h1><br></td>";

        if (!$hideButtons and $showLoginButton) {
            print "
                        <form method='post' action='${nextPage}$this->m_urlParams'>
                            <td colspan='3' nowrap width='100' align='right'>
                                $coachInfo<br>
                                <input style='background-color: yellow' name=" . self::SUBMIT . " type='submit' value='" . self::SIGN_IN . "'>
                            </td>
                        </form>";
        }

        if (!$hideButtons) {
            print "
                        <form method='post' action='${nextPage}$this->m_urlParams'>
                            <td nowrap width='100' align='right'>
                                $coachInfo<br>
                                <input style='background-color: yellow' name=" . self::SUBMIT . " type='submit' value='$headerButton'>";
        }

        if (isset($sessionId) and $sessionId > 0) {
            print "
                                <input type='hidden' id='sessionId' name='sessionId' value='$sessionId'>";
        }

        print "
                            </td>
                        </form>
                    </tr>
                </table>";

        $this->displayHeaderNavigation();

        if (isset($this->m_controller->m_season)) {
            $this->render();
        } else {
            print "
            <table align='center' valign='top' border='1' cellpadding='5' cellspacing='0'>
                <tr>
                    <td>Sorry, we are in the off season right now.  Come back later.</td>
                </tr>
            </table>";
        }

        print "
                <br>";

// print $this-htmlFormatArray($_REQUEST);
// print $this->htmlFormatArray($_POST);
// print $this-htmlFormatArray($this->m_tableData);
// print $this-htmlFormatArray($this->m_tableSummaryData);

        print "
            </body>
        </html>";
    }

    /**
     * @brief Display Header Navigation (Welcome, Show Reservatio, etc.)
     */
    public function displayHeaderNavigation() {
        print '
                <ul id="nav">'
            . ($this->m_pageName == self::WELCOME_PAGE || $this->m_pageName == self::LOGIN_PAGE ?
                '<li><div>HOME</div></li>' : '<li><a href="' . self::WELCOME_PAGE . '">HOME</a></li>')
            . ($this->m_pageName == self::SHOW_RESERVATION_PAGE ?
                '<li><div>RESERVATIONS</div></li>' : '<li><a href="' . self::SHOW_RESERVATION_PAGE . '">RESERVATIONS</a></li>')
            . ($this->m_pageName == self::SELECT_FIELD_PAGE ?
                '<li><div>SELECT</div></li>' : '<li><a href="' . self::SELECT_FIELD_PAGE . '?newSelection=1">SELECT</a></li>')
            . ($this->m_pageName == self::HELP_PAGE ?
                '<li><div>HELP</div></li>' : '<li><a href="' . self::HELP_PAGE . '">HELP</a></li>')
            . '
               </ul>';
    }


    /**
     * @brief Render data for display how to authenticate instruction on the page.
     */
    public function renderAuthenticateView() {
        print "
            <table align='center' valign='top' border='1' cellpadding='5' cellspacing='0' style='max-width:900px;'>
            <tr><td>
            <table align='center' valign='top' border='0' cellpadding='5' cellspacing='0'>";

        print "
                <tr>";

        $this->renderAuthenticateInfo();

        print "
                </tr>";

        print "
            </table>
            </td></tr>
            </table>";
    }

    public function renderAuthenticateInfo() {
        print "
                <h1 align='center'>Ah, first time User - Welcome!</h1>

                <p style='text-align: left;'>Here's how to access this site so that I know your team information:</p>
                <ol>
                    <li>Go to <a href='https://r122.webyouthsoccer.com/login.php'>Region 122 WebYouthSoccer</a></li>
                    <li>Login</li>
                    <li>Click on <strong>Coach</strong><font color='red'>*</font></li>
                    <li>Select your team<font color='red'>**</font></li>
                    <li>Click on Practice Field link to get back to this site to select a practice space</li>
                </ol>
                <p style='text-align: left;'>Go to the HELP tab if you have questions.</p>
                <p style='text-align: left;'><font color='red'>*</font> You must be a coach to select a practice field<br><font color='red'>**</font> Team selection only required if you coach multiple teams</p>";
    }
}