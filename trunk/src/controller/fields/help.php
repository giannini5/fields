<?php
use DAG\Framework\Email;


/**
 * Class Controller_Help
 *
 * @brief Controller to render the help page
 */
class Controller_Fields_Help extends Controller_Fields_Base {
    public $m_emailAddress;
    public $m_subject;
    public $m_helpRequest;
    public $m_headerMessage;
    public $m_robot;


    public function __construct() {
        parent::__construct();

        if (isset($_SERVER['REQUEST_METHOD']) and $_SERVER['REQUEST_METHOD'] == 'POST') {
            if ($this->m_operation == View_Base::SUBMIT) {
                $this->m_emailAddress = $this->getPostAttribute(
                    View_Base::EMAIL_ADDRESS,
                    ''
                );
                $this->m_subject = $this->getPostAttribute(
                    View_Base::SUBJECT,
                    'AYSO Practice Field Help Request'
                );
                $this->m_helpRequest = $this->getPostAttribute(
                    View_Base::HELP_REQUEST,
                    'Uh, no body to this email.  Good stuff must be in the subject...'
                );
                $this->m_robot = $this->getPostAttribute(
                    View_Base::ROBOT,
                    0
                );
            }
        }
    }

    /**
     * @brief On GET, render the page to ask user for create account attributes
     */
    public function process() {
        if ($this->m_operation == View_Base::SUBMIT) {
            if ($this->m_robot == 30) {
                $this->sendHelpRequestEmail();
                $this->m_headerMessage = "Email sent.  Expect a response within 24 hours.";
            }
            else {
                $this->m_headerMessage = "Sorry, I think you are a robot - no email sent";
            }
        }
        $view = new View_Fields_Help($this);
        $view->displayPage();
    }

    public function sendHelpRequestEmail()
    {
        $email = new Email();
        $email->send($this->m_subject, $this->m_helpRequest, EMAIL_USER, EMAIL_NAME, EMAIL_USER, EMAIL_NAME, $this->m_emailAddress);
    }
}
