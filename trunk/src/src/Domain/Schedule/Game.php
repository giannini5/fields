<?php

namespace DAG\Domain\Schedule;

use DAG\Domain\Domain;
use DAG\Framework\Exception\Assertion;
use DAG\Framework\Orm\NoResultsException;
use DAG\Orm\Schedule\GameOrm;
use DAG\Framework\Exception\Precondition;
use DAG\Orm\Schedule\GameRefereeOrm;
use DAG\Orm\Schedule\MoveGameException;


/**
 * @property int            $id
 * @property Schedule       $schedule
 * @property Flight         $flight
 * @property Pool           $pool
 * @property GameDate       $gameDate
 * @property GameTime       $gameTime
 * @property Team           $homeTeam
 * @property Team           $visitingTeam
 * @property int            $homeTeamScore
 * @property int            $visitingTeamScore
 * @property int            $homeTeamYellowCards
 * @property int            $visitingTeamYellowCards
 * @property int            $homeTeamRedCards
 * @property int            $visitingTeamRedCards
 * @property int            $notes
 * @property string         $title
 * @property int            $playInHomeGameId
 * @property int            $playInVisitingGameId
 * @property int            $playInByWin
 * @property int            $locked
 * @property RefereeCrew    $refereeCrew
 * @property bool           $areRefereesAssigned
 * @property bool           $isCenterRefereeAssigned
 * @property bool           $isAssistantReferee1Assigned
 * @property bool           $isAssistantReferee2Assigned
 */
class Game extends Domain
{
    /** @var GameOrm */
    private $gameOrm;

    /** @var Schedule */
    private $schedule;

    /** @var Flight */
    private $flight;

    /** @var Pool */
    private $pool;

    /** @var GameDate */
    private $gameDate;

    /** @var GameTime */
    private $gameTime;

    /** @var Team */
    private $homeTeam;

    /** @var Team */
    private $visitingTeam;

    /** @var RefereeCrew */
    private $refereeCrew;

    /**
     * @param GameOrm   $gameOrm
     * @param Flight    $flight (defaults to null)
     * @param Pool      $pool (defaults to null)
     * @param GameTime  $gameTime (defaults to null)
     * @param Team      $homeTeam (defaults to null)
     * @param Team      $visitingTeam (defaults to null)
     */
    protected function __construct(GameOrm $gameOrm, $flight = null, $pool = null, $gameTime = null, $homeTeam = null, $visitingTeam = null)
    {
        $this->gameOrm      = $gameOrm;
        $this->flight       = isset($flight) ? $flight : Flight::lookupById($gameOrm->flightId);
        $this->schedule     = $this->flight->schedule;

        $this->pool = null;
        if (isset($pool)) {
            $this->pool = $pool;
        } elseif (isset($gameOrm->poolId)) {
            $this->pool = Pool::lookupById($gameOrm->poolId);
        }

        $this->homeTeam = null;
        if (isset($homeTeam)) {
            $this->homeTeam = $homeTeam;
        } elseif (isset($gameOrm->homeTeamId)) {
            $this->homeTeam = Team::lookupById($gameOrm->homeTeamId);
        }

        $this->visitingTeam = null;
        if (isset($visitingTeam)) {
            $this->visitingTeam = $visitingTeam;
        } elseif (isset($gameOrm->visitingTeamId)) {
            $this->visitingTeam = Team::lookupById($gameOrm->visitingTeamId);
        }

        $this->gameTime     = isset($gameTime) ? $gameTime : GameTime::lookupById($gameOrm->gameTimeId, $this);
        $this->gameDate     = $this->gameTime->gameDate;
        $this->refereeCrew  = isset($gameOrm->refereeCrewId) ? RefereeCrew::lookupById($gameOrm->refereeCrewId) : null;
    }

    /**
     * @param Flight    $flight
     * @param Pool      $pool
     * @param GameTime  $gameTime
     * @param Team      $homeTeam
     * @param Team      $visitingTeam
     * @param string    $title
     * @param int       $locked
     * @param int       $playInHomeGameId
     * @param int       $playInVisitingGameId
     * @param int       $playInByWin
     *
     * @return Game
     */
    public static function create(
        $flight,
        $pool,
        $gameTime,
        $homeTeam,
        $visitingTeam,
        $title = '',
        $locked = 0,
        $playInHomeGameId = 0,
        $playInVisitingGameId = 0,
        $playInByWin = 0
    ) {
        $poolId         = isset($pool) ? $pool->id : null;
        $homeTeamId     = isset($homeTeam) ? $homeTeam->id : null;
        $visitingTeamId = isset($visitingTeam) ? $visitingTeam->id : null;

        $gameOrm        = GameOrm::create($flight->schedule->id, $flight->id, $poolId, $gameTime->gameDate->id,
            $gameTime->id, $homeTeamId, $visitingTeamId, $title, $locked, $playInHomeGameId, $playInVisitingGameId,
            $playInByWin);
        $game           = new static($gameOrm, $flight, $pool, null, $homeTeam, $visitingTeam);
        $gameTime->game = $game;

        return $game;
    }

    /**
     * @param int       $gameId
     * @param GameTime  $gameTime defaults to null
     *
     * @return Game
     */
    public static function lookupById($gameId, $gameTime = null)
    {
        $gameOrm = GameOrm::loadById($gameId);
        return new static($gameOrm, null, null, $gameTime);
    }

    /**
     * @param int       $gameId
     * @param Game      $foundGame - null if not found
     *
     * @return bool     true if game found; false otherwise
     */
    public static function findById($gameId, &$foundGame)
    {
        try {
            $gameOrm = GameOrm::loadById($gameId);
            $foundGame = new static($gameOrm);
            return true;
        } catch (NoResultsException $e) {
            return false;
        }
    }

    /**
     * @param Game  $game
     * @param int   $playInByWin
     * @param Game  $foundGame - null if not found
     *
     * @return bool     true if game found; false otherwise
     */
    public static function findByPlayInGame($game, $playInByWin, &$foundGame)
    {
        $gameOrm = null;
        $result = GameOrm::findByPlayInGameId($game->id, $playInByWin, $gameOrm);

        if ($result) {
            $foundGame = new static($gameOrm);
        }

        return $result;
    }

    /**
     * @param Schedule $schedule
     *
     * @return Game[]
     */
    public static function lookupBySchedule($schedule)
    {
        $games = [];

        $gameOrms = GameOrm::loadByScheduleId($schedule->id);
        foreach ($gameOrms as $gameOrm) {
            $games[] = new static($gameOrm);
        }

        return $games;
    }

    /**
     * @param Flight $flight
     *
     * @return Game[]
     */
    public static function lookupByFlight($flight)
    {
        $games = [];

        $gameOrms = GameOrm::loadByFlightId($flight->id);
        foreach ($gameOrms as $gameOrm) {
            $games[] = new static($gameOrm, $flight);
        }

        return $games;
    }

    /**
     * @param Flight    $flight
     * @param string    $title
     *
     * @return Game[]
     */
    public static function lookupByFlightAndTitle($flight, $title)
    {
        $games = [];

        $gameOrms = GameOrm::loadByFlightIdAndTitle($flight->id, $title);
        foreach ($gameOrms as $gameOrm) {
            $games[] = new static($gameOrm, $flight);
        }

        return $games;
    }

    /**
     * @param Pool $pool
     *
     * @return Game[]
     */
    public static function lookupByPool($pool)
    {
        $games = [];

        $gameOrms = GameOrm::loadByPoolId($pool->id);
        foreach ($gameOrms as $gameOrm) {
            $games[] = new static($gameOrm, null, $pool);
        }

        return $games;
    }

    /**
     * @param GameTime      $gameTime
     *
     * @return Game
     */
    public static function lookupByGameTime($gameTime)
    {
        $gameOrm = GameOrm::loadByGameTimeId($gameTime->id);
        return new static($gameOrm, null, null, $gameTime);
    }

    /**
     * @param Team  $team
     *
     * @return Game[]
     */
    public static function lookupByTeam($team)
    {
        $games = [];

        $gameOrms = GameOrm::loadByTeamId($team->id);
        foreach ($gameOrms as $gameOrm) {
            $games[] = new static($gameOrm);
        }

        return $games;
    }

    /**
     * @param Team      $team
     * @param GameDate  $gameDate
     *
     * @return Game[]
     */
    public static function lookupByTeamAndDay($team, $gameDate)
    {
        $games = [];

        $gameOrms = GameOrm::loadByGameDateIdTeamId($gameDate->id, $team->id);
        foreach ($gameOrms as $gameOrm) {
            $games[] = new static($gameOrm, null, null);
        }

        return $games;
    }

    /**
     * @param Team  $team
     *
     * @return Game[]
     */
    public static function lookupByHomeTeam($team)
    {
        $games = [];

        $gameOrms = GameOrm::loadByHomeTeamId($team->id);
        foreach ($gameOrms as $gameOrm) {
            $games[] = new static($gameOrm);
        }

        return $games;
    }

    /**
     * Get all games being played by specified division on specified day.
     *
     * @param Division  $division
     * @param string    $day
     * @param bool      $sortByTime
     *
     * @return Game[]
     */
    public static function lookupByDivisionDay($division, $day, $sortByTime = false)
    {
        $gamesOnDay     = [];
        $gamesByTime    = [];

        $schedules = Schedule::lookupByDivision($division);
        foreach ($schedules as $schedule) {
            $flights = Flight::lookupBySchedule($schedule);

            foreach ($flights as $flight) {
                $games = Game::lookupByFlight($flight);

                foreach ($games as $game) {
                    if ($game->gameTime->gameDate->day == $day) {
                        $gamesByTime[$game->gameTime->startTime][] = $game;
                        $gamesOnDay[] = $game;
                    }
                }
            }
        }

        if ($sortByTime) {
            $gamesOnDay = [];
            ksort($gamesByTime);
            foreach ($gamesByTime as $games) {
                $gamesOnDay = array_merge($gamesOnDay, $games);
            }
        }

        return $gamesOnDay;
    }

    /**
     * Get all games being played by specified schedule on specified day.
     *
     * @param Schedule  $schedule
     * @param GameDate  $gameDate
     * @param bool      $sortByTime
     *
     * @return Game[]
     */
    public static function lookupByScheduleDay($schedule, $gameDate, $sortByTime = false)
    {
        $gamesOnDay     = [];
        $gamesByTime    = [];

        $gameOrms = GameOrm::loadByScheduleIdGameDateId($schedule->id, $gameDate->id);
        foreach ($gameOrms as $gameOrm) {
            $game = new static($gameOrm);
            if ($game->gameTime->gameDate->id == $gameDate->id) {
                $gamesOnDay[]                               = $game;
                $gamesByTime[$game->gameTime->startTime][]  = $game;
            }
        }

        if ($sortByTime) {
            $gamesOnDay = [];
            ksort($gamesByTime);
            foreach ($gamesByTime as $games) {
                $gamesOnDay = array_merge($gamesOnDay, $games);
            }
        }

        return $gamesOnDay;
    }

    /**
     * Swap the home and visiting teams
     */
    public function swapTeams()
    {
        Precondition::isTrue(isset($this->homeTeam), "Home Team is not set");
        Precondition::isTrue(isset($this->visitingTeam), "Visiting Team is not set");

        $team1  = $this->homeTeam;
        $team2  = $this->visitingTeam;

        $this->gameOrm->homeTeamId      = $team2->id;
        $this->gameOrm->visitingTeamId  = $team1->id;
        $this->gameOrm->save();

        $this->homeTeam     = $team2;
        $this->visitingTeam = $team1;
    }

    /**
     * @param $propertyName
     * @return int|string
     */
    public function __get($propertyName)
    {
        switch ($propertyName) {
            case "id":
            case "title":
            case "playInHomeGameId":
            case "playInVisitingGameId":
            case "playInByWin":
            case "locked":
            case "homeTeamScore":
            case "visitingTeamScore":
            case "homeTeamYellowCards":
            case "visitingTeamYellowCards":
            case "homeTeamRedCards":
            case "visitingTeamRedCards":
            case "notes":
                return $this->gameOrm->{$propertyName};

            case "schedule":
            case "flight":
            case "pool":
            case "gameDate":
            case "gameTime":
            case "homeTeam":
            case "visitingTeam":
            case "refereeCrew":
                return $this->{$propertyName};

            case "areRefereesAssigned":
                $gameReferees   = GameReferee::lookupByGame($this);
                $centerCount    = 0;
                $arCount        = 0;
                foreach ($gameReferees as $gameReferee) {
                    $centerCount += $gameReferee->role == GameRefereeOrm::CENTER_ROLE ? 1 : 0;
                    $arCount     += $gameReferee->role == GameRefereeOrm::ASSISTANT_ROLE_1 ? 1 : 0;
                    $arCount     += $gameReferee->role == GameRefereeOrm::ASSISTANT_ROLE_2 ? 1 : 0;
                }
                return $centerCount >= 1 and $arCount >= 2;

            case "isCenterRefereeAssigned":
                $gameReferees   = GameReferee::lookupByGame($this);
                $centerCount    = 0;
                foreach ($gameReferees as $gameReferee) {
                    $centerCount += $gameReferee->role == GameRefereeOrm::CENTER_ROLE ? 1 : 0;
                }
                return $centerCount >= 1;

            case "isAssistantReferee1Assigned":
                $gameReferees   = GameReferee::lookupByGame($this);
                $arCount        = 0;
                foreach ($gameReferees as $gameReferee) {
                    $arCount     += $gameReferee->role == GameRefereeOrm::ASSISTANT_ROLE_1 ? 1 : 0;
                }
                return $arCount >= 1;

            case "isAssistantReferee2Assigned":
                $gameReferees   = GameReferee::lookupByGame($this);
                $arCount        = 0;
                foreach ($gameReferees as $gameReferee) {
                    $arCount     += $gameReferee->role == GameRefereeOrm::ASSISTANT_ROLE_2 ? 1 : 0;
                }
                return $arCount >= 1;

            default:
                Precondition::isTrue(false, "Unrecognized property: $propertyName");
                return false;
        }
    }

    /**
     * @param $propertyName
     * @param $value
     */
    public function __set($propertyName, $value)
    {
        switch ($propertyName) {
            case "title":
            case "playInByWin":
            case "locked":
            case "homeTeamScore":
            case "visitingTeamScore":
            case "homeTeamYellowCards":
            case "visitingTeamYellowCards":
            case "homeTeamRedCards":
            case "visitingTeamRedCards":
            case "notes":
                $this->gameOrm->{$propertyName} = $value;
                $this->gameOrm->save();
                break;

            case "gameTime":
                $this->gameOrm->gameTimeId = $value->id;
                $this->gameOrm->save();
                $this->gameTime = $value;
                break;

            case "homeTeam":
                $this->gameOrm->homeTeamId = $value->id;
                $this->gameOrm->save();
                $this->homeTeam = $value;
                break;

            case "visitingTeam":
                $this->gameOrm->visitingTeamId = $value->id;
                $this->gameOrm->save();
                $this->visitingTeam = $value;
                break;

            case "refereeCrew":
                $this->gameOrm->refereeCrewId = isset($value) ? $value->id : null;
                $this->gameOrm->save();
                $this->refereeCrew = $value;
                break;

            default:
                Precondition::isTrue(false, "Unrecognized property: $propertyName");
        }
    }

    /**
     * @param $propertyName
     *
     * @return bool - true if set; false otherwise
     */
    public function __isset($propertyName)
    {
        switch ($propertyName) {
            case "id":
            case "title":
            case "playInByWin":
            case "locked":
            case "homeTeamScore":
            case "visitingTeamScore":
            case "homeTeamYellowCards":
            case "visitingTeamYellowCards":
            case "homeTeamRedCards":
            case "visitingTeamRedCards":
            case "notes":
                return isset($this->gameOrm->{$propertyName});

            case "playInHomeGameId":
            case "playInVisitingGameId":
                return $this->gameOrm->{$propertyName} != 0;

            case "schedule":
            case "flight":
            case "pool":
            case "gameDate":
            case "gameTime":
            case "homeTeam":
            case "visitingTeam":
            case "refereeCrew":
                return isset($this->{$propertyName});

            default:
                Precondition::isTrue(false, "Unrecognized property: $propertyName");
                return false;
        }
    }

    /**
     * @param $game
     */
    public function setPlayInHomeGame($game)
    {
        $this->gameOrm->playInHomeGameId = $game->id;
        $this->gameOrm->save();
    }

    /**
     * @param $game
     */
    public function setPlayInVisitingGame($game)
    {
        $this->gameOrm->playInVisitingGameId = $game->id;
        $this->gameOrm->save();
    }

    /**
     * @return bool true if locked; false otherwise
     */
    public function isLocked()
    {
        return $this->gameOrm->locked == 1;
    }

    /**
     * Check to see if this game overlaps with any of the passed in games
     *
     * @param Game[]    $games
     * @param int       $preGameBufferMinute
     * @param int       $postGameBufferMinutes
     * @param Game      $overlappingGame (null if none found)
     * @param bool      $includeThisGame - return this game as the overlapping game if this game is included
     *                                     in the $games list (defaults to false)
     * @param bool      $backToBackOkay - Defaults to false and games that are back-to-back are considered an overlap
     *
     * @return bool true if this game overlaps with one of the passed in games
     */
    public function anyOverlap($games, &$overlappingGame, $preGameBufferMinute = 0, $postGameBufferMinutes = 0,
                               $includeThisGame = false, $backToBackOkay = false)
    {
        $overlappingGame = null;

        // Get this games start time with $preGameBufferMinute minute pre-game buffer
        $dateTime = \DateTime::createFromFormat("H:i:s", $this->gameTime->actualStartTime);
        $interval = new \DateInterval("PT" . $preGameBufferMinute . "M");
        $dateTime->sub($interval);
        $thisStartTime  = $dateTime->format("H:i:s");

        // Get this games end time plus $postGameBufferMinutes minute buffer
        $thisEndTime = $this->gameTime->getEndTime($this->pool->schedule->division->gameDurationMinutes + $postGameBufferMinutes);

        foreach ($games as $game) {
            if (!$includeThisGame and $game->id == $this->id) {
                continue;
            }

            // Not sure why, but sometimes the gameTime is null???
            // Just skip these games if/when this happens
            // Note: I think this was a bug from some direct database manipulation - should no longer happen!
            $gameTime = $game->gameTime;
            if (!isset($gameTime)) {
                continue;
            }

            $startTime  = $gameTime->actualStartTime;
            $endTime    = $gameTime->getEndTime($game->pool->schedule->division->gameDurationMinutes);

            if ($backToBackOkay) {
                if ($thisStartTime < $endTime and $thisEndTime > $startTime) {
                    $overlappingGame = $game;
                    return true;
                }
            }
            else if ($thisStartTime <= $endTime and $thisEndTime >= $startTime) {
                $overlappingGame = $game;
                return true;
            }
        }

        return false;
    }

    /**
     * Move game to a new game time
     *
     * @param GameTime  $newGameTime
     *
     * @throws MoveGameException
     */
    public function move($newGameTime)
    {
        try {
            Precondition::isTrue(!isset($newGameTime->game), "ERROR, game already exists for new game time");
            Precondition::isTrue($newGameTime->gameDate->day == $this->gameTime->gameDate->day, "ERROR, attempt to move a game to a different day");

            // Verify new game time's field is supported for this day
            DivisionField::lookupByDivisionAndField($this->flight->schedule->division, $newGameTime->field);

            // Update the GameTime objects
            $oldGameTime        = $this->gameTime;
            $oldGameTime->game  = null;
            $newGameTime->game  = $this;

            // Update this game to the new gameTime
            $this->gameTime             = $newGameTime;
            $this->gameOrm->gameTimeId  = $newGameTime->id;
            $this->gameOrm->save();
        } catch (\Exception $e) {
            throw new MoveGameException("Error trying to move game to a new time", $e);
        }
    }

    /**
     * @param Team  $team
     * @param int[] $playInGameIds
     *
     * @return bool true if game is for team; false otherwise
     */
    public function isForTeam($team, $playInGameIds = [])
    {
        if (isset($this->homeTeam) and $this->homeTeam->id == $team->id) {
            return true;
        }

        if (isset($this->visitingTeam) and $this->visitingTeam->id == $team->id) {
            return true;
        }

        if ((!isset($this->homeTeam) or !isset($this->visitingTeam))
            and (in_array($this->playInHomeGameId, $playInGameIds) or in_array($this->playInVisitingGameId, $playInGameIds))) {
            return true;
        }

        if (!isset($this->pool) and !empty($this->title) and $this->flight->id == $team->pool->flight->id) {
            return true;
        }

        return false;
    }

    /**
     * Game points are computed as follows for tournament play:
     *      - 6 points for a win
     *      - 3 points for a tie
     *      - 1 point for a shutout
     *      - 1 point for every goal scored up to 3 goals
     *      - -2 points for every red card
     *  10 points maximum.
     *
     * Game points are computed as follows for league play
     *      - 3 points for a win
     *      - 1 point for a tie
     *
     * @param bool  $computeForHomeTeam
     * @param bool  $isLeaguePlay defaults to false
     *
     * @return int  $points for desired team
     */
    public function computeGamePoints($computeForHomeTeam, $isLeaguePlay = false)
    {
        $goals          = $computeForHomeTeam ? $this->homeTeamScore : $this->visitingTeamScore;
        $opposingGoals  = $computeForHomeTeam ? $this->visitingTeamScore : $this->homeTeamScore;
        $reds           = $computeForHomeTeam ? $this->homeTeamRedCards : $this->visitingTeamRedCards;
        $win            = $computeForHomeTeam ? $this->homeTeamScore > $this->visitingTeamScore : $this->visitingTeamScore > $this->homeTeamScore;
        $tie            = $this->homeTeamScore == $this->visitingTeamScore;

        if ($isLeaguePlay) {
            $points = $win ? 3 : 0;
            $points = $tie ? 1 : $points;
        } else {
            $points = $win ? 6 : 0;
            $points = $tie ? 3 : $points;
            $points += $goals > 3 ? 3 : $goals;
            $points += $opposingGoals == 0 ? 1 : 0;
            $points += $reds * -2;
        }

        return $points;
    }

    /**
     * Set the game stats
     *
     * @param array     $gameStats  [gameId =>
     *                                  [teamId =>
     *                                      [playerId =>
     *                                          [
     *                                              playerId        => <id>,
     *                                              playerName      => <name>,
     *                                              playerGoals     => <goals>,
     *                                              playerSubQ1     => <X or empty>
     *                                              playerSubQ2     => <X or empty>
     *                                              playerSubQ3     => <X or empty>
     *                                              playerSubQ4     => <X or empty>
     *                                              playerKeepQ1    => <G or empty>
     *                                              playerKeepQ2    => <G or empty>
     *                                              playerKeepQ3    => <G or empty>
     *                                              playerKeepQ4    => <G or empty>
     *                                              reds            => <reds>,
     *                                              yellows         => <yellows>,
     *                                          ]
     *                                      ]
     *                                  ]
     *                              ]
     * @param string    $notes
     */
    public function setStats($gameStats, $notes = '')
    {
        $homeScore          = 0;
        $homeRedCards       = 0;
        $homeYellowCards    = 0;
        $visitScore         = 0;
        $visitRedCards      = 0;
        $visitYellowCards   = 0;

        foreach ($gameStats as $gameId => $teamData) {
            Assertion::isTrue($gameId == $this->id, "Invalid gameId: $gameId, expected $this->id");

            foreach ($teamData as $teamId => $playerStats) {
                $team = Team::lookupById($teamId);

                foreach ($playerStats as $playerId => $stats) {
                    // Add new player if playerId <= 0 and either name or number is populated
                    if ($playerId <= 0 and
                        (!empty($stats[\View_Base::PLAYER_NUMBER]) or !empty($stats[\View_Base::PLAYER_NAME]))) {
                        $player = Player::create($team, null, '', '', '');
                    } else if ($playerId <= 0) {
                        continue;
                    } else {
                        $player = Player::lookupById($playerId);
                    }

                    $playerGameStats    = PlayerGameStats::findOrCreate($this, $team, $player);

                    // Update Player Number if not empty
                    if (!empty($stats[\View_Base::PLAYER_NUMBER])) {
                        $player->number = (int)$stats[\View_Base::PLAYER_NUMBER];
                    } else {
                        $player->number = null;
                    }

                    // Update Player Name if different (part of unique key, exception will be thrown if not unique)
                    if (!empty($stats[\View_Base::PLAYER_NAME]) and
                        $player->name != $stats[\View_Base::PLAYER_NAME]) {
                        $player->setName($stats[\View_Base::PLAYER_NAME]);
                    } else if (empty($stats[\View_Base::PLAYER_NAME])) {
                        $player->setName('');
                    }

                    // Remove old stats from Player
                    $this->removePlayerStats($playerGameStats);

                    // Set Player Goals
                    Assertion::isTrue($stats[\View_Base::PLAYER_GOALS] >= 0, "Invalid goals for player: $player->name: " . $stats[\View_Base::PLAYER_GOALS]);
                    $playerGameStats->goals = empty($stats[\View_Base::PLAYER_GOALS]) ? 0 : (int) $stats[\View_Base::PLAYER_GOALS];

                    // Process player by quarter
                    for ($i = 1; $i <= 4; $i++) {
                        $label          = \View_Base::PLAYER_BASE . $i;
                        $subField       = 'substitutionQuarter' . $i;
                        $keepField      = 'keeperQuarter' . $i;
                        $injuredField   = 'injuredQuarter' . $i;
                        $absentField    = 'absentQuarter' . $i;

                        Assertion::isTrue(empty($stats[$label])
                            or $stats[$label] == 'X'
                            or $stats[$label] == 'G'
                            or $stats[$label] == 'I'
                            or $stats[$label] == 'A'
                            or $stats[$label] == 'E',
                            "Invalid setting for player: $player->name: " . $stats[$label]);

                        $playerGameStats->{$subField}       = $stats[$label] == 'X';
                        $playerGameStats->{$keepField}      = $stats[$label] == 'G';
                        $playerGameStats->{$injuredField}   = $stats[$label] == 'I';
                        $playerGameStats->{$absentField}    = $stats[$label] == 'A';
                    }

                    // Process cards
                    $yellowCards = 0;
                    $yellowCards += isset($stats[\View_Base::PLAYER_YELLOW1]) ? 1 : 0;
                    $yellowCards += isset($stats[\View_Base::PLAYER_YELLOW2]) ? 1 : 0;
                    $playerGameStats->yellowCards = $yellowCards;

                    $redCards = 0;
                    $redCards += isset($stats[\View_Base::PLAYER_RED]) ? 1 : 0;
                    $playerGameStats->redCard = $redCards > 0;

                    if ($player->team->id == $this->homeTeam->id) {
                        $homeScore          += $playerGameStats->goals;
                        $homeYellowCards    += $yellowCards;
                        $homeRedCards       += $redCards;
                    } else {
                        $visitScore         += $playerGameStats->goals;
                        $visitYellowCards   += $yellowCards;
                        $visitRedCards      += $redCards;
                    }

                    // Add stats from Player
                    $this->addPlayerStats($playerGameStats);
                }
            }
        }

        $this->homeTeamScore            = $homeScore;
        $this->homeTeamYellowCards      = $homeYellowCards;
        $this->homeTeamRedCards         = $homeRedCards;
        $this->visitingTeamScore        = $visitScore;
        $this->visitingTeamYellowCards  = $visitYellowCards;
        $this->visitingTeamRedCards     = $visitRedCards;
        $this->notes                    = $notes;
    }

    /**
     * Remove player stats from player
     *
     * @param PlayerGameStats $playerGameStats
     */
    private function removePlayerStats($playerGameStats)
    {
        $player = $playerGameStats->player;

        // Update goals
        $goals = $player->goals - $playerGameStats->goals;
        $player->goals = $goals < 0 ? 0 : $goals;

        // Update quartersSub
        $quartersSub = $player->quartersSub - (
            ($playerGameStats->substitutionQuarter1 ? 1 : 0) +
            ($playerGameStats->substitutionQuarter2 ? 1 : 0) +
            ($playerGameStats->substitutionQuarter3 ? 1 : 0) +
            ($playerGameStats->substitutionQuarter4 ? 1 : 0));
        $player->quartersSub = $quartersSub < 0 ? 0 : $quartersSub;

        // Update quartersKeep
        $quartersKeep = $player->quartersKeep - (
            ($playerGameStats->keeperQuarter1 ? 1 : 0) +
            ($playerGameStats->keeperQuarter2 ? 1 : 0) +
            ($playerGameStats->keeperQuarter3 ? 1 : 0) +
            ($playerGameStats->keeperQuarter4 ? 1 : 0));
        $player->quartersKeep = $quartersKeep < 0 ? 0 : $quartersKeep;

        // Update quartersInjured
        $quartersInjured = $player->quartersKeep - (
                ($playerGameStats->injuredQuarter1 ? 1 : 0) +
                ($playerGameStats->injuredQuarter2 ? 1 : 0) +
                ($playerGameStats->injuredQuarter3 ? 1 : 0) +
                ($playerGameStats->injuredQuarter4 ? 1 : 0));
        $player->quartersInjured = $quartersInjured < 0 ? 0 : $quartersInjured;

        // Update quartersAbsent
        $quartersAbsent = $player->quartersAbsent - (
                ($playerGameStats->absentQuarter1 ? 1 : 0) +
                ($playerGameStats->absentQuarter2 ? 1 : 0) +
                ($playerGameStats->absentQuarter3 ? 1 : 0) +
                ($playerGameStats->absentQuarter4 ? 1 : 0));
        $player->quartersAbsent = $quartersAbsent < 0 ? 0 : $quartersAbsent;

        // Update yellowCards
        $yellowCards = $player->yellowCards - $playerGameStats->yellowCards;
        $player->yellowCards = $yellowCards < 0 ? 0 : $yellowCards;

        // Update redCards
        $redCards = $player->redCards - ($playerGameStats->redCard ? 1 : 0);
        $player->redCards = $redCards < 0 ? 0 : $redCards;
    }

    /**
     * Add player stats to player
     *
     * @param PlayerGameStats $playerGameStats
     */
    private function addPlayerStats($playerGameStats)
    {
        $player = $playerGameStats->player;

        // Update goals
        $player->goals = $player->goals + $playerGameStats->goals;

        // Update quartersSub
        $player->quartersSub = $player->quartersSub + (
                ($playerGameStats->substitutionQuarter1 ? 1 : 0) +
                ($playerGameStats->substitutionQuarter2 ? 1 : 0) +
                ($playerGameStats->substitutionQuarter3 ? 1 : 0) +
                ($playerGameStats->substitutionQuarter4 ? 1 : 0));

        // Update quartersKeep
        $player->quartersKeep = $player->quartersKeep + (
                ($playerGameStats->keeperQuarter1 ? 1 : 0) +
                ($playerGameStats->keeperQuarter2 ? 1 : 0) +
                ($playerGameStats->keeperQuarter3 ? 1 : 0) +
                ($playerGameStats->keeperQuarter4 ? 1 : 0));

        // Update quartersInjured
        $player->quartersInjured = $player->quartersInjured + (
                ($playerGameStats->injuredQuarter1 ? 1 : 0) +
                ($playerGameStats->injuredQuarter2 ? 1 : 0) +
                ($playerGameStats->injuredQuarter3 ? 1 : 0) +
                ($playerGameStats->injuredQuarter4 ? 1 : 0));

        // Update quartersAbsent
        $player->quartersAbsent = $player->quartersAbsent + (
                ($playerGameStats->absentQuarter1 ? 1 : 0) +
                ($playerGameStats->absentQuarter2 ? 1 : 0) +
                ($playerGameStats->absentQuarter3 ? 1 : 0) +
                ($playerGameStats->absentQuarter4 ? 1 : 0));

        // Update yellowCards
        $player->yellowCards = $player->yellowCards + $playerGameStats->yellowCards;

        // Update redCards
        $player->redCards = $player->redCards + ($playerGameStats->redCard ? 1 : 0);
    }

    /**
     * Clear the game stats
     */
    public function clearStats()
    {
        // Delete player game stats
        $this->deletePlayerGameStats();

        // Clear game stats
        $this->homeTeamScore            = null;
        $this->homeTeamYellowCards      = 0;
        $this->homeTeamRedCards         = 0;
        $this->visitingTeamScore        = null;
        $this->visitingTeamYellowCards  = 0;
        $this->visitingTeamRedCards     = 0;
        $this->notes                    = '';
    }

    /**
     *  Delete the game
     */
    public function deletePlayerGameStats()
    {
        // Delete all playerGameStat entities
        $playerGameStats = PlayerGameStats::lookup($this);
        foreach ($playerGameStats as $playerGameStat) {
            $playerGameStat->delete();
        }
    }

    /**
     *  Delete the game
     */
    public function delete()
    {
        // Delete all playerGameStat entities
        $this->deletePlayerGameStats();

        // Delete all familyGame entities
        $familyGames = FamilyGame::lookupByGame($this);
        foreach ($familyGames as $familyGame) {
            $familyGame->delete();
        }

        // Update gameTime to remove gameId
        $gameTime                   = GameTime::lookupById($this->gameTime->id);
        $gameTime->game             = null;
        $gameTime->actualStartTime  = null;

        // Delete game
        $this->gameOrm->delete();
    }
}