<?php

/**
 * @brief: Navigation display for Administration of Game Schedules
 */
class View_AdminSchedules_Navigation extends View_Navigation
{
    /**
     * @inheritdoc
     */
    public function displayBodyHeader($urlParams)
    {
        $sessionId          = $this->controller->getSessionId();
        $headerButton       = View_Base::SIGN_OUT;
        $nextPage           = View_Base::SCHEDULE_UPLOAD_PAGE;
        $headerImage        = "/images/aysoLogo.jpeg";
        $splashImage        = "/images/logo-splash.png";
        $name               = isset($this->controller->m_coordinator) ? $this->controller->m_coordinator->name : '';

        $seasonTitle        = isset($this->controller->m_season) ? $this->controller->m_season->name : 'No Season Enabled';
        $headerTitle        = "<font color='darkblue'>" . LEAGUE_NAME . ":<br></font><font color='red'>Game Scheduling Administration<br>$seasonTitle</font>";

        print "
                <table valign='top' border='0' style='width: 100%;' cellpadding='10' cellspacing=''>
                    <tr>
                        <td width='50'><img src='$headerImage' alt='Organization Icon' width='75' height='75'></td>
                        <td align='left'><h1>$headerTitle</h1><br></td>
                        <td width='50'><img src='$splashImage' alt='Organization Icon' width='75' height='75'></td>
                        <form method='post' action='{$nextPage}$urlParams'>
                            <td nowrap width='100' align='left'>";

        if ($this->controller->m_isAuthenticated) {
            print "
                                $name<br>
                                <input style='background-color: yellow' name=" . View_Base::SUBMIT . " type='submit' value='$headerButton'>";
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
    }

    /**
     * @inheritdoc
     */
    public function displayNavigation()
    {
        print '
                <ul id="nav">'
            . ($this->pageName == View_Base::SCHEDULE_UPLOAD_PAGE ?
                '<li><div>HOME</div></li>' : '<li><a href="' . View_Base::SCHEDULE_UPLOAD_PAGE . '">HOME</a></li>')
            . ($this->pageName == View_Base::SCHEDULE_SEASON_PAGE ?
                '<li><div>SEASON</div></li>' : '<li><a href="' . View_Base::SCHEDULE_SEASON_PAGE . '">SEASON</a></li>')
            . ($this->pageName == View_Base::SCHEDULE_GAME_DATE_PAGE ?
                '<li><div>GAME DATE</div></li>' : '<li><a href="' . View_Base::SCHEDULE_GAME_DATE_PAGE . '">GAME DATE</a></li>')
            . ($this->pageName == View_Base::SCHEDULE_DIVISIONS_PAGE ?
                '<li><div>DIVISION</div></li>' : '<li><a href="' . View_Base::SCHEDULE_DIVISIONS_PAGE . '">DIVISION</a></li>')
            . ($this->pageName == View_Base::SCHEDULE_TEAMS_PAGE ?
                '<li><div>TEAM</div></li>' : '<li><a href="' . View_Base::SCHEDULE_TEAMS_PAGE . '">TEAM</a></li>')
            . ($this->pageName == View_Base::SCHEDULE_FAMILY_PAGE ?
                '<li><div>FAMILY</div></li>' : '<li><a href="' . View_Base::SCHEDULE_FAMILY_PAGE . '">FAMILY</a></li>')
            . ($this->pageName == View_Base::SCHEDULE_FACILITIES_PAGE ?
                '<li><div>FACILITY</div></li>' : '<li><a href="' . View_Base::SCHEDULE_FACILITIES_PAGE . '">FACILITY</a></li>')
            . ($this->pageName == View_Base::SCHEDULE_FIELDS_PAGE ?
                '<li><div>FIELD</div></li>' : '<li><a href="' . View_Base::SCHEDULE_FIELDS_PAGE . '">FIELD</a></li>')
            . ($this->pageName == View_Base::SCHEDULE_SCHEDULES_PAGE ?
                '<li><div>SCHEDULE</div></li>' : '<li><a href="' . View_Base::SCHEDULE_SCHEDULES_PAGE . '">SCHEDULE</a></li>')
            . ($this->pageName == View_Base::SCHEDULE_PREVIEW_PAGE ?
                '<li><div>PREVIEW</div></li>' : '<li><a href="' . View_Base::SCHEDULE_PREVIEW_PAGE . '">PREVIEW</a></li>')
            . '
               </ul>';
    }
}
