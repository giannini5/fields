<?php

/**
 * @brief Show the Login if not authenticated; otherwise show referee preferences
 */
class View_Referees_Preferences extends View_Referees_Base {

    /**
     * View_Referees_Preferences constructor.
     * @param Controller_Base $controller
     * @param string          $page
     */
    public function __construct($controller, $page = self::REF_PREFERENCES_PAGE) {
        parent::__construct($page, $controller);
    }

    /**
     * @brief Render data for display on the page.
     */
    public function renderPage()
    {
        if (!$this->m_controller->m_isAuthenticated) {
            $this->renderLoginView();
        } else {
            $this->renderPreferences();
        }
    }

    /**
     * @brief Render login form for display on the page.
     */
    public function renderLoginView() {
        $this->_printLoginError();

        print "
            <table align='center' valign='top' border='1' cellpadding='5' cellspacing='0'>
            <tr><td>
            <table align='center' valign='top' border='0' cellpadding='5' cellspacing='0'>";

        // Login To an Existing Account Form
        print "
            <form method='post' action='" . $this->m_pageName . $this->m_urlParams . "'>";

        print "
                <tr>
                    <td colspan='2' style='font-size:24px'><font color='darkblue'><b>Sign In</b></font></td>
                </tr>";

        $this->displayInput('Email Address:', 'text', View_Base::EMAIL_ADDRESS, 'email address', '');
        $this->displayInput('Password:', 'text', View_Base::PASSWORD, 'password', '');

        print "
                <tr>
                    <td colspan='2' align='right'>
                        <input style='background-color: yellow' name='" . View_Base::SUBMIT . "' type='submit' value='" . View_Base::SIGN_IN . "'>
                        <input type='hidden' id='region' name='region' value='122'>
                    </td>
                    <td>&nbsp</td>
                </tr>
            </form>";

        print "
            </table>
            </td></tr></table>";
    }

    /**
     * @brief Print the error seen with the last login attempt (no op if no error)
     */
    private function _printLoginError() {
        if (! empty($this->m_controller->m_loginErrorMessage)) {
            $errorString = $this->m_controller->m_loginErrorMessage;

            print "
            <table valign='top' align='center' width='625' border='0' cellpadding='5' cellspacing='0'>
                <tr>
                    <td><h1 align='left'><font color='red' size='4'>$errorString</font></h1></td>
                </tr>
            </table>";
        }
    }

    private function renderPreferences()
    {
        print "TODO";
    }
}