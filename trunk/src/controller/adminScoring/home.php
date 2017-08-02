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
    const GAME_SCORING      = 'game';
    const TEAM_SCORING      = 'team';
    const DIVISION_SCORING  = 'division';
    const VOLUNTEER_POINTS  = 'volunteerPoints';

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

    private $m_homeScore;
    private $m_homeRedCards;
    private $m_homeYellowCards;
    private $m_visitScore;
    private $m_visitRedCards;
    private $m_visitYellowCards;
    private $m_gameNotes;
    private $m_isTitleGame;
    private $m_volunteerPointsData = [];

    public function __construct()
    {
        parent::__construct();

        if (isset($_SERVER['REQUEST_METHOD']) and $_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->m_scoringType    = $this->getPostAttribute(View_Base::SCORING_TYPE, '', false, false);
            $isTitleGameString      = $this->getPostAttribute(View_Base::IS_TITLE_GAME, 'no', false, false);
            $this->m_isTitleGame    = $isTitleGameString == 'no' ? false : true;

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

                if ($this->m_isTitleGame) {
                    $this->m_homeTeamId     = $this->getPostAttribute(View_Base::HOME_TEAM_ID, '', true, true);
                    $this->m_visitingTeamId = $this->getPostAttribute(View_Base::VISITING_TEAM_ID, '', true, true);
                    $this->m_gameId         = $this->getPostAttribute(View_Base::GAME_ID, null, true, true);
                    $this->populateGameAttributes(true, false);
                } else {
                    $this->populateGameAttributes(false, true);
                }
            } else if ($this->m_scoringType == self::VOLUNTEER_POINTS) {
                $this->m_divisionName   = $this->getPostAttribute(View_Base::DIVISION_NAME, '', false, false);
                $divisionNameAttributes = explode(' ', $this->m_divisionName);
                if (count($divisionNameAttributes) == 2) {
                    $this->m_division               = Division::lookupByNameAndGender($this->m_season, $divisionNameAttributes[0], $divisionNameAttributes[1]);
                    $this->m_volunteerPointsData    = $this->getPostAttributeArray(View_Base::VOLUNTEER_POINTS_DATA);
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
        if (isset($this->m_gameId)) {
            $this->m_homeScore = $this->getPostAttribute(View_Base::HOME_SCORE, '', $rememberIfGameDataIsMissing, true);
            $this->m_homeRedCards = $this->getPostAttribute(View_Base::HOME_RED_CARDS, '', $rememberIfGameDataIsMissing, true);
            $this->m_homeYellowCards = $this->getPostAttribute(View_Base::HOME_YELLOW_CARDS, '', $rememberIfGameDataIsMissing, true);
            $this->m_visitScore = $this->getPostAttribute(View_Base::VISITING_SCORE, '', $rememberIfGameDataIsMissing, true);
            $this->m_visitRedCards = $this->getPostAttribute(View_Base::VISITING_RED_CARDS, '', $rememberIfGameDataIsMissing, true);
            $this->m_visitYellowCards = $this->getPostAttribute(View_Base::VISITING_YELLOW_CARDS, '', $rememberIfGameDataIsMissing, true);
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

                case self::VOLUNTEER_POINTS:
                    $this->processVolunteerPoints();
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
        $result = $this->populateTitleGames($game);
        $titleGameMessage = '';
        if ($result == 1) {
            $titleGameMessage = "<br>Medal Round Games Populated with Teams";
        } else if ($result == 2) {
            $titleGameMessage = "<br>Medal Round Games Ready for Population, but tie-breaker rules need to be consulted.  Manual population required.";
        }

        $this->m_messageString = "Score $operation for game $this->m_gameId" . $titleGameMessage;
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
                $this->processGameScoring($this->m_operation == View_Base::UPDATE);
            }
        }
    }

    /**
     * Set the teams and enter or Update score for a title game
     */
    private function processTitleGameScoring()
    {
        if (isset($this->m_gameId)) {
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
                    $this->processGameScoring($this->m_operation == View_Base::UPDATE);
                }
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
            $this->processTeamScoring();
        }
    }

    /**
     * @param Game  $game   - Game that was just scored
     *
     * @return int   0 if title games not ready to populate
     *              1 if title games populated
     *              2 if title games ready to populate, but there is a tie in points
     */
    private function populateTitleGames($game)
    {
        // Skip if schedule does not support tournament play
        if ($game->flight->schedule->scheduleType != ScheduleOrm::SCHEDULE_TYPE_TOURNAMENT) {
            return 0;
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
        foreach ($games as $game) {
            if ($game->title != '') {
                if ($game->title == GameOrm::TITLE_SEMI_FINAL) {
                    $semiFinalGames[] = $game;
                } else {
                    $titleGames[$game->title] = $game;
                }
                continue;
            }

            if (!isset($game->homeTeamScore)) {
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
                /*
                while (isset($standingsByPoints["$points"])) {
                    $points = $points + .01;
                }
                */
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
        }

        // Populate title games
        foreach ($titleGames as $titleGame) {
            switch ($titleGame->title) {
                case GameOrm::TITLE_5TH_6TH:
                    // Populate 5th/6th game with third place finisher in pool1 and pool2
                    $thirdPlaceTeams            = $this->getTeamsForTitleGame($teamsByPool, $titleGame->title, 2);
                    $titleGame->homeTeam        = $thirdPlaceTeams[0];
                    $titleGame->visitingTeam    = $thirdPlaceTeams[1];
                    break;

                case GameOrm::TITLE_3RD_4TH:
                    // Use semi-final games if any; otherwise use pool teams
                    if (count($semiFinalGames) > 0) {
                        if ($this->areSemiFinalGamesScored($semiFinalGames)) {
                            $semiFinalLosers            = $this->getSemiFinalGameLosers($semiFinalGames);
                            $titleGame->homeTeam        = $semiFinalLosers[0];
                            $titleGame->visitingTeam    = $semiFinalLosers[1];
                        }
                    } else {
                        $secondPlaceTeams           = $this->getTeamsForTitleGame($teamsByPool, $titleGame->title, 1);
                        $titleGame->homeTeam        = $secondPlaceTeams[0];
                        $titleGame->visitingTeam    = $secondPlaceTeams[1];
                    }
                    break;

                case GameOrm::TITLE_CHAMPIONSHIP:
                    // Use semi-final games if any; otherwise use pool teams
                    if (count($semiFinalGames) > 0) {
                        if ($this->areSemiFinalGamesScored($semiFinalGames)) {
                            $semiFinalWinners           = $this->getSemiFinalGameWinners($semiFinalGames);
                            $titleGame->homeTeam        = $semiFinalWinners[0];
                            $titleGame->visitingTeam    = $semiFinalWinners[1];
                        }
                    } else {
                        $thirdPlaceTeams            = $this->getTeamsForTitleGame($teamsByPool, $titleGame->title, 0);
                        $titleGame->homeTeam        = $thirdPlaceTeams[0];
                        $titleGame->visitingTeam    = $thirdPlaceTeams[1];
                    }
                    break;

                default:
                    Assertion::isTrue(false, "$titleGame->title not supported");
            }
        }

        return 1;
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

    /**
     * Update volunteer points for teams
     */
    private function processVolunteerPoints()
    {
        foreach ($this->m_volunteerPointsData as $teamId => $volunteerPoints) {
            $team = Team::lookupById($teamId);
            $team->volunteerPoints = $volunteerPoints;
        }

        $this->m_messageString = count($this->m_volunteerPointsData) > 0 ? "Volunteer Points Updated" : "";
    }
}
