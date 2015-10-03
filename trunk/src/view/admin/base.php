<?php

/**
 * @brief: Abstract base class for all admin views.
 */
abstract class View_Admin_Base extends View_Base {

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
        $headerButton = View_Base::SIGN_OUT;
        $nextPage = $headerButton == View_Base::CREATE_ACCOUNT ? View_Base::WELCOME_PAGE : View_Base::LOGIN_PAGE;
        $headerImage = "images/aysoLogo.jpeg";

        $headerTitle = "<font color='darkblue'>AYSO Region 122:<br></font>Practice Field Reservations";

        print "
            <!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Strict//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd'>
            <html xmlns='http://www.w3.org/1999/xhtml' lang='en' xml:lang='en'>";

        $this->m_styles->render(0);

        print "
            <head>
                <title>Practice Fields</title>
                <script type='text/JavaScript' src='../js/scw.js'></script>
            </head>

            <body bgcolor='#FFFFFF'>
                <div id='wrap'>
                <table valign='top' border='0' style='width: 100%;' cellpadding='10' cellspacing=''>
                    <tr>
                        <td width='50'><img src='$headerImage' alt='Organization Icon' width='75' height='75'></td>
                        <td align='left'><h1>$headerTitle</h1><br></td>
                        <form method='post' action='${nextPage}$this->m_urlParams'>
                            <td nowrap width='100' align='left'>
                                Fill Me In Please<br>
                                <input style='background-color: yellow' name=" . self::SUBMIT . " type='submit' value='$headerButton'>";

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
        $this->render();

        print "
                <br>";

// print $this-htmlFormatArray($_REQUEST);
        print $this->htmlFormatArray($_POST);
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
            . ($this->m_pageName == self::ADMIN_HOME_PAGE || $this->m_pageName == self::ADMIN_HOME_PAGE ?
                '<li><div>HOME</div></li>' : '<li><a href="$this->m_pageName">HOME</a></li>')
            . ($this->m_pageName == self::ADMIN_SEASON_PAGE ?
                '<li><div>SEASON</div></li>' : '<li><a href="' . self::ADMIN_SEASON_PAGE . '?newSelection=1">SEASON</a></li>')
            . ($this->m_pageName == self::ADMIN_DIVISION_PAGE ?
                '<li><div>DIVISION</div></li>' : '<li><a href="' . self::ADMIN_DIVISION_PAGE . '">DIVISION</a></li>')
            . ($this->m_pageName == self::ADMIN_LOCATION_PAGE ?
                '<li><div>DIVISION</div></li>' : '<li><a href="' . self::ADMIN_LOCATION_PAGE . '">DIVISION</a></li>')
            . ($this->m_pageName == self::ADMIN_FACILITY_PAGE ?
                '<li><div>DIVISION</div></li>' : '<li><a href="' . self::ADMIN_FACILITY_PAGE . '">DIVISION</a></li>')
            . ($this->m_pageName == self::ADMIN_FIELD_PAGE ?
                '<li><div>DIVISION</div></li>' : '<li><a href="' . self::ADMIN_FIELD_PAGE . '">DIVISION</a></li>')
            . '
               </ul>';
    }
}