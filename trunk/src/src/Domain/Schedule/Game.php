<?php

namespace DAG\Domain\Schedule;

use DAG\Domain\Domain;
use DAG\Orm\Schedule\GameOrm;
use DAG\Framework\Exception\Precondition;


/**
 * @property int        $id
 * @property Flight     $flight
 * @property Pool       $pool
 * @property GameTime   $gameTime
 * @property Team       $homeTeam
 * @property Team       $visitingTeam
 * @property string     $title
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

    /** @var string */
    private $title;

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
     *
     * @return Game
     */
    public static function create(
        $flight,
        $pool,
        $gameTime,
        $homeTeam,
        $visitingTeam,
        $title = '')
    {
        $poolId         = isset($pool) ? $pool->id : null;
        $homeTeamId     = isset($homeTeam) ? $homeTeam->id : null;
        $visitingTeamId = isset($visitingTeam) ? $visitingTeam->id : null;

        $gameOrm = GameOrm::create($flight->id, $poolId, $gameTime->id, $homeTeamId, $visitingTeamId, $title);
        return new static($gameOrm, $flight, $pool, null, $homeTeam, $visitingTeam);
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
     * @return Game
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
     *
     * @return Game[]
     */
    public static function lookupByDivisionDay($division, $day)
    {
        $gamesOnDay = [];

        $schedules = Schedule::lookupByDivision($division);
        foreach ($schedules as $schedule) {
            $flights = Flight::lookupBySchedule($schedule);

            foreach ($flights as $flight) {
                $games = Game::lookupByFlight($flight);

                foreach ($games as $game) {
                    if ($game->gameTime->gameDate->day == $day) {
                        $gamesOnDay[] = $game;
                    }
                }
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
                $this->gameOrm->title = $value;
                $this->gameOrm->save();
                break;

            case "gameTime":
                $this->gameOrm->gameTimeId = $value->id;
                $this->gameOrm->save();
                $this->gameTime = $value;
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
            DivisionField::lookupByDivisionAndField($this->pool->schedule->division, $newGameTime->field);

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