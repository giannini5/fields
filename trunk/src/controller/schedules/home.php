<?php


/**
 * Class Controller_Schedules_Home
 *
 * @brief Default to Team display since that should be the most frequently visited
 */
class Controller_Schedules_Home extends Controller_Schedules_Base {
    public function __construct() {
        parent::__construct();

        if (isset($_SERVER['REQUEST_METHOD']) and $_SERVER['REQUEST_METHOD'] == 'POST') {
        }
    }

    /**
     * @brief Process the request based on provided filters
     */
    public function process() {
        switch ($this->m_operation) {
            case View_Base::SUBMIT:
            default:
                break;
        }

        // Display Team Schedule page
        $view = new View_Schedules_Team($this);
        $view->displayPage();
    }
}