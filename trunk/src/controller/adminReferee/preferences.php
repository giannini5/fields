<?php

use \DAG\Domain\Schedule\Referee;
use \DAG\Domain\Schedule\GameDate;
use \DAG\Domain\Schedule\GameDateReferee;
use \DAG\Domain\Schedule\Division;
use \DAG\Domain\Schedule\DivisionReferee;

/**
 * Class Controller_AdminReferee_Preferences
 *
 * @brief View/Adjust preferences for a referee
 */
class Controller_AdminReferee_Preferences extends Controller_AdminReferee_Base {

    public $m_filterRefereeId;
    public $m_refereeId;
    public $m_maxGamesPerDay;
    public $m_specialInstructions;
    public $m_gameDatesChecked;
    public $m_divisionsChecked;

    public function __construct() {
        parent::__construct();

        if (isset($_SERVER['REQUEST_METHOD']) and $_SERVER['REQUEST_METHOD'] == 'POST') {
            if ($this->m_operation == View_Base::FILTER) {
                $this->m_filterDivisionId = $this->getPostAttribute(View_Base::FILTER_DIVISION_ID, 0, true, true);
                $this->m_filterRefereeId  = $this->getPostAttribute(View_Base::FILTER_REFEREE_ID, 0, true, true);
            }

            if ($this->m_operation == View_Base::UPDATE) {
                $this->m_refereeId              = $this->getPostAttribute(View_Base::REFEREE_ID, 0, true, true);
                $this->m_filterRefereeId        = $this->getPostAttribute(View_Base::FILTER_REFEREE_ID, 0, false, true);
                $this->m_maxGamesPerDay         = $this->getPostAttribute(View_Base::MAX_GAMES_PER_DAY, 0, true, true);
                $this->m_specialInstructions    = $this->getPostAttribute(View_Base::SPECIAL_INSTRUCTIONS, '', false, false);
                $this->m_gameDatesChecked       = $this->getPostAttributeArray(View_Base::GAME_DATES_CHECKED);
                $this->m_divisionsChecked       = $this->getPostAttributeArray(View_Base::DIVISIONS_CHECKED);
            }
        }
    }

    /**
     * @brief On GET, render the page to administer referee
     *        On POST, complete the transaction and then render the page (with error message if any)
     */
    public function process() {
        if ($this->m_isAuthenticated and $this->m_missingAttributes == 0) {
            switch ($this->m_operation) {
                case View_Base::UPDATE:
                    $this->updatePreferences();
                    break;
            }
        }

        if ($this->m_isAuthenticated) {
            $view = new View_AdminReferee_Preferences($this);
        } else {
            $view = new View_AdminReferee_Home($this);
        }

        $view->displayPage();
    }

    /**
     * @brief Update Referee Preferences
     */
    private function updatePreferences() {
        // Verify referee exists
        $referee    = Referee::lookupById((int)$this->m_refereeId);
        $divisions  = Division::lookupBySeason($this->m_season);

        // Update Referee Preferences
        $referee->maxGamesPerDay        = $this->m_maxGamesPerDay;
        $referee->specialInstructions   = $this->m_specialInstructions;

        $this->updateGameDatePreferences($referee);
        $this->updateDivisionsChecked($divisions, $referee);

        $this->m_messageString = "Referee " . $referee->name . " successfully updated.";
    }

    private function updateGameDatePreferences($referee)
    {
        $gameDates = GameDate::lookupBySeason($this->m_season);
        foreach ($gameDates as $gameDate) {
            /** @var GameDateReferee $gameDateReferee */
            $gameDateReferee = null;
            GameDateReferee::findByGameDateAndReferee($gameDate, $referee, $gameDateReferee);

            if (isset($this->m_gameDatesChecked[$gameDate->id])) {
                if (!isset($gameDateReferee)) {
                    GameDateReferee::create($gameDate, $referee);
                }
            } else {
                if (isset($gameDateReferee)) {
                    $gameDateReferee->delete();
                }
            }
        }
    }

    /**
     * @param Division[]    $divisions
     * @param Referee       $referee
     */
    private function updateDivisionsChecked($divisions, $referee)
    {
        foreach ($divisions as $division) {
            /** @var DivisionReferee $divisionReferee */
            $divisionReferee = null;
            DivisionReferee::findByDivisionAndReferee($division, $referee, $divisionReferee);

            if (isset($this->m_divisionsChecked[$division->id])) {
                $isCenter       = isset($this->m_divisionsChecked[$division->id][View_Base::CENTER]);
                $isAssistant    = isset($this->m_divisionsChecked[$division->id][View_Base::ASSISTANT]);
                $isMentor       = isset($this->m_divisionsChecked[$division->id][View_Base::MENTOR]);

                if (!isset($divisionReferee)) {
                    DivisionReferee::create($division, $referee, $isCenter, $isAssistant, $isMentor);
                } else {
                    $divisionReferee->isCenter      = $isCenter;
                    $divisionReferee->isAssistant   = $isAssistant;
                    $divisionReferee->isMentor      = $isMentor;
                }
            } else {
                if (isset($divisionReferee)) {
                    $divisionReferee->delete();
                }
            }
        }
    }
}