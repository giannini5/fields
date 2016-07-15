<?php

/**
 * @brief Show the CreateAccount page and get the user to either create an account or login.
 */
class View_Fields_CreateAccount extends View_Fields_Base {
    /**
     * @brief Construct he View
     *
     * @param $controller - Controller that contains data used when rendering this view.
     */
    public function __construct($controller, $page = self::CREATE_ACCOUNT_PAGE) {
        parent::__construct($page, $controller);
    }

    /**
     * @brief Render data for display on the page.
     */
    public function render()
    {
        if ($this->m_controller->m_season->createAllowed) {
            $this->renderCreateAccountView();
        } else {
            $this->renderAuthenticateView();
        }
    }

    /**
     * @brief Render data for display create account dialog on the page.
     */
    public function renderCreateAccountView() {
        print "
            <table align='center' valign='top' border='1' cellpadding='5' cellspacing='0'>
            <tr><td>
            <table align='center' valign='top' border='0' cellpadding='5' cellspacing='0'>";

        // Create account form
        print "
            <form method='post' action='" . self::CREATE_ACCOUNT_PAGE . $this->m_urlParams . "'>
                <tr>
                    <td colspan='2' style='font-size:24px'><font color='darkblue'><b>Create Account</b></font></td>
                </tr>";

        $divisions = array();
        foreach ($this->m_controller->m_divisions as $division) {
            $divisions[$division->id] = $division->name;
        }

        $this->displaySelector('Division:', Model_Fields_TeamDB::DB_COLUMN_DIVISION_ID, 'select division', $divisions);
        $this->displaySelector('Gender:', Model_Fields_TeamDB::DB_COLUMN_GENDER, 'select gender', $this->m_controller->m_genders);
        $this->displayInput('Name:', 'text', Model_Fields_CoachDB::DB_COLUMN_NAME, 'firstName lastName', $this->m_controller->m_name);
        $this->displayInput('Email Address:', 'text', Model_Fields_CoachDB::DB_COLUMN_EMAIL, 'email address', $this->m_controller->m_email);
        $this->displayInput('Phone Number:', 'text', Model_Fields_CoachDB::DB_COLUMN_PHONE, 'phone number', $this->m_controller->m_phone);

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
            </td></tr>
            </table>";
    }
}