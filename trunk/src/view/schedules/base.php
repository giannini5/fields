<?php

/**
 * @brief: Abstract base class for all admin views.
 */
abstract class View_Schedules_Base extends View_Base {

    /**
     * @brief: Construct a new instance of this base class.
     *
     * @param: $page - Name of the page being constructed.
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
        $nextPage = View_Base::SCHEDULE_HOME_PAGE;
        $headerImage = "images/aysoLogo.jpeg";
        $name = isset($this->m_controller->m_coordinator) ? $this->m_controller->m_coordinator->name : '';
        $collapsibleCount = $this->getCollapsibleCount();

        $headerTitle = "<font color='darkblue'>AYSO Region 122:<br></font><font color='red'>Game Scheduling Administration</font>";

        print "
            <!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Strict//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd'>
            <html xmlns='http://www.w3.org/1999/xhtml' lang='en' xml:lang='en'>";

        $this->m_styles->render($collapsibleCount);

        print "
            <head>
                <title>Game Scheduling</title>
                <script type='text/JavaScript' src='../js/scw.js'></script>
            </head>

            <body bgcolor='#FFFFFF'>
                <div id='wrap'>
                <table valign='top' border='0' style='width: 100%;' cellpadding='10' cellspacing=''>
                    <tr>
                        <td width='50'><img src='$headerImage' alt='Organization Icon' width='75' height='75'></td>
                        <td align='left'><h1>$headerTitle</h1><br></td>
                        <form method='post' action='${nextPage}$this->m_urlParams'>
                            <td nowrap width='100' align='left'>";

        if ($this->m_controller->m_isAuthenticated) {
            print "
                                $name<br>
                                <input style='background-color: yellow' name=".self::SUBMIT." type='submit' value='$headerButton'>";
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
        $this->printError();
        $this->render();

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
     * @brief Display Header Navigation (Home, Season, etc.)
     */
    public function displayHeaderNavigation() {
        print '
                <ul id="nav">'
            . ($this->m_pageName == self::SCHEDULE_HOME_PAGE ?
                '<li><div>HOME</div></li>' : '<li><a href="' . self::SCHEDULE_HOME_PAGE . '">HOME</a></li>')
            . ($this->m_pageName == self::SCHEDULE_SEASON_PAGE ?
                '<li><div>SEASON</div></li>' : '<li><a href="' . self::SCHEDULE_SEASON_PAGE . '">SEASON</a></li>')
            . ($this->m_pageName == self::SCHEDULE_FIELDS_PAGE ?
                '<li><div>FIELD</div></li>' : '<li><a href="' . self::SCHEDULE_FIELDS_PAGE . '">FIELD</a></li>')
            . ($this->m_pageName == self::SCHEDULE_TEAMS_PAGE ?
                '<li><div>TEAM</div></li>' : '<li><a href="' . self::SCHEDULE_TEAMS_PAGE . '">TEAM</a></li>')
            . ($this->m_pageName == self::SCHEDULE_DIVISIONS_PAGE ?
                '<li><div>DIVISION</div></li>' : '<li><a href="' . self::SCHEDULE_DIVISIONS_PAGE . '">DIVISION</a></li>')
            . ($this->m_pageName == self::SCHEDULE_PLAYERS_PAGE ?
                '<li><div>PLAYER</div></li>' : '<li><a href="' . self::SCHEDULE_PLAYERS_PAGE . '">PLAYER</a></li>')
            . ($this->m_pageName == self::SCHEDULE_SCHEDULES_PAGE ?
                '<li><div>SCHEDULE</div></li>' : '<li><a href="' . self::SCHEDULE_SCHEDULES_PAGE . '">SCHEDULE</a></li>')
            . '
               </ul>';
    }

    /**
     * @brief Return the count of classes that need to be created to support collapsing tables.
     *
     * @return int $collapsibleCount
     */
    public function getCollapsibleCount() {
        return 0;
    }
}