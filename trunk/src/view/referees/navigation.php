<?php

/**
 * @brief: Navigation display for Referees
 */
class View_Referees_Navigation extends View_Navigation
{
    /**
     * @inheritdoc
     */
    public function displayBodyHeader($urlParams)
    {
        $sessionId          = $this->controller->getSessionId();
        $nextPage           = View_Base::REFEREE_PREFERENCES_PAGE;
        $headerImage        = "/images/aysoLogo.jpeg";
        $referee            = $this->controller->referee;
        $refereeInfo        = isset($referee) ? "<font color='black'>$referee->name</font>" : '';
        $headerTitle        = "<font color='darkblue'>AYSO Region 122:<br></font>Referees";
        $showSingOutButton  = $this->controller->m_isAuthenticated;

        print "
                <table valign='top' border='0' style='width: 100%;' cellpadding='10' cellspacing=''>
                    <tr>
                        <td width='50'><img src='$headerImage' alt='Organization Icon' width='75' height='75'></td>
                        <td align='left'><h1>$headerTitle</h1><br></td>";

        if ($showSingOutButton) {
            print "
                        <form method='post' action='${nextPage}$urlParams'>
                            <td colspan='3' nowrap width='100' align='right'>
                                $refereeInfo<br>
                                <input style='background-color: yellow' name=" . View_Base::SUBMIT . " type='submit' value='" . View_Base::SIGN_OUT . "'>
                                <input type='hidden' id='sessionId' name='sessionId' value='$sessionId'>
                            </td>
                        </form>";
        }

        print "
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
            . ($this->pageName == View_Base::REF_PREFERENCES_PAGE ?
                '<li><div>PREFERENCES</div></li>' : '<li><a href="' . View_Base::REF_PREFERENCES_PAGE . '">PREFERENCES</a></li>')
            . ($this->pageName == View_Base::REF_TEAMS_PAGE ?
                '<li><div>TEAMS</div></li>' : '<li><a href="' . View_Base::REF_TEAMS_PAGE . '">TEAMS</a></li>')
            . ($this->pageName == View_Base::REF_ASSIGNMENTS_PAGE ?
                '<li><div>ASSIGNMENTS</div></li>' : '<li><a href="' . View_Base::REF_ASSIGNMENTS_PAGE . '?newSelection=1">ASSIGNMENTS</a></li>')
            . ($this->pageName == View_Base::REF_HELP_PAGE ?
                '<li><div>HELP</div></li>' : '<li><a href="' . View_Base::REF_HELP_PAGE . '">HELP</a></li>')
            . '
               </ul>';
    }
}
