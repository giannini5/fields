<?php

/**
 * Class Controller_Schedules_Division
 *
 * @brief Select a field to administer or create a new division
 */
class Controller_Schedules_Division extends Controller_Schedules_Base {

    public function __construct() {
        parent::__construct();
    }

    /**
     * @brief On GET, render the page to administer seasons
     *        On POST, complete the transaction and then render the page (with error message if any)
     */
    public function process() {
        if ($this->m_missingAttributes == 0) {
            switch ($this->m_operation) {
                case View_Base::UPDATE:
                    // TODO
                    break;
            }
        }

        if ($this->m_isAuthenticated) {
            $view = new View_Schedules_Division($this);
        } else {
            $view = new View_Schedules_Home($this);
        }

        $view->displayPage();
    }

    /**
     * @brief Create Division
     */
    private function _createDivision() {
        // TODO
    }

    /**
     * @brief Update Division
     */
    private function _updateDivision() {
        // TODO
    }
}