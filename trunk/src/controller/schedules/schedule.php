<?php

/**
 * Class Controller_Schedules_Schedule
 *
 * @brief Select a field to administer or create a new schedule
 */
class Controller_Schedules_Schedule extends Controller_Schedules_Base {

    public function __construct() {
        parent::__construct();

        if (isset($_SERVER['REQUEST_METHOD']) and $_SERVER['REQUEST_METHOD'] == 'POST') {
            // TODO
        }
    }

    /**
     * @brief On GET, render the page to administer seasons
     *        On POST, complete the transaction and then render the page (with error message if any)
     */
    public function process() {
        if ($this->m_missingAttributes == 0) {
            switch ($this->m_operation) {
                case View_Base::CREATE:
                    // TODO
                    break;

                case View_Base::UPDATE:
                    // TODO
                    break;
            }
        }

        if ($this->m_isAuthenticated) {
            $view = new View_Schedules_Schedule($this);
        } else {
            $view = new View_Schedules_Home($this);
        }

        $view->displayPage();
    }

    /**
     * @brief Create Schedule
     */
    private function _createSchedule() {
        // TODO
    }

    /**
     * @brief Update Schedule
     */
    private function _updateSchedule() {
        // TODO
    }
}