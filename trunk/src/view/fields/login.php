<?php

/**
 * @brief Show the Login page and get the user to login or select he create account button.
 */
class View_Fields_Login extends View_Fields_Base {
    /**
     * @brief Construct he View
     *
     * @param $controller - Controller that contains data used when rendering this view.
     */
    public function __construct($controller, $page = self::LOGIN_PAGE) {
        parent::__construct($page, $controller);
    }

    /**
     * @brief Render data for display on the page.
     */
    public function render()
    {
        if ($this->m_controller->m_season->createAllowed) {
            $this->renderLoginView();
        } else {
            $this->renderAuthenticateView();
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
            <form method='post' action='" . self::LOGIN_PAGE . $this->m_urlParams . "'>";

        print "
                <tr>
                    <td colspan='2' style='font-size:24px'><font color='darkblue'><b>Sign In</b></font></td>
                </tr>";

        $divisions = array();
        foreach ($this->m_controller->m_divisions as $division) {
            $divisions[$division->id] = $division->name;
        }

        $this->displaySelector('Division:', Model_Fields_TeamDB::DB_COLUMN_DIVISION_ID, 'select division', $divisions);
        $this->displaySelector('Gender:', Model_Fields_TeamDB::DB_COLUMN_GENDER, 'select gender', $this->m_controller->m_genders);
        $this->displayInput('Email Address:', 'text', Model_Fields_CoachDB::DB_COLUMN_EMAIL, 'email address', $this->m_controller->m_email);

        $passwordInput = '';
        if (self::REQUIRE_PASSWORD) {
            $this->displayInput('Password:', 'text', Model_Fields_CoachDB::DB_COLUMN_PASSWORD, 'password', $this->m_controller->m_password);
        } else {
            $password = rand(1000, 9999);
            $passwordInput = "<input type='hidden' id='" . Model_Fields_CoachDB::DB_COLUMN_PASSWORD . "' name='" . Model_Fields_CoachDB::DB_COLUMN_PASSWORD . "' value='$password'>";
        }

        print "
                <tr>
                    <td colspan='2' align='right'>
                        <input style='background-color: yellow' name='" . View_Base::SUBMIT . "' type='submit' value='" . View_Base::SUBMIT . "'>
                        <input type='hidden' id='region' name='region' value='122'>
                        $passwordInput
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
}