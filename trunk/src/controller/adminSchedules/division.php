<?php

use \DAG\Domain\Schedule\Division;
use \DAG\Services\MySql\DuplicateKeyException;
use \DAG\Domain\Schedule\GameDate;
use \DAG\Domain\Schedule\GameTime;
use \DAG\Domain\Schedule\Facility;
use \DAG\Domain\Schedule\Field;

/**
 * Class Controller_AdminSchedules_Division
 *
 * @brief Select a field to administer or create a new division
 */
class Controller_AdminSchedules_Division extends Controller_AdminSchedules_Base {

    private $m_divisionUpdates;

    public function __construct() {
        parent::__construct();

        if (isset($_SERVER['REQUEST_METHOD']) and $_SERVER['REQUEST_METHOD'] == 'POST') {
            if ($this->m_operation == View_Base::UPDATE) {
                $this->m_divisionUpdates = $this->getPostAttributeArray(View_Base::DIVISION_UPDATE_DATA);
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
                case View_Base::UPDATE:
                    $this->updateDivisions();
                    break;
            }
        }

        if ($this->m_isAuthenticated) {
            $view = new View_AdminSchedules_Division($this);
        } else {
            $view = new View_AdminSchedules_Home($this);
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
     * @brief Update Divisions
     */
    private function updateDivisions() {
        try {
            $anyGameTimesSet    = false;
            $anyGamesSet        = false;
            $gameDates          = GameDate::lookupBySeason($this->m_season);
            if (count($gameDates) > 0) {
                $gameTimes          = GameTime::lookupByGameDate($gameDates[0]);
                $anyGameTimesSet    = count($gameTimes) > 0;
                foreach ($gameTimes as $gameTime) {
                    if (isset($gameTime->game)) {
                        $anyGamesSet = true;
                        break;
                    }
                }
            }

            foreach ($this->m_divisionUpdates as $divisionId => $data) {
                $division                       = Division::lookupById($divisionId);
                $division->name                 = $data[View_Base::NAME];
                $division->displayOrder         = $data[View_Base::DISPLAY_ORDER];

                if (!$anyGamesSet) {
                    $division->gameDurationMinutes  = $data[View_Base::GAME_DURATION_MINUTES];
                }
            }

            // Re-create game times if they existed prior and no games have been set
            if (!$anyGamesSet and $anyGameTimesSet) {
                $facilities = Facility::lookupBySeason($this->m_season);
                foreach ($facilities as $facility) {
                    $fields = Field::lookupByFacility($facility);
                    foreach ($fields as $field) {
                        $this->m_season->createGameTimes($field, true);
                    }
                }
            }

            $this->m_messageString  = "Division meta data successfully updated.";
            if ($anyGamesSet) {
                $this->m_messageString .= " FIELD USE MINUTES NOT CHANGED SINCE GAMES HAVE ALREADY BEEN CREATED.";
            }
        } catch (DuplicateKeyException $e) {
            $this->m_errorString = "Sorry, updated failed, two divisions cannot have the same name: " . $e->getMessage();
        }
    }
}