<?php

use \DAG\Domain\Schedule\GameDate;

/**
 * Class Controller_Schedules_GameDate
 *
 * @brief Select a gameDate to administer or create a new gameDate
 */
class Controller_Schedules_GameDate extends Controller_Schedules_Base {
    public $m_day           = NULL;
    public $m_gameDateId    = NULL;

    public function __construct() {
        parent::__construct();

        if (isset($_SERVER['REQUEST_METHOD']) and $_SERVER['REQUEST_METHOD'] == 'POST') {
            if ($this->m_operation == View_Base::CREATE) {
                $this->m_day = $this->getPostAttribute(
                    View_Base::DAY,
                    'YYYY-MM-DD',
                    true,
                    false,
                    "* day required"
                );
            }

            if ($this->m_operation == View_Base::DELETE) {
                $this->m_gameDateId = $this->getPostAttribute(
                    View_Base::GAME_DATE_ID,
                    null,
                    false,
                    true
                );
            }
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
                    $this->_createGameDate();
                    break;

                case View_Base::DELETE:
                    $this->_deleteGameDate();
                    break;
            }
        }

        if ($this->m_isAuthenticated) {
            $view = new View_Schedules_GameDate($this);
        } else {
            $view = new View_Schedules_Home($this);
        }

        $view->displayPage();
    }

    /**
     * @brief Create GameDate
     */
    private function _createGameDate() {
        $gameDate = GameDate::create(
            $this->m_season,
            $this->m_day);

        $this->m_messageString = "Game Date '$gameDate->day' successfully created.";
    }

    /**
     * @brief Update GameDate
     */
    private function _deleteGameDate() {
        $gameDate = GameDate::lookupById($this->m_gameDateId);
        $gameDate->delete();

        $this->m_messageString = "Game Date '$gameDate->day' successfully deleted (Warning, not cascading).";
    }
}