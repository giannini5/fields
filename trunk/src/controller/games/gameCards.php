<?php

use \DAG\Domain\Schedule\Division;
use \DAG\Domain\Schedule\GameDate;

/**
 * Class Controller_Games_GameCards
 *
 * @brief Controller displaying game cards
 */
class Controller_Games_GameCards extends Controller_Games_Base
{
    const GAME              = 'game';
    const DIVISION_GAMES    = 'divisionGames';

    public $m_scoringType;
    public $m_gameId;
    public $m_divisionId;
    public $m_divisionName;
    public $m_division;
    public $m_gameDate;
    public $m_gameDateId;

    public function __construct()
    {
        parent::__construct();

        if (isset($_SERVER['REQUEST_METHOD']) and $_SERVER['REQUEST_METHOD'] == 'GET') {
            $this->m_scoringType = $this->getRequestAttribute(View_Base::SCORING_TYPE, '');
            $this->m_isPopup     = $this->getRequestAttribute(View_Base::POPUP, false);

            if ($this->m_scoringType == self::GAME) {
                $this->m_gameId = $this->getRequestAttribute(View_Base::GAME_ID, null);
            } else if ($this->m_scoringType == self::DIVISION_GAMES) {
                $this->m_divisionName   = $this->getRequestAttribute(View_Base::DIVISION_NAME, '');
                $this->m_divisionId     = $this->getRequestAttribute(View_Base::FILTER_DIVISION_ID, null);
                $this->m_gameDateId     = $this->getRequestAttribute(View_Base::GAME_DATE, null);

                if (isset($this->m_divisionId)) {
                    $this->m_division       = Division::lookupById((int)$this->m_divisionId);
                    $this->m_divisionName   = $this->m_division->nameWithGender;
                } else {
                    $divisionNameAttributes = explode(' ', $this->m_divisionName);
                    if (count($divisionNameAttributes) == 2) {
                        $this->m_division = Division::lookupByNameAndGender($this->m_season, $divisionNameAttributes[0], $divisionNameAttributes[1]);
                    }
                }

                if (isset($this->m_gameDateId)) {
                    $this->m_gameDate = GameDate::lookupById((int)$this->m_gameDateId);
                }
            }
        }
    }

    /**
     * @brief On GET/POST, render the page to display game card(s)
     */
    public function process()
    {
        $view = new View_Games_GameCards($this);
        $view->displayPage();
    }
}
