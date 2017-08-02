<?php

/**
 * @brief: Navigation display for Administration of Referees
 */
class View_AdminReferee_Navigation extends View_Navigation
{
    /**
     * @inheritdoc
     */
    public function displayBodyHeader($urlParams)
    {
        $sessionId          = $this->controller->getSessionId();
        $headerButton       = View_Base::SIGN_OUT;
        $nextPage           = View_Base::REFEREE_HOME_PAGE;
        $headerImage        = "/images/aysoLogo.jpeg";
        $name               = isset($this->controller->m_coordinator) ? $this->controller->m_coordinator->name : '';

        $seasonTitle        = isset($this->controller->m_season) ? $this->controller->m_season->name : 'No Season Enabled';
        $headerTitle        = "<font color='darkblue'>AYSO Region 122:<br></font><font color='red'>Referee Administration<br>$seasonTitle</font>";

        print "
                <table valign='top' border='0' style='width: 100%;' cellpadding='10' cellspacing=''>
                    <tr>
                        <td width='50'><img src='$headerImage' alt='Organization Icon' width='75' height='75'></td>
                        <td align='left'><h1>$headerTitle</h1><br></td>
                        <form method='post' action='${nextPage}$urlParams'>
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
            . ($this->pageName == View_Base::REFEREE_HOME_PAGE ?
                '<li><div>REFEREE</div></li>' : '<li><a href="' . View_Base::REFEREE_HOME_PAGE . '">REFEREE</a></li>')
            . '
               </ul>';
    }
}
