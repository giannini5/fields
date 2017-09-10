<?php

namespace DAG\Domain\Schedule;

use DAG\Domain\Domain;
use DAG\Framework\Orm\NoResultsException;
use DAG\Orm\Schedule\GameOrm;
use DAG\Framework\Exception\Precondition;
use DAG\Orm\Schedule\MoveGameException;


/**
 * @property int        $id
 * @property Flight     $flight
 * @property Pool       $pool
 * @property GameTime   $gameTime
 * @property Team       $homeTeam
 * @property Team       $visitingTeam
 * @property int        $homeTeamScore
 * @property int        $visitingTeamScore
 * @property int        $homeTeamYellowCards
 * @property int        $visitingTeamYellowCards
 * @property int        $homeTeamRedCards
 * @property int        $visitingTeamRedCards
 * @property int        $notes
 * @property string     $title
 * @property int        $locked
 */
class Game extends Domain
{
    /** @var GameOrm */
    private $gameOrm;

    /** @var Flight */
    private $flight;

    /** @var Pool */
    private $pool;

    /** @var GameTime */
    private $gameTime;

    /** @var Team */
    private $homeTeam;

    /** @var Team */
    private $visitingTeam;

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
    }

    /**
     * @param Flight    $flight
     * @param Pool      $pool
     * @param GameTime  $gameTime
     * @param Team      $homeTeam
     * @param Team      $visitingTeam
     * @param string    $title
     * @param int       $locked
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
        $locked = 0)
    {
        $poolId         = isset($pool) ? $pool->id : null;
        $homeTeamId     = isset($homeTeam) ? $homeTeam->id : null;
        $visitingTeamId = isset($visitingTeam) ? $visitingTeam->id : null;

        $gameOrm        = GameOrm::create($flight->id, $poolId, $gameTime->id, $homeTeamId, $visitingTeamId, $title, $locked);
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
     * @param $propertyName
     * @return int|string
     */
    public function __get($propertyName)
    {
        switch ($propertyName) {
            case "id":
            case "title":
            case "locked":
            case "homeTeamScore":
            case "visitingTeamScore":
            case "homeTeamYellowCards":
            case "visitingTeamYellowCards":
            case "homeTeamRedCards":
            case "visitingTeamRedCards":
            case "notes":
                return $this->gameOrm->{$propertyName};

            case "flight":
            case "pool":
            case "gameTime":
            case "homeTeam":
            case "visitingTeam":
                return $this->{$propertyName};

            default:
                Precondition::isTrue(false, "Unrecognized property: $propertyName");
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
            case "locked":
            case "homeTeamScore":
            case "visitingTeamScore":
            case "homeTeamYellowCards":
            case "visitingTeamYellowCards":
            case "homeTeamRedCards":
            case "visitingTeamRedCards":
            case "notes":
                return isset($this->gameOrm->{$propertyName});

            case "flight":
            case "pool":
            case "gameTime":
            case "homeTeam":
            case "visitingTeam":
                return isset($this->{$propertyName});

            default:
                Precondition::isTrue(false, "Unrecognized property: $propertyName");
        }
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
     *
     * @return bool true if this game overlaps with one of the passed in games
     */
    public function anyOverlap($games, &$overlappingGame, $preGameBufferMinute = 0, $postGameBufferMinutes = 0)
    {
        $overlappingGame = null;

        // Get this games start time with $preGameBufferMinute minute pre-game buffer
        $dateTime = \DateTime::createFromFormat("H:i:s", $this->gameTime->startTime);
        $interval = new \DateInterval("PT" . $preGameBufferMinute . "M");
        $dateTime->sub($interval);
        $thisStartTime  = $dateTime->format("H:i:s");

        // Get this games end time plus $postGameBufferMinutes minute buffer
        $thisEndTime = $this->gameTime->getEndTime($this->pool->schedule->division->gameDurationMinutes + $postGameBufferMinutes);

        foreach ($games as $game) {
            if ($game->id == $this->id) {
                continue;
            }

            // Not sure why, but sometimes the gameTime is null???
            // Just skip these games if/when this happens
            if (!isset($game->gameTime)) {
                continue;
            }

            $gameTime   = $game->gameTime;
            $startTime  = $gameTime->startTime;
            $endTime    = $gameTime->getEndTime($game->pool->schedule->division->gameDurationMinutes);

            if ($thisStartTime <= $endTime and $thisEndTime >= $startTime) {
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
     *
     * @return bool true if game is for team; false otherwise
     */
    public function isForTeam($team)
    {
        if (isset($this->homeTeam) and $this->homeTeam->id == $team->id) {
            return true;
        }

        if (isset($this->visitingTeam) and $this->visitingTeam->id == $team->id) {
            return true;
        }

        if (!isset($this->homeTeam) and !isset($this->visitingTeam) and $this->flight->id == $team->pool->flight->id) {
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
     *  Delete the game
     */
    public function delete()
    {
        // Delete all familyGame entities
        $familyGames = FamilyGame::lookupByGame($this);
        foreach ($familyGames as $familyGame) {
            $familyGame->delete();
        }

        // Update gameTime to remove gameId
        $gameTime       = GameTime::lookupById($this->gameTime->id);
        $gameTime->game = null;

        // Delete game
        $this->gameOrm->delete();
    }
}