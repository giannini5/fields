<?php

use \DAG\Domain\Schedule\Division;

/**
 * @brief: Abstract base class for all schedule views.
 */
abstract class View_Games_Base extends View_Base {

    /**
     * @brief: Construct a new instance of this base class.
     *
     * @param string        $page - Name of the page being constructed.
     * @param               $controller - Controller that contains data used when rendering this view.
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
        $sessionId      = $this->m_controller->getSessionId();
        $headerImage    = "/images/aysoLogo.jpeg";
        $seasonTitle    = isset($this->m_controller->m_season) ? $this->m_controller->m_season->name : 'No Season Enabled';
        $headerTitle    = "<font color='darkblue'>AYSO Region 122:<br></font><font color='red'>Game Schedules<br>$seasonTitle</font>";

        print "
            <!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Strict//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd'>
            <html xmlns='http://www.w3.org/1999/xhtml' lang='en' xml:lang='en'>";

        $this->m_styles->render(0);

        print "
            <head>
                <title>Game Schedules</title>
                <script type='text/JavaScript' src='../js/scw.js'></script>
            </head>

            <body bgcolor='#FFFFFF'>
                <div id='wrap'>
                <table valign='top' border='0' style='width: 100%;' cellpadding='10' cellspacing=''>
                    <tr>
                        <td width='50'><img src='$headerImage' alt='Organization Icon' width='75' height='75'></td>
                        <td align='left'><h1>$headerTitle</h1><br></td>
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
            . ($this->m_pageName == self::GAMES_SCHEDULE_PAGE ?
                '<li><div>SCHEDULE</div></li>' : '<li><a href="' . self::GAMES_SCHEDULE_PAGE . '">SCHEDULE</a></li>')
            . ($this->m_pageName == self::GAMES_STANDINGS_PAGE ?
                '<li><div>STANDINGS</div></li>' : '<li><a href="' . self::GAMES_STANDINGS_PAGE . '">STANDINGS</a></li>')
            . '
               </ul>';
    }
}