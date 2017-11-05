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

    private $m_name;
    private $m_gender;
    private $m_displayOrder;
    private $m_gameDurationMinutes;

    public function __construct() {
        parent::__construct();

        if (isset($_SERVER['REQUEST_METHOD']) and $_SERVER['REQUEST_METHOD'] == 'POST') {
            if ($this->m_operation == View_Base::CREATE) {
                $this->m_name                   = $this->getPostAttribute(View_Base::NAME, '', TRUE);
                $this->m_gender                 = $this->getPostAttribute(View_Base::GENDER, '', TRUE);
                $this->m_displayOrder           = $this->getPostAttribute(View_Base::DISPLAY_ORDER, '', TRUE, TRUE);
                $this->m_gameDurationMinutes    = $this->getPostAttribute(View_Base::GAME_DURATION_MINUTES, '', TRUE, TRUE);
            }

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
                case View_Base::CREATE:
                    $this->createDivision();
                    break;

                case View_Base::UPDATE:
                    $this->updateDivisions();
                    break;
            }
        } else {
            $this->m_errorString = "ERROR: Form missing data.  Make sure you fill in all fields";
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
    private function createDivision() {
        // Verify division does not already exist
        $result = Division::findByNameAndGender($this->m_season, $this->m_name, $this->m_gender, $division);
        if ($result) {
            $this->m_errorString = "Division $this->m_name $this->m_gender already exists.  Use the update control to change information.";
            return;
        }

        // Create the new division
        $division = Division::create($this->m_season, $this->m_name, $this->m_gender, (int)$this->m_gameDurationMinutes, $this->m_displayOrder);
        $this->m_messageString = "Division $division->nameWithGender successfully created";
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
                $division                           = Division::lookupById($divisionId);
                $division->name                     = $data[View_Base::NAME];
                $division->displayOrder             = $data[View_Base::DISPLAY_ORDER];
                $division->scoringTracked           = $data[View_Base::SCORING_TRACKED];
                $division->combineLeagueSchedules   = $data[View_Base::COMBINE_LEAGUE_SCHEDULES];
                $division->minutesBetweenGames      = $data[View_Base::MINUTES_BETWEEN_GAMES];

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