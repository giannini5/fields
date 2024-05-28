<?php

/**
 * @brief: Navigation display for Administration of Practice Fields
 */
class View_AdminPractice_Navigation extends View_Navigation
{
    /**
     * @inheritdoc
     */
    public function displayBodyHeader($urlParams)
    {
        $sessionId          = $this->controller->getSessionId();
        $headerButton       = View_Base::SIGN_OUT;
        $nextPage           = View_Base::ADMIN_HOME_PAGE;
        $headerImage        = "/images/aysoLogo.jpeg";
        $name               = isset($this->controller->m_coordinator) ? $this->controller->m_coordinator->name : '';
        $headerTitle        = "<font color='darkblue'>" . LEAGUE_NAME . ":<br></font><font color='red'>Practice Field Administration</font>";

        print "
                <table valign='top' border='0' style='width: 100%;' cellpadding='10' cellspacing=''>
                    <tr>
                        <td width='50'><img src='$headerImage' alt='Organization Icon' width='75' height='75'></td>
                        <td align='left'><h1>$headerTitle</h1><br></td>
                        <form method='post' action='{$nextPage}$urlParams'>
                            <td nowrap width='100' align='left'>";

        if ($this->controller->m_isAuthenticated) {
            print "
                                $name<br>
                                <input style='background-color: yellow' name=". View_Base::SUBMIT. " type='submit' value='$headerButton'>";
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
            . ($this->pageName == View_Base::ADMIN_HOME_PAGE ?
                '<li><div>HOME</div></li>' : '<li><a href="' . View_Base::ADMIN_HOME_PAGE . '">HOME</a></li>')
            . ($this->pageName == View_Base::ADMIN_SEASON_PAGE ?
                '<li><div>SEASON</div></li>' : '<li><a href="' . View_Base::ADMIN_SEASON_PAGE . '">SEASON</a></li>')
            . ($this->pageName == View_Base::ADMIN_DIVISION_PAGE ?
                '<li><div>DIVISION</div></li>' : '<li><a href="' . View_Base::ADMIN_DIVISION_PAGE . '">DIVISION</a></li>')
            . ($this->pageName == View_Base::ADMIN_LOCATION_PAGE ?
                '<li><div>LOCATION</div></li>' : '<li><a href="' . View_Base::ADMIN_LOCATION_PAGE . '">LOCATION</a></li>')
            . ($this->pageName == View_Base::ADMIN_FACILITY_PAGE ?
                '<li><div>FACILITY</div></li>' : '<li><a href="' . View_Base::ADMIN_FACILITY_PAGE . '">FACILITY</a></li>')
            . ($this->pageName == View_Base::ADMIN_FIELD_PAGE ?
                '<li><div>FIELD</div></li>' : '<li><a href="' . View_Base::ADMIN_FIELD_PAGE . '">FIELD</a></li>')
            . ($this->pageName == View_Base::ADMIN_TRANSACTION_PAGE ?
                '<li><div>TRANSACTIONS</div></li>' : '<li><a href="' . View_Base::ADMIN_TRANSACTION_PAGE . '">TRANSACTIONS</a></li>')
            . ($this->pageName == View_Base::ADMIN_RESERVATIONS_PAGE ?
                '<li><div>RESERVATIONS</div></li>' : '<li><a href="' . View_Base::ADMIN_RESERVATIONS_PAGE . '">RESERVATIONS</a></li>')
            . ($this->pageName == View_Base::ADMIN_SELECT_FIELD_PAGE ?
                '<li><div>SELECT</div></li>' : '<li><a href="' . View_Base::ADMIN_SELECT_FIELD_PAGE . '?newSelection=1">SELECT</a></li>')
            . '
               </ul>';
    }
}
