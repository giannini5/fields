<?php

/**
 * Class Controller_Welcome
 *
 * @brief Get user to create an account or login to an existing account.
 */
class Controller_Fields_Welcome extends Controller_Fields_Base {
    public function __construct() {
        parent::__construct();
    }

    /**
     * @brief On GET, render the page to ask user to Create account or Login.
     *        On POST, complete login or create account
     */
    public function process() {
        switch ($this->m_operation) {
            case View_Base::CREATE_ACCOUNT:
                $view = new View_Fields_CreateAccount($this, View_Base::WELCOME_PAGE);
                $view->displayPage();
                break;

            case View_Base::SIGN_IN:
                $view = new View_Fields_Login($this, View_Base::WELCOME_PAGE);
                $view->displayPage();
                break;

            case View_Base::SIGN_OUT:
                $this->signOut();
                $view = new View_Fields_Login($this);
                $view->displayPage();
                break;

            default:
                $view = new View_Fields_Welcome($this);
                $view->displayPage();
                break;
        }
    }
}