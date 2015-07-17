<?php

/**
 * @brief Show the Welcome page and get the user to either create an account or login.
 */
class View_Welcome extends View_Base {
    /**
     * @brief Construct he View
     *
     * @param $controller - Controller that contains data used when rendering this view.
     */
    public function __construct($controller) {
        parent::__construct(self::WELCOME_PAGE, $controller);
    }

    /**
     * @brief Render data for display on the page.
     */
    public function render() {
        print "
            <table valign='top' border='0' cellpadding='5' cellspacing='0'>";

        // Create account form
        print "
            <form method='post' action='" . self::WELCOME_PAGE . $this->m_urlParams . "'>
                <tr>
                    <td colspan='2' style='font-size:24px'><font color='darkblue'><b>Create Account</b></font></td>
                </tr>";

        $divisions = array();
        foreach ($this->m_controller->m_divisions as $division) {
            $divisions[$division->id] = $division->name;
        }

        $this->displaySelector('Division:', Model_Fields_TeamDB::DB_COLUMN_DIVISION_ID, 'select division', $divisions);
        $this->displaySelector('Team Number:', Model_Fields_TeamDB::DB_COLUMN_TEAM_NUMBER, 'select team number', $this->m_controller->m_teamNumbers);
        $this->displayInput('Name:', 'text', Model_Fields_CoachDB::DB_COLUMN_NAME, 'firstName lastName', $this->m_controller->m_name);
        $this->displayInput('Email Address:', 'text', Model_Fields_CoachDB::DB_COLUMN_EMAIL, 'email address', $this->m_controller->m_email);
        $this->displayInput('Phone Number:', 'text', Model_Fields_CoachDB::DB_COLUMN_PHONE, 'phone number', $this->m_controller->m_phone);
        $this->displayInput('Password:', 'text', Model_Fields_CoachDB::DB_COLUMN_PASSWORD, 'password', $this->m_controller->m_password);

        print "
                <tr>
                    <td colspan='2' align='right'>
                        <input style='background-color: yellow' name='" . View_Base::SUBMIT . "' type='submit' value='" . View_Base::CREATE_ACCOUNT . "'>
                        <input type='hidden' id='date' name='region' value='122'>
                    </td>
                    <td>&nbsp</td>
                </tr>
            </form>";

        // Re-direct to the Login Account Form
        print "
            <form method='post' action='" . self::WELCOME_PAGE . $this->m_urlParams . "'>
                <tr><td colspan=3>&nbsp<td></tr>
                <tr>
                    <td colspan='1' align='right'><font color='lightblue'>Already have an account?</font></td>
                    <td colspan='1' align='right'>
                        <input style='background-color: yellow' name='" . View_Base::SUBMIT . "' type='submit' value='" . View_Base::LOGIN . "'>
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