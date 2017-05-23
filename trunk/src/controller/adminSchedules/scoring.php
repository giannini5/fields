<?php

use \DAG\Domain\Schedule\Game;
use \DAG\Domain\Schedule\Division;
use \DAG\Domain\Schedule\GameDate;
use \DAG\Orm\Schedule\ScheduleOrm;
use \DAG\Domain\Schedule\Pool;
use \DAG\Domain\Schedule\Team;
use \DAG\Orm\Schedule\GameOrm;
use \DAG\Framework\Exception\Assertion;

/**
 * Class Controller_AdminSchedules_Scoring
 *
 * @brief Controller for game scoring
 */
class Controller_AdminSchedules_Scoring extends Controller_AdminSchedules_Base
{

    const GAME_SCORING      = 'game';
    const TEAM_SCORING      = 'team';
    const DIVISION_SCORING  = 'division';

    public $m_scoringType;
    public $m_gameId;
    public $m_coachId;
    public $m_divisionId;
    public $m_divisionName;
    public $m_division;
    public $m_gameDate;
    public $m_gameDateId;

    private $m_homeScore;
    private $m_homeRedCards;
    private $m_homeYellowCards;
    private $m_visitScore;
    private $m_visitRedCards;
    private $m_visitYellowCards;
    private $m_gameNotes;


    public function __construct()
    {
        parent::__construct();

        if (isset($_SERVER['REQUEST_METHOD']) and $_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->m_scoringType = $this->getPostAttribute(View_Base::SCORING_TYPE, '', true, false);

            if ($this->m_scoringType == self::TEAM_SCORING) {
                $this->m_coachId = $this->getPostAttribute(View_Base::FILTER_COACH_ID, null, false, true);

                $this->populateGameAttributes(false);
            } else if ($this->m_scoringType == self::GAME_SCORING) {
                $this->populateGameAttributes(true);
            } else if ($this->m_scoringType == self::DIVISION_SCORING) {
                $this->m_divisionName   = $this->getPostAttribute(View_Base::DIVISION_NAME, '', false, false);
                $this->m_divisionId     = $this->getPostAttribute(View_Base::FILTER_DIVISION_ID, null, false, true);
                $this->m_gameDateId     = $this->getPostAttribute(View_Base::GAME_DATE, null, true, false);

                if (isset($this->m_divisionId)) {
                    $this->m_division       = Division::lookupById($this->m_divisionId);
                    $this->m_divisionName   = $this->m_division->nameWithGender;
                } else {
                    $divisionNameAttributes = explode(' ', $this->m_divisionName);
                    if (count($divisionNameAttributes) == 2) {
                        $this->m_division = Division::lookupByNameAndGender($this->m_season, $divisionNameAttributes[0], $divisionNameAttributes[1]);
                    }
                }

                if (isset($this->m_gameDateId)) {
                    $this->m_gameDate = GameDate::lookupById($this->m_gameDateId);
                }

                $this->populateGameAttributes(false);
            }
        }
    }

    /**
     * If gameId exist then populate all scoring attributes for game
     *
     * @param bool  $rememberIfGameIdMissing - If true then error tracked if gameId is missing
     */
    private function populateGameAttributes($rememberIfGameIdMissing = true)
    {
        $this->m_gameId = $this->getPostAttribute(View_Base::GAME_ID, null, $rememberIfGameIdMissing, true);
        if (isset($this->m_gameId)) {
            $this->m_homeScore = $this->getPostAttribute(View_Base::HOME_SCORE, '', true, true);
            $this->m_homeRedCards = $this->getPostAttribute(View_Base::HOME_RED_CARDS, '', true, true);
            $this->m_homeYellowCards = $this->getPostAttribute(View_Base::HOME_YELLOW_CARDS, '', true, true);
            $this->m_visitScore = $this->getPostAttribute(View_Base::VISITING_SCORE, '', true, true);
            $this->m_visitRedCards = $this->getPostAttribute(View_Base::VISITING_RED_CARDS, '', true, true);
            $this->m_visitYellowCards = $this->getPostAttribute(View_Base::VISITING_YELLOW_CARDS, '', true, true);
            $this->m_gameNotes = $this->getPostAttribute(View_Base::GAME_NOTES, '', false, false);
        }
    }

    /**
     * @brief On GET, render the page to administer seasons
     *        On POST, complete the transaction and then render the page (with error message if any)
     */
    public function process()
    {
        if ($this->m_missingAttributes == 0) {
            switch ($this->m_scoringType) {
                case self::GAME_SCORING:
                    $this->processGameScoring();
                    break;

                case self::TEAM_SCORING:
                    $this->processTeamScoring();
                    break;

                case self::DIVISION_SCORING:
                    $this->processDivisionScoring();
                    break;
            }
        }

        if ($this->m_isAuthenticated) {
            $view = new View_AdminSchedules_Scoring($this);
        } else {
            $view = new View_AdminSchedules_Home($this);
        }

        $view->displayPage();
    }

    /**
     * Enter score for a game
     *
     * @param bool  $allowUpdate - if true then updates to game score allowed
     */
    private function processGameScoring($allowUpdate = false)
    {
        if (!Game::findById($this->m_gameId, $game)) {
            $this->m_errorString = "Game with id $this->m_gameId not found";
            return;
        }

        if (isset($game->homeTeamScore) and !$allowUpdate) {
            $this->m_errorString = "Results already entered for game with id $this->m_gameId.  Do you want to update?";
            return;
        }

        $operation = isset($game->homeTeamScore) ? "updated" : "entered";

        $game->homeTeamScore            = $this->m_homeScore;
        $game->homeTeamYellowCards      = $this->m_homeYellowCards;
        $game->homeTeamRedCards         = $this->m_homeRedCards;
        $game->visitingTeamScore        = $this->m_visitScore;
        $game->visitingTeamYellowCards  = $this->m_visitYellowCards;
        $game->visitingTeamRedCards     = $this->m_visitRedCards;
        $game->notes                    = $this->m_gameNotes;

        // Populate title games if any and all flight games are complete
        $this->populateTitleGame($game);

        $this->m_messageString = "Result $operation for game $this->m_gameId";
    }

    /**
     * Enter or Update score for a game
     */
    private function processTeamScoring()
    {
        if (isset($this->m_gameId)) {
            if ($this->m_operation == View_Base::CLEAR) {
                if (!Game::findById($this->m_gameId, $game)) {
                    $this->m_errorString = "Game with id $this->m_gameId not found";
                    return;
                }

                $game->homeTeamScore            = null;
                $game->homeTeamYellowCards      = 0;
                $game->homeTeamRedCards         = 0;
                $game->visitingTeamScore        = null;
                $game->visitingTeamYellowCards  = 0;
                $game->visitingTeamRedCards     = 0;
                $game->notes                    = '';
            } else {
                $this->processGameScoring();
            }
        }
    }

    /**
     * Update scores for division
     */
    private function processDivisionScoring()
    {
        $this->processTeamScoring();
    }

    /**
     * @param Game  $game
     */
    private function populateTitleGame($game)
    {
        // Skip if this is a title game
        if ($game->title != '') {
            return;
        }

        // Skip if schedule does not support tournament play
        if ($game->flight->schedule->scheduleType != ScheduleOrm::SCHEDULE_TYPE_TOURNAMENT) {
            return;
        }

        // Skip if flight games are not 100% complete
        $titleGames = [];
        $games = Game::lookupByFlight($game->flight);
        foreach ($games as $game) {
            if ($game->title != '') {
                $titleGames[$game->title] = $game;
                break;
            }

            if (!isset($game->homeTeamScore)) {
                return;
            }
        }

        // Get teams by pool with points
        $pools = Pool::lookupByFlight($game->flight);
        $standingsByPoolByPoints = [];
        $standingsByPoints = [];
        foreach ($pools as $pool) {
            $teams = Team::lookupByPool($pool);

            foreach ($teams as $team) {
                $points = $team->getPoints($games);
                $standingsByPoints[$points][$team->id][] = $team;
            }

            krsort($standingsByPoints);
            $standingsByPoolByPoints[$pool->id] = $standingsByPoints;
        }

        // Populate title games
        foreach ($titleGames as $titleGame) {
            switch ($titleGame->title) {
                case GameOrm::TITLE_5TH_6TH:
                    break;

                case GameOrm::TITLE_3RD_4TH:
                    break;

                case GameOrm::TITLE_CHAMPIONSHIP:
                    break;

                default:
                    Assertion::isTrue(false, "$titleGame->title not supported");
            }

            // How to deal with ties???
        }

    }
}