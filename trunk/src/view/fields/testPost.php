<?php

/**
 * @brief Show the Test Post page
 */
class View_Fields_TestPost extends View_Fields_Base {
    /**
     * @brief Construct he View
     *
     * @param Controller_Base   $controller - Controller that contains data used when rendering this view.
     * @param string            $page
     */
    public function __construct($controller, $page = self::TEST_POST_PAGE) {
        parent::__construct($page, $controller);
    }

    /**
     * @brief Render data for display on the page.
     */
    public function renderPage() {
        print "
            <table align='center' valign='top' border='1' cellpadding='5' cellspacing='0'>
            <tr><td>
            <table align='center' valign='top' border='0' cellpadding='5' cellspacing='0'>";

        // Test sending post attributes to Welcome page for auto-login
        print "
            <form method='post' action='" . self::WELCOME_PAGE . $this->m_urlParams . "'>";

        print "
                <tr>
                    <td colspan='2' style='font-size:24px'><font color='darkblue'><b>Sign In</b></font></td>
                </tr>";

        $divisions = array();
        foreach ($this->m_controller->m_divisions as $division) {
            $divisions[$division->name] = $division->name;
        }

        $this->displaySelector('Division:', Controller_Fields_Welcome::DIVISION_NAME, 'select division', $divisions);
        $this->displaySelector('Gender:', Model_Fields_TeamDB::DB_COLUMN_GENDER, 'select gender', $this->m_controller->m_genders);
        $this->displayInput('Name:', 'text', Model_Fields_CoachDB::DB_COLUMN_NAME, 'firstName lastName', $this->m_controller->m_name);
        $this->displayInput('Email Address:', 'text', Model_Fields_CoachDB::DB_COLUMN_EMAIL, 'email address', $this->m_controller->m_email);
        $this->displayInput('Phone Number:', 'text', Model_Fields_CoachDB::DB_COLUMN_PHONE, 'phone number', $this->m_controller->m_phone);

        print "
                <tr>
                    <td colspan='2' align='right'>
                        <input style='background-color: yellow' name='" . View_Base::SUBMIT . "' type='submit' value='" . View_Base::SUBMIT . "'>
                    </td>
                    <td>&nbsp</td>
                </tr>
            </form>";

        print "
            </table>
            </td></tr></table>";
    }
}