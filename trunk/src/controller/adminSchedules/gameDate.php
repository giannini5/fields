<?php

use \DAG\Domain\Schedule\GameDate;
use \DAG\Domain\Schedule\GameTime;
use \DAG\Domain\Schedule\DivisionField;
use \DAG\Domain\Schedule\Division;

/**
 * Class Controller_AdminSchedules_GameDate
 *
 * @brief Select a gameDate to administer or create a new gameDate
 */
class Controller_AdminSchedules_GameDate extends Controller_AdminSchedules_Base {
    public $m_day           = NULL;
    public $m_gameDateIds   = [];
    public $m_divisionNames = [];

    public function __construct() {
        parent::__construct();

        if (isset($_SERVER['REQUEST_METHOD']) and $_SERVER['REQUEST_METHOD'] == 'POST') {
            if ($this->m_operation == View_Base::CREATE) {
                $this->m_day = $this->getPostAttribute(
                    View_Base::DAY,
                    'YYYY-MM-DD',
                    true,
                    false,
                    "* day required");
            }

            if ($this->m_operation == View_Base::DELETE) {
                $this->m_gameDateIds = $this->getPostAttributeArray(
                    View_Base::GAME_DATES
                );
                if (count($this->m_gameDateIds) == 0) {
                    $this->setErrorString("Error: Game Date(s) is a Required Field");
                }
            }

            if ($this->m_operation == View_Base::REMOVE) {
                $this->m_gameDateId     = $this->getPostAttribute(
                    View_Base::GAME_DATE,
                    null,
                    true,
                    false);

                $this->m_divisionNames = $this->getPostAttributeArray(
                    View_Base::DIVISION_NAMES
                );
                if (count($this->m_divisionNames) == 0) {
                    $this->setErrorString("Error: Division Name(s) is a Required Field");
                }
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
                    $this->_deleteGameDates();
                    break;

                case View_Base::REMOVE:
                    $this->removeGameDateForDivisions();
                    break;
            }
        }

        if ($this->m_isAuthenticated) {
            $view = new View_AdminSchedules_GameDate($this);
        } else {
            $view = new View_AdminSchedules_Home($this);
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
     * @brief Delete GameDate
     */
    private function _deleteGameDates() {
        $gameDatesString    = '';
        $gameDates          = [];

        // Verify games have not been assigned
        foreach ($this->m_gameDateIds as $gameDateId) {
            $gameDate       = GameDate::lookupById($gameDateId);
            $gameDates[]    = $gameDate;

            $gameTimes = GameTime::lookupByGameDate($gameDate);
            foreach ($gameTimes as $gameTime) {
                if (isset($gameTime->game)) {
                    $this->m_errorString = "Games have already been set.  You must delete the schedule before you can remove game dates";
                    return;
                }
            }

        }

        // Delete the game dates
        foreach ($gameDates as $gameDate) {
            $gameDatesString .= empty($gameDatesString) ? $gameDate->day : ", " . $gameDate->day;
            $gameDate->delete();
        }

        $this->m_messageString = "Game Date(s) successfully deleted for: $gameDatesString.";
    }

    /**
     * @brief Remove GameDate for specified divisions
     */
    private function removeGameDateForDivisions() {
        $divisionNames  = '';
        $gameDate       = GameDate::lookupById((int)$this->m_gameDateId);
        $divisions      = [];

        foreach ($this->m_divisionNames as $divisionName) {
            $newDivisions = Division::lookupByName($this->m_season, $divisionName);
            $divisions = array_merge($divisions, $newDivisions);
        }

        // Verify games have not already been set
        foreach ($divisions as $division) {
            $divisionFields = DivisionField::lookupByDivision($division);
            foreach ($divisionFields as $divisionField) {
                $field = $divisionField->field;

                if ($field->gamesExists(null, array($gameDate))) {
                    $this->m_errorString = "Games have already been set.  You must delete the schedule before you can remove game dates";
                    return;
                }
            }

            $divisionNames .= empty($divisionNames) ? $division->nameWithGender : ", " . $division->nameWithGender;
        }

        // Delete game times for division fields.  Exception thrown if a gameTime has an assigned game
        foreach ($divisions as $division) {
            $divisionFields = DivisionField::lookupByDivision($division);
            foreach ($divisionFields as $divisionField) {
                $field = $divisionField->field;
                $field->deleteGameTimes(true, $gameDate);
            }
        }

        $this->m_messageString = "Game Date '$gameDate->day' successfully removed for divisions: $divisionNames.";
    }
}