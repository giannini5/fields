<?php

use \DAG\Domain\Schedule\Division;

/**
 * @brief: Abstract base class for all adminPractice views.
 */
abstract class View_AdminSchedules_Base extends View_Base {

    /**
     * @brief: Construct a new instance of this base class.
     *
     * @param: $page - Name of the page being constructed.
     * @param $controller - Controller that contains data used when rendering this view.
     */
    public function __construct($page, $controller) {
        parent::__construct($page, $controller);
    }

    public function displayPage()
    {
        $sessionId = $this->m_controller->getSessionId();
        $headerButton = View_Base::SIGN_OUT;
        $nextPage = View_Base::SCHEDULE_UPLOAD_PAGE;
        $headerImage = "/images/aysoLogo.jpeg";
        $name = isset($this->m_controller->m_coordinator) ? $this->m_controller->m_coordinator->name : '';
        $collapsibleCount = $this->getCollapsibleCount();

        $seasonTitle = isset($this->m_controller->m_season) ? $this->m_controller->m_season->name : 'No Season Enabled';
        $headerTitle = "<font color='darkblue'>AYSO Region 122:<br></font><font color='red'>Game Scheduling Administration<br>$seasonTitle</font>";

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
            . ($this->m_pageName == self::SCHEDULE_UPLOAD_PAGE ?
                '<li><div>HOME</div></li>' : '<li><a href="' . self::SCHEDULE_UPLOAD_PAGE . '">HOME</a></li>')
            . ($this->m_pageName == self::SCHEDULE_SEASON_PAGE ?
                '<li><div>SEASON</div></li>' : '<li><a href="' . self::SCHEDULE_SEASON_PAGE . '">SEASON</a></li>')
            . ($this->m_pageName == self::SCHEDULE_GAME_DATE_PAGE ?
                '<li><div>GAME DATE</div></li>' : '<li><a href="' . self::SCHEDULE_GAME_DATE_PAGE . '">GAME DATE</a></li>')
            . ($this->m_pageName == self::SCHEDULE_DIVISIONS_PAGE ?
                '<li><div>DIVISION</div></li>' : '<li><a href="' . self::SCHEDULE_DIVISIONS_PAGE . '">DIVISION</a></li>')
            . ($this->m_pageName == self::SCHEDULE_TEAMS_PAGE ?
                '<li><div>TEAM</div></li>' : '<li><a href="' . self::SCHEDULE_TEAMS_PAGE . '">TEAM</a></li>')
            . ($this->m_pageName == self::SCHEDULE_FAMILY_PAGE ?
                '<li><div>FAMILY</div></li>' : '<li><a href="' . self::SCHEDULE_FAMILY_PAGE . '">FAMILY</a></li>')
            . ($this->m_pageName == self::SCHEDULE_FACILITIES_PAGE ?
                '<li><div>FACILITY</div></li>' : '<li><a href="' . self::SCHEDULE_FACILITIES_PAGE . '">FACILITY</a></li>')
            . ($this->m_pageName == self::SCHEDULE_FIELDS_PAGE ?
                '<li><div>FIELD</div></li>' : '<li><a href="' . self::SCHEDULE_FIELDS_PAGE . '">FIELD</a></li>')
            . ($this->m_pageName == self::SCHEDULE_SCHEDULES_PAGE ?
                '<li><div>SCHEDULE</div></li>' : '<li><a href="' . self::SCHEDULE_SCHEDULES_PAGE . '">SCHEDULE</a></li>')
            . ($this->m_pageName == self::SCHEDULE_PREVIEW_PAGE ?
                '<li><div>PREVIEW</div></li>' : '<li><a href="' . self::SCHEDULE_PREVIEW_PAGE . '">PREVIEW</a></li>')
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

    /**
     * @brief Print the drop down list of teams by division for selection
     *
     * @param int $filterTeamId - Default selection
     */
    public function printTeamSelector($filterTeamId) {
        $teams = $this->m_controller->m_teams;

        $selectorHTML = '';
        $selectorHTML .= "<option value='0' ";
        $selectorHTML .= ">All</option>";

        foreach ($teams as $team) {
            $selected = ($team->id == $filterTeamId) ? ' selected ' : '';
            $teamName = $team->name;
            $selectorHTML .= "<option value='$team->id' $selected>$teamName</option>";
        }

        print "
                <tr>
                    <td><font color='#069'><b>Team:&nbsp</b></font></td>
                    <td><select name='" . View_Base::FILTER_TEAM_ID . "'>" . $selectorHTML . "</select></td>
                </tr>";
    }

    /**
     * @brief Print the drop down list of coaches by division for selection
     *
     * @param int $filterTeamId - Default selection
     */
    public function printCoachSelector($filterCoachId) {
        $coaches = $this->m_controller->m_coaches;

        $sortedCoaches = [];
        foreach ($coaches as $coach) {
            $sortedCoaches[$coach->id] = $coach->name . " (" . $coach->team->name . ")";
        }
        asort($sortedCoaches);

        $selectorHTML = '';
        $selectorHTML .= "<option value='0' ";
        $selectorHTML .= ">All</option>";

        foreach ($sortedCoaches as $id => $name) {
            $selected = ($id == $filterCoachId) ? ' selected ' : '';
            $coachName = $name;
            $selectorHTML .= "<option value='$id' $selected>$coachName</option>";
        }

        print "
                <tr>
                    <td><font color='#069'><b>Coach:&nbsp</b></font></td>
                    <td><select name='" . View_Base::FILTER_COACH_ID . "'>" . $selectorHTML . "</select></td>
                </tr>";
    }
}