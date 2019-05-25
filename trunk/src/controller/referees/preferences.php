<?php

/**
 * Class Controller_Referees_Preferences
 */
class Controller_Referees_Preferences extends Controller_Referees_Base {
    public function __construct() {
        parent::__construct();

        if (isset($_SERVER['REQUEST_METHOD']) and $_SERVER['REQUEST_METHOD'] == 'POST') {
            if ($this->m_operation == View_Base::UPDATE) {
                // TODO
            }
        }
    }

    /**
     * @brief On GET, render the page to ask user to Create account or Login.
     *        On POST, complete login or create account
     */
    public function process() {
        switch ($this->m_operation) {
            case View_Base::SIGN_OUT:
                $this->signOut();
                break;

            case View_Base::UPDATE:
                // TODO
                break;
        }

        $view = new View_Referees_Preferences($this, View_Base::REF_PREFERENCES_PAGE);
        $view->displayPage();
    }
}