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
    public function __construct($controller) {
        parent::__construct(self::LOGIN_PAGE, $controller);
    }

    /**
     * @brief Render data for display on the page.
     */
    public function render() {
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
        $this->displayInput('Password:', 'text', Model_Fields_CoachDB::DB_COLUMN_PASSWORD, 'password', $this->m_controller->m_password);

        print "
                <tr>
                    <td colspan='2' align='right'>
                        <input style='background-color: yellow' name='" . View_Base::SUBMIT . "' type='submit' value='" . View_Base::SUBMIT . "'>
                        <input type='hidden' id='date' name='region' value='122'>
                    </td>
                    <td>&nbsp</td>
                </tr>
            </form>";

        print "
            </table>
            </td></tr></table>";
    }
}