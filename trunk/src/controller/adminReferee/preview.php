<?php

/**
 * Class Controller_AdminReferee_Preview
 *
 * @brief View schedule for referees
 */
class Controller_AdminReferee_Preview extends Controller_AdminSchedules_Base {

    public $m_filterGameDateId;
    public $m_filterDivisionName;

    public function __construct() {
        parent::__construct();

        if (isset($_SERVER['REQUEST_METHOD']) and $_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->m_filterDivisionName = $this->getPostAttribute(View_Base::FILTER_DIVISION_NAME, '', true);
            $this->m_filterGameDateId  = $this->getPostAttribute(View_Base::GAME_DATE_ID, 0, true, true);
        }
    }

    /**
     * @brief On GET, render the page to administer referee
     *        On POST, complete the transaction and then render the page (with error message if any)
     */
    public function process() {
        $view = new View_AdminReferee_Preview($this);
        $view->displayPage();
    }
}