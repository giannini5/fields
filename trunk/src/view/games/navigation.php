<?php

/**
 * @brief: Navigation display for Game Schedules
 */
class View_Games_Navigation extends View_Navigation
{
    /**
     * @inheritdoc
     */
    public function displayBodyHeader($urlParams)
    {
        $headerImage    = "/images/aysoLogo.jpeg";
        $splashImage    = "/images/splash-2018-5_1.jpg";
        $seasonTitle    = isset($this->controller->m_season) ? $this->controller->m_season->name : 'No Season Enabled';
        $headerTitle    = "<font color='darkblue'>" . LEAGUE_NAME . ":<br></font><font color='red'>Game Schedules<br>$seasonTitle</font>";

        print "
                <table valign='top' border='0' style='width: 100%;' cellpadding='10' cellspacing=''>
                    <tr>
                        <td width='50'><img src='$headerImage' alt='Organization Icon' width='75' height='75'></td>
                        <td align='left'><h1>$headerTitle</h1><br></td>
                        <td width='50'><img src='$splashImage' alt='Tournament Icon' width='75' height='75'></td>
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
            . ($this->pageName == View_Base::GAMES_FLIGHTS_PAGE ?
                '<li><div>FLIGHTS</div></li>' : '<li><a href="' . View_Base::GAMES_FLIGHTS_PAGE . '">FLIGHTS</a></li>')
            . ($this->pageName == View_Base::GAMES_SCHEDULE_PAGE ?
                '<li><div>SCHEDULE</div></li>' : '<li><a href="' . View_Base::GAMES_SCHEDULE_PAGE . '">SCHEDULE</a></li>')
            . ($this->pageName == View_Base::GAMES_STANDINGS_PAGE ?
                '<li><div>STANDINGS</div></li>' : '<li><a href="' . View_Base::GAMES_STANDINGS_PAGE . '">STANDINGS</a></li>')
            . ($this->pageName == View_Base::GAMES_TEAM_STATS_PAGE ?
                '<li><div>TEAM STATS</div></li>' : '<li><a href="' . View_Base::GAMES_TEAM_STATS_PAGE . '">TEAM STATS</a></li>')
            . ($this->pageName == View_Base::GAMES_GAME_CARDS_PAGE ?
                '<li><div>GAME CARDS</div></li>' : '<li><a href="' . View_Base::GAMES_GAME_CARDS_PAGE . '">GAME CARDS</a></li>')
            . '
               </ul>';
    }
}
