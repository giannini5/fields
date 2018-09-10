<?php

use \DAG\Domain\Schedule\Game;
use \DAG\Domain\Schedule\Division;
use \DAG\Domain\Schedule\GameDate;
use \DAG\Orm\Schedule\ScheduleOrm;
use \DAG\Domain\Schedule\Pool;
use \DAG\Domain\Schedule\Team;
use \DAG\Orm\Schedule\GameOrm;
use \DAG\Framework\Exception\Assertion;
use \DAG\Framework\Exception\Precondition;

/**
 * Class Controller_AdminScoring_Home
 *
 * @brief Controller for scoring
 */
class Controller_AdminScoring_Home extends Controller_AdminScoring_Base
{
    const GAME_SCORING              = 'game';
    const GAME_SCORING_LOOKUP       = 'gameLookup';
    const UPDATE_GAME_SCORING       = 'updateGame';
    const DIVISION_SCORING          = 'division';
    const DIVISION_SCORING_LOOKUP   = 'division';

    public $m_scoringType;
    public $m_gameId;
    public $m_coachId;
    public $m_divisionId;
    public $m_divisionName;
    public $m_division;
    public $m_gameDate;
    public $m_gameDateId;
    public $m_homeTeamId;
    public $m_visitingTeamId;
    public $m_facility;
    public $m_quickScoring = true;

    private $m_gameCardData = [];
    private $m_gameNotes;
    private $m_isTitleGame;

    private $m_homeTeamScore;
    private $m_homeTeamYellowCards;
    private $m_homeTeamRedCards;
    private $m_visitingTeamScore;
    private $m_visitingTeamYellowCards;
    private $m_visitingTeamRedCards;

    public function __construct()
    {
        parent::__construct();

        if (isset($_SERVER['REQUEST_METHOD']) and $_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->m_scoringType    = $this->getPostAttribute(View_Base::SCORING_TYPE, '', false, false);
            $isTitleGameString      = $this->getPostAttribute(View_Base::IS_TITLE_GAME, 'no', false, false);
            $this->m_isTitleGame    = $isTitleGameString == 'no' ? false : true;
            $this->m_quickScoring   = $this->getPostAttribute(View_Base::QUICK_SCORING, false, false, false);

            if ($this->m_scoringType == self::GAME_SCORING or $this->m_scoringType == self::GAME_SCORING_LOOKUP) {
                $this->m_gameId = $this->getPostAttribute(View_Base::GAME_ID, null, true, true);
                $this->populateGameAttributes(true);
            } else if ($this->m_scoringType == self::UPDATE_GAME_SCORING) {
                $this->populateGameAttributes(false);
            } else if ($this->m_scoringType == self::DIVISION_SCORING or $this->m_scoringType == self::DIVISION_SCORING_LOOKUP) {
                $this->m_divisionName   = $this->getPostAttribute(View_Base::DIVISION_NAME, '', false, false);
                $this->m_divisionId     = $this->getPostAttribute(View_Base::FILTER_DIVISION_ID, null, false, true);
                $this->m_gameDateId     = $this->getPostAttribute(View_Base::GAME_DATE, null, true, false);

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

                if ($this->m_isTitleGame) {
                    $this->m_homeTeamId     = $this->getPostAttribute(View_Base::HOME_TEAM_ID, '', true, true);
                    $this->m_visitingTeamId = $this->getPostAttribute(View_Base::VISITING_TEAM_ID, '', true, true);
                    $this->m_gameId         = $this->getPostAttribute(View_Base::GAME_ID, null, true, true);
                    $this->populateGameAttributes(true, false);
                } else {
                    $this->populateGameAttributes(false, true);
                }
            } else {
                $this->m_email = $this->getPostAttribute(
                    Model_Fields_CoachDB::DB_COLUMN_EMAIL,
                    '* Email Address is required'
                );
                $this->m_password = $this->getPostAttribute(
                    Model_Fields_CoachDB::DB_COLUMN_PASSWORD,
                    '* Password is required'
                );
            }
        }
    }

    /**
     * If gameId exist then populate all scoring attributes for game
     *
     * @param bool  $rememberIfGameIdMissing        - If true then error tracked if gameId is missing
     * @param bool  $rememberIfGameDataIsMissing    - If true then error tracked if game data is missing
     */
    private function populateGameAttributes($rememberIfGameIdMissing = true, $rememberIfGameDataIsMissing = true)
    {
        $this->m_gameId = $this->getPostAttribute(View_Base::GAME_ID, null, $rememberIfGameIdMissing, true);
        if (isset($this->m_gameId) and $this->m_quickScoring) {
            $this->m_homeTeamScore           = $this->getPostAttribute(View_Base::HOME_SCORE, NULL, false, true);
            $this->m_homeTeamYellowCards     = $this->getPostAttribute(View_Base::HOME_YELLOW_CARDS, 0, false, true);
            $this->m_homeTeamRedCards        = $this->getPostAttribute(View_Base::HOME_RED_CARDS, 0, false, true);
            $this->m_visitingTeamScore       = $this->getPostAttribute(View_Base::VISITING_SCORE, NULL, false, true);
            $this->m_visitingTeamYellowCards = $this->getPostAttribute(View_Base::VISITING_YELLOW_CARDS, 0, false, true);
            $this->m_visitingTeamRedCards    = $this->getPostAttribute(View_Base::VISITING_RED_CARDS, 0, false, true);
            $this->m_gameNotes               = $this->getPostAttribute(View_Base::GAME_NOTES, '', false, false);
        } else {
            $this->m_gameCardData   = $this->getPostAttributeArray(View_Base::GAME_CARD_DATA);
            $this->m_gameNotes      = $this->getPostAttribute(View_Base::GAME_NOTES, '', false, false);
        }
    }

    /**
     * @brief On GET, render the page to administer seasons
     *        On POST, complete the transaction and then render the page (with error message if any)
     */
    public function process()
    {
        if ($this->m_operation == View_Base::SIGN_OUT) {
            $this->signOut();
        } else if ($this->m_missingAttributes == 0) {
            switch ($this->m_scoringType) {
                case View_Base::SIGN_OUT:
                    $this->signOut();
                    break;

                case self::GAME_SCORING:
                case self::UPDATE_GAME_SCORING:
                    if (isset($this->m_gameId)) {
                        $this->processGameScoring();
                    }
                    break;

                case self::DIVISION_SCORING:
                    if (isset($this->m_gameId)) {
                        $this->processDivisionScoring();
                    }
                    break;

                case self::GAME_SCORING_LOOKUP:
                    $this->m_scoringType = self::GAME_SCORING;
                    break;

                case self::DIVISION_SCORING_LOOKUP:
                    $this->m_scoringType = self::DIVISION_SCORING;
                    break;

                default:
                    $this->_login();
                    break;
            }
        }

        $view = new View_AdminScoring_Home($this);

        $view->displayPage();
    }

    /**
     * Enter score for a game
     */
    private function processGameScoring()
    {
        /** @var Game $game */
        $game = null;

        if (!Game::findById((int)$this->m_gameId, $game)) {
            $this->m_errorString = "Game with id $this->m_gameId not found";
            return;
        }

        $operation          = isset($game->homeTeamScore) ? "updated" : "entered";
        $titleGameMessage   = "";

        if ($this->m_operation == View_Base::CLEAR) {
            $game->clearStats();
            $operation = 'cleared';
        } else {
            if ($this->m_quickScoring) {
                if (isset($this->m_homeTeamScore)) {
                    $game->homeTeamScore            = $this->m_homeTeamScore;
                    $game->homeTeamYellowCards      = $this->m_homeTeamYellowCards;
                    $game->homeTeamRedCards         = $this->m_homeTeamRedCards;
                    $game->visitingTeamScore        = $this->m_visitingTeamScore;
                    $game->visitingTeamYellowCards  = $this->m_visitingTeamYellowCards;
                    $game->visitingTeamRedCards     = $this->m_visitingTeamRedCards;
                    $game->notes                    = $this->m_gameNotes;
                } else {
                    return;
                }
            } else {
                $game->setStats($this->m_gameCardData);
            }

            // Populate title games if any and all flight games are complete
            $result = $this->populateTitleGames($game);
            $titleGameMessage = '';
            if ($result == 1) {
                $titleGameMessage = "<br>Medal Round Games Populated with Teams";
            } else if ($result == 2) {
                $titleGameMessage = "<br>Medal Round Games Ready for Population, but tie-breaker rules need to be consulted.  Manual population required.";
            }
        }

        $this->m_messageString = "Score $operation for game $this->m_gameId" . $titleGameMessage;
    }

    /**
     * Set the teams and enter or Update score for a title game
     */
    private function processTitleGameScoring()
    {
        if (!Game::findById((int)$this->m_gameId, $game)) {
            $this->m_errorString = "Game with id $this->m_gameId not found";
            return;
        }

        if ($this->m_operation == View_Base::CLEAR) {
            $game->homeTeamScore            = null;
            $game->homeTeamYellowCards      = 0;
            $game->homeTeamRedCards         = 0;
            $game->visitingTeamScore        = null;
            $game->visitingTeamYellowCards  = 0;
            $game->visitingTeamRedCards     = 0;
            $game->notes                    = '';
        } else {
            // Set the teams
            $homeTeam           = Team::lookupById((int)$this->m_homeTeamId);
            $visitingTeam       = Team::lookupById((int)$this->m_visitingTeamId);
            $game->homeTeam     = $homeTeam;
            $game->visitingTeam = $visitingTeam;

            // Enter/Update games scores if passed along in request
            if ($this->m_homeScore != '') {
                $this->processGameScoring();
            }
        }
    }

    /**
     * Update scores for division
     */
    private function processDivisionScoring()
    {
        if ($this->m_isTitleGame) {
            $this->processTitleGameScoring();
        } else {
            $this->processGameScoring();
        }
    }

    /**
     * @param Game  $game   - Game that was just scored
     *
     * @return int  0 if title games not ready to populate
     *              1 if title games populated
     *              2 if title games ready to populate, but there is a tie in points
     */
    private function populateTitleGames($game)
    {
        // Skip if schedule does not support tournament/bracket play
        if ($game->flight->schedule->scheduleType != ScheduleOrm::SCHEDULE_TYPE_TOURNAMENT
            and $game->flight->schedule->scheduleType != ScheduleOrm::SCHEDULE_TYPE_BRACKET) {
            return 0;
        }

        // If bracket tournament then use bracket rules to populate title game(s)
        if ($game->flight->schedule->scheduleType == ScheduleOrm::SCHEDULE_TYPE_BRACKET) {
            return $this->populateBracketGames($game);
        }

        // Skip if this is a title game that does not require follow-on scheduling
        // Simi-final games require follow-on scheduling
        if ($game->title != '' and $game->title != GameOrm::TITLE_SEMI_FINAL) {
            return 0;
        }

        // Skip if flight games are not 100% complete
        $titleGames     = [];
        $semiFinalGames = [];
        $games          = Game::lookupByFlight($game->flight);
        foreach ($games as $tGame) {
            if ($tGame->title != '') {
                if ($tGame->title == GameOrm::TITLE_SEMI_FINAL) {
                    $semiFinalGames[] = $tGame;
                } else {
                    $titleGames[$tGame->title] = $tGame;
                }
                continue;
            }

            if (!isset($tGame->homeTeamScore)) {
                return 0;
            }
        }

        // Skip if flight does not have any title games
        if (count($titleGames) == 0) {
            return 0;
        }

        // Get teams by pool with points
        $pools = Pool::lookupByFlight($game->flight);
        $standingsByPoolByPoints    = [];
        $teamsById                  = [];
        foreach ($pools as $pool) {
            $standingsByPoints  = [];
            $teams              = Team::lookupByPool($pool);

            foreach ($teams as $team) {
                $teamsById[$team->id]                       = $team;
                $points                                     = $team->getPoints($games);
                // TODO: Deal better with ties here!!!
                if (isset($standingsByPoints["$points"])) {
                    // Bail for now.
                    return 2;
                }
                $standingsByPoints["$points"] = $team->id;
            }

            krsort($standingsByPoints);
            $standingsByPoolByPoints[$pool->id] = $standingsByPoints;
        }

        Assertion::isTrue(count($standingsByPoolByPoints) == 2,
            "Support only implemented for flights with two pools. This flight has " . count($standingsByPoolByPoints) . " pools.");

        $teamsByPool = [];
        foreach ($standingsByPoolByPoints as $poolId => $standingsByPoints) {
            foreach ($standingsByPoints as $points => $teamId) {
                $teamsByPool[$poolId][] = $teamsById[$teamId];
            }
        }

        // Get Semi-Final games
        $returnValue = 0;
        if (count($semiFinalGames) > 0) {
            Assertion::isTrue(count($semiFinalGames) == 2, "Incorrect number of semi-final games found: " . count($semiFinalGames));

            $semiFinalTeams = [];
            foreach ($teamsByPool as $poolId => $teams) {
                $semiFinalTeams[] = $teams[0];
                $semiFinalTeams[] = $teams[1];
            }
            Assertion::isTrue(count($semiFinalTeams) == 4, "Incorrect number of semi-final teams found: " . count($semiFinalTeams));

            // Populate with first from pool1 vs second from pool2
            $semiFinalGames[0]->homeTeam        = $semiFinalTeams[0];
            $semiFinalGames[0]->visitingTeam    = $semiFinalTeams[3];

            // Populate with first from pool2 vs second from pool1
            $semiFinalGames[1]->homeTeam        = $semiFinalTeams[2];
            $semiFinalGames[1]->visitingTeam    = $semiFinalTeams[1];

            $returnValue = 1;
        }

        // Populate 5th/6th title game if any
        foreach ($titleGames as $titleGame) {
            switch ($titleGame->title) {
                case GameOrm::TITLE_5TH_6TH:
                    // Populate 5th/6th game with third place finisher in pool1 and pool2
                    $thirdPlaceTeams            = $this->getTeamsForTitleGame($teamsByPool, $titleGame->title, 2);
                    $titleGame->homeTeam        = $thirdPlaceTeams[0];
                    $titleGame->visitingTeam    = $thirdPlaceTeams[1];
                    $returnValue = 1;
                    break;

                case GameOrm::TITLE_3RD_4TH:
                case GameOrm::TITLE_CHAMPIONSHIP:
                    break;

                default:
                    Assertion::isTrue(false, "$titleGame->title not supported");
            }
        }

        $bracketReturnValue = $this->populateBracketGames($game);
        return $bracketReturnValue > $returnValue ? $bracketReturnValue : $returnValue;
    }

    /**
     * @param Game  $game   - Game that was just scored
     *
     * @return int
     *              1 if title games populated
     *              2 if title games ready to populate, but there is a tie
     */
    private function populateBracketGames($game)
    {
        $returnValue = 0;

        // Return if game is a tie
        if ($game->homeTeamScore == $game->visitingTeamScore) {
            return 0;
        }

        // Get winning and losing teams
        $winningTeam    = $game->homeTeamScore > $game->visitingTeamScore ? $game->homeTeam : $game->visitingTeam;
        $losingTeam     = $game->homeTeamScore > $game->visitingTeamScore ? $game->visitingTeam : $game->homeTeam;

        // Find and populate game for winning team
        $titleGame = null;
        if (Game::findByPlayInGame($game, 1, $titleGame)) {
            if ($titleGame->playInHomeGameId == $game->id) {
                $titleGame->homeTeam = $winningTeam;
            } else {
                $titleGame->visitingTeam = $winningTeam;
            }
            $returnValue = 1;
        }

        // Find and populate game for losing team
        $titleGame = null;
        if (Game::findByPlayInGame($game, 0, $titleGame)) {
            if ($titleGame->playInHomeGameId == $game->id) {
                $titleGame->homeTeam = $losingTeam;
            } else {
                $titleGame->visitingTeam = $losingTeam;
            }
            $returnValue = 1;
        }

        return $returnValue;
    }

    /**
     * @param array     $teamsByPoolSortedByPoints
     * @param string    $gameTitle
     * @param int       $teamIndex  - index into $teamsByPoolSortedByPoints's teams array for teams to return
     *
     * @return Team[]   Array of two Team entries
     */
    private function getTeamsForTitleGame($teamsByPoolSortedByPoints, $gameTitle, $teamIndex)
    {
        Precondition::isTrue(count($teamsByPoolSortedByPoints) == 2, "Incorrect number of pools for $gameTitle teams: " . count($teamsByPoolSortedByPoints));

        $teams = [];
        foreach ($teamsByPoolSortedByPoints as $poolId => $teamsSortedByPoints) {
            $teams[] = $teamsSortedByPoints[$teamIndex];
        }

        Assertion::isTrue(count($teams) == 2, "Incorrect number of $gameTitle teams found: " . count($teams));
        return $teams;
    }

    /**
     * @param Game[]    $semiFinalGames
     *
     * @return bool     true if all games have been scored; false otherwise
     */
    private function areSemiFinalGamesScored($semiFinalGames)
    {
        foreach ($semiFinalGames as $semiFinalGame) {
            if (!isset($semiFinalGame->homeTeamScore)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param Game[]    $semiFinalGames
     *
     * @return Team[]   Semi final game losers
     */
    private function getSemiFinalGameLosers($semiFinalGames)
    {
        $teams = [];

        foreach ($semiFinalGames as $semiFinalGame) {
            if ($semiFinalGame->homeTeamScore > $semiFinalGame->visitingTeamScore) {
                $teams[] = $semiFinalGame->visitingTeam;
            } else if ($semiFinalGame->homeTeamScore < $semiFinalGame->visitingTeamScore) {
                $teams[] = $semiFinalGame->homeTeam;
            }
        }

        // TODO: Cleaner error message here to support case where scorer accidentally enters a tie
        Assertion::isTrue(count($teams) == 2,
            "Incorrect number of loser Semi-Final teams found (did scorer enter a tie for the semi-final games?): " . count($teams));

        return $teams;
    }

    /**
     * @param Game[]    $semiFinalGames
     *
     * @return Team[]   Semi final game winners
     */
    private function getSemiFinalGameWinners($semiFinalGames)
    {
        $teams = [];

        foreach ($semiFinalGames as $semiFinalGame) {
            if ($semiFinalGame->homeTeamScore > $semiFinalGame->visitingTeamScore) {
                $teams[] = $semiFinalGame->homeTeam;
            } else if ($semiFinalGame->homeTeamScore < $semiFinalGame->visitingTeamScore) {
                $teams[] = $semiFinalGame->visitingTeam;
            }
        }

        // TODO: Cleaner error message here to support case where scorer accidentally enters a tie
        Assertion::isTrue(count($teams) == 2,
            "Incorrect number of loser Semi-Final teams found (did scorer enter a tie for the semi-final games?): " . count($teams));

        return $teams;
    }
}
