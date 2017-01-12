<?php

namespace DAG\Domain\Schedule;

use DAG\Domain\Domain;
use DAG\Orm\Schedule\GameOrm;
use DAG\Framework\Exception\Precondition;


/**
 * @property int        $id
 * @property Pool       $pool
 * @property GameTime   $gameTime
 * @property Team       $homeTeam
 * @property Team       $visitingTeam
 */
class Game extends Domain
{
    /** @var GameOrm */
    private $gameOrm;

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
     * @param Pool      $pool (defaults to null)
     * @param GameTime  $gameTime (defaults to null)
     * @param Team      $homeTeam (defaults to null)
     * @param Team      $visitingTeam (defaults to null)
     */
    protected function __construct(GameOrm $gameOrm, $pool = null, $gameTime = null, $homeTeam = null, $visitingTeam = null)
    {
        $this->gameOrm      = $gameOrm;
        $this->pool         = isset($pool) ? $pool : Pool::lookupById($gameOrm->poolId);
        $this->homeTeam     = isset($homeTeam) ? $homeTeam : Team::lookupById($gameOrm->homeTeamId);
        $this->visitingTeam = isset($visitingTeam) ? $visitingTeam : Team::lookupById($gameOrm->visitingTeamId);
        $this->gameTime     = isset($gameTime) ? $gameTime : GameTime::lookupById($gameOrm->gameTimeId, $this);
    }

    /**
     * @param Pool      $pool
     * @param GameTime  $gameTime
     * @param Team      $homeTeam
     * @param Team      $visitingTeam
     *
     * @return Game
     */
    public static function create(
        $pool,
        $gameTime,
        $homeTeam,
        $visitingTeam)
    {
        $gameOrm = GameOrm::create($pool->id, $gameTime->id, $homeTeam->id, $visitingTeam->id);
        return new static($gameOrm, $pool, null, $homeTeam, $visitingTeam);
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
        return new static($gameOrm, null, $gameTime);
    }

    /**
     * @param Pool      $pool
     *
     * @return Game[]
     */
    public static function lookupByPool($pool)
    {
        $games = [];

        $gameOrms = GameOrm::loadByPoolId($pool->id);
        foreach ($gameOrms as $gameOrm) {
            $games[] = new static($gameOrm, $pool);
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
        return new static($gameOrm, null, $gameTime);
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
            $pools = Pool::lookupBySchedule($schedule);

            foreach ($pools as $pool) {
                $games = Game::lookupByPool($pool);

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
                return $this->gameOrm->{$propertyName};

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