<?php

/**
 * @brief Display the specified image if it exists
 */
class View_Fields_Help extends View_Fields_Base {
    /**
     * @brief Construct he View
     *
     * @param $controller - Controller that contains data used when rendering this view.
     * @param $page       - Page name
     */
    public function __construct($controller, $page = self::HELP_PAGE) {
        parent::__construct($page, $controller);
    }

    /**
     * @brief Render data for display on the page.
     */
    public function render() {
        $emailAddress = View_Base::EMAIL_ADDRESS;
        $subject      = View_Base::SUBJECT;
        $helpRequest  = View_Base::HELP_REQUEST;

        if (!empty($this->m_controller->m_headerMessage)) {
            $headerMessage = $this->m_controller->m_headerMessage;
            print "
                <p align='center'><font color='green' size='4'>$headerMessage</font></p>
            ";
        }

        print "
            <table valign='top' align=center border='1' cellpadding='5' cellspacing='5'>
            <tr><td>
                <table valign='top' align=center border='0' cellpadding='5' cellspacing='5'>
                    <form method='post' action='" . self::HELP_PAGE . $this->m_urlParams . "'>
                        <tr>
                            <td>
                                <p>Email: <input style='text-align:left' type='text' name='$emailAddress' autofocus=1 required=1 size=30 maxlength=60 placeholder='Email Address'></p>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <p>Subject: <input style='text-align:left' type='text' name='$subject' required=1 size=60 maxlength=70 placeholder='Email Subject'></p>
                            </td>
                        </tr>
                        <tr>
                            <td colspan=2>
                                <textarea name='$helpRequest' maxlength=2048 required=0 wrap='hard' rows='4' cols='70' placeholder='Help request'></textarea>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <input style='background-color: yellow' name='" . View_Base::SUBMIT . "' type='submit' value='" . View_Base::SUBMIT . "'>
                            </td>
                        </tr>
                    </form>
                </table>
            </td></tr></table>";
    }
}
