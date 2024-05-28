<?php

/**
 * @brief: Navigation display for Practice Fields
 */
#[AllowDynamicProperties]
class View_Fields_Navigation extends View_Navigation
{
    /**
     * @inheritdoc
     */
    public function displayBodyHeader($urlParams)
    {
        $sessionId          = $this->controller->getSessionId();
        $headerButton       = $this->controller->getHeaderButtonToShow();
        $nextPage           = View_Base::WELCOME_PAGE;
        $headerImage        = "/images/aysoLogo.jpeg";
        $coachName          = $this->controller->getCoachName();
        $divisionName       = $this->controller->getDivisionName();
        $gender             = $this->controller->getGender();
        $coachInfo          = $coachName != '' ? "<font color='black'>$coachName<br>Team: $divisionName-$gender</font>" : '';
        $headerTitle        = "<font color='darkblue'>" . LEAGUE_NAME . ":<br></font>Practice Field Reservations";
        $showLoginButton    = ($headerButton == View_Base::CREATE_ACCOUNT
            and !$this->controller->m_isAuthenticated
            and ($this->pageName == View_Base::WELCOME_PAGE or $this->pageName == View_Base::SHOW_RESERVATION_PAGE)
            and $this->controller->m_operation != View_Base::SIGN_IN) ? TRUE : FALSE;

        $hideButtons        = $headerButton == View_Base::NO_BUTTON;

        print "
                <table valign='top' border='0' style='width: 100%;' cellpadding='10' cellspacing=''>
                    <tr>
                        <td width='50'><img src='$headerImage' alt='Organization Icon' width='75' height='75'></td>
                        <td align='left'><h1>$headerTitle</h1><br></td>";

        if (!$hideButtons and $showLoginButton) {
            print "
                        <form method='post' action='{$nextPage}$urlParams'>
                            <td colspan='3' nowrap width='100' align='right'>
                                $coachInfo<br>
                                <input style='background-color: yellow' name=" . View_Base::SUBMIT . " type='submit' value='" . View_Base::SIGN_IN . "'>
                            </td>
                        </form>";
        }

        if (!$hideButtons) {
            print "
                        <form method='post' action='{$nextPage}$urlParams'>
                            <td nowrap width='100' align='right'>
                                $coachInfo<br>
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
            . ($this->pageName == View_Base::WELCOME_PAGE || $this->pageName == View_Base::LOGIN_PAGE ?
                '<li><div>HOME</div></li>' : '<li><a href="' . View_Base::WELCOME_PAGE . '">HOME</a></li>')
            . ($this->pageName == View_Base::SHOW_RESERVATION_PAGE ?
                '<li><div>RESERVATIONS</div></li>' : '<li><a href="' . View_Base::SHOW_RESERVATION_PAGE . '">RESERVATIONS</a></li>')
            . ($this->pageName == View_Base::SELECT_FIELD_PAGE ?
                '<li><div>SELECT</div></li>' : '<li><a href="' . View_Base::SELECT_FIELD_PAGE . '?newSelection=1">SELECT</a></li>')
            . ($this->pageName == View_Base::HELP_PAGE ?
                '<li><div>HELP</div></li>' : '<li><a href="' . View_Base::HELP_PAGE . '">HELP</a></li>')
            . '
               </ul>';
    }
}
