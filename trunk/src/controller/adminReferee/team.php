<?php

/**
 * Class Controller_AdminReferee_Team
 *
 * @brief View/Adjust team for a referee
 */
class Controller_AdminReferee_Team extends Controller_AdminReferee_Base {

    public function __construct() {
        parent::__construct();

        if (isset($_SERVER['REQUEST_METHOD']) and $_SERVER['REQUEST_METHOD'] == 'POST') {
            if ($this->m_operation == View_Base::SUBMIT) {
                $this->m_filterDivisionId = $this->getPostAttribute(View_Base::FILTER_DIVISION_ID, 0, true, true);
            }
        }
    }

    /**
     * @brief On GET, render the page to administer referee
     *        On POST, complete the transaction and then render the page (with error message if any)
     */
    public function process() {
        if ($this->m_isAuthenticated) {
            $view = new View_AdminReferee_Team($this);
        } else {
            $view = new View_AdminReferee_Home($this);
        }

        $view->displayPage();
    }
}