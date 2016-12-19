<?php

/**
 * Class Controller_Schedules_Team
 *
 * @brief Select a field to administer or create a new team
 */
class Controller_Schedules_Team extends Controller_Schedules_Base {

    public $m_showPlayers = false;

    public function __construct() {
        parent::__construct();

        if (isset($_SERVER['REQUEST_METHOD']) and $_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->m_filterDivisionId = $this->getPostAttribute(View_Base::FILTER_DIVISION_ID, 0);
            $this->m_filterTeamId     = $this->getPostAttribute(View_Base::FILTER_TEAM_ID, 0);
            $this->m_filterCoachId    = $this->getPostAttribute(View_Base::FILTER_COACH_ID, 0);
            $this->m_showPlayers      = $this->getPostCheckboxAttribute(View_Base::SHOW_PLAYERS, false);
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
            $view = new View_Schedules_Team($this);
        } else {
            $view = new View_Schedules_Home($this);
        }

        $view->displayPage();
    }

    /**
     * @brief Create Team
     */
    private function _createTeam() {
        // TODO
    }

    /**
     * @brief Update Team
     */
    private function _updateTeam() {
        // TODO
    }
}