<?php

/**
 * @brief Show the Login page and get the user to login or select he create account button.
 */
class View_Login extends View_Base {
    /**
     * @brief Construct he View
     *
     * @param $controller - Controller that contains data used when rendering this view.
     */
    public function __construct($controller) {
        parent::__construct(self::LOGIN_PAGE, $controller);
    }

    /**
     * @brief Render data for display on the page.
     */
    public function render() {
        print "
            <table valign='top' border='0' cellpadding='5' cellspacing='0'>";

        // Login To an Existing Account Form
        print "
            <form method='post' action='" . self::LOGIN_PAGE . $this->m_urlParams . "'>";

        print "
                <tr>
                    <td colspan='3' style='font-size:24px'><font color='darkblue'><b>Login to Account</b></font></td>
                </tr>";

        $divisions = array();
        foreach ($this->m_controller->m_divisions as $division) {
            $divisions[$division->id] = $division->name;
        }

        $this->displaySelector('Division:', Model_Fields_TeamDB::DB_COLUMN_DIVISION_ID, 'select division', $divisions);
        $this->displaySelector('Team Number:', Model_Fields_TeamDB::DB_COLUMN_TEAM_NUMBER, 'select team number', $this->m_controller->m_teamNumbers);
        $this->displayInput('Email Address:', 'text', Model_Fields_CoachDB::DB_COLUMN_EMAIL, 'email address', $this->m_controller->m_email);
        $this->displayInput('Password:', 'text', Model_Fields_CoachDB::DB_COLUMN_PASSWORD, 'password', $this->m_controller->m_password);

        print "
                <tr>
                    <td colspan='2' align='right'>
                        <input style='background-color: yellow' name='" . View_Base::SUBMIT . "' type='submit' value='" . View_Base::LOGIN . "'>
                        <input type='hidden' id='date' name='region' value='122'>
                    </td>
                    <td>&nbsp</td>
                </tr>
            </form>";

        // Re-direct to the Create Account Form
        print "
            <form method='post' action='" . self::LOGIN_PAGE . $this->m_urlParams . "'>
                <tr><td colspan=3>&nbsp<td></tr>
                <tr>
                    <td colspan='1' align='right'><font color='lightblue'>First time? Create a new account:</font></td>
                    <td colspan='1' align='right'>
                        <input style='background-color: yellow' name='" . View_Base::SUBMIT . "' type='submit' value='" . View_Base::CREATE_ACCOUNT . "'>
                        <input type='hidden' id='name' name='" . Model_Fields_CoachDB::DB_COLUMN_NAME . "' value=''>
                        <input type='hidden' id='email' name='" . Model_Fields_CoachDB::DB_COLUMN_EMAIL . "' value=''>
                        <input type='hidden' id='password' name='" . Model_Fields_CoachDB::DB_COLUMN_PASSWORD . "' value=''>
                    </td>
                    <td>&nbsp</td>
                </tr>
            </form>";

        print "
            </table>";
    }
}