<?php

namespace DAG\Domain\Schedule;

use DAG\Domain\Domain;
use DAG\Orm\Schedule\GameOrm;
use DAG\Framework\Exception\Precondition;


/**
 * @property int        $id
 * @property Schedule   $schedule
 * @property GameTime   $gameTime
 * @property Team       $homeTeam
 * @property Team       $visitingTeam
 */
class Game extends Domain
{
    /** @var GameOrm */
    private $gameOrm;

    /** @var Schedule */
    private $schedule;

    /** @var GameTime */
    private $gameTime;

    /** @var Team */
    private $homeTeam;

    /** @var Team */
    private $visitingTeam;

    /**
     * @param GameOrm   $gameOrm
     * @param Schedule  $schedule (defaults to null)
     * @param GameTime  $gameTime (defaults to null)
     * @param Team      $homeTeam (defaults to null)
     * @param Team      $visitingTeam (defaults to null)
     */
    protected function __construct(GameOrm $gameOrm, $schedule = null, $gameTime = null, $homeTeam = null, $visitingTeam = null)
    {
        $this->gameOrm      = $gameOrm;
        $this->schedule     = isset($schedule) ? $schedule : Schedule::lookupById($gameOrm->scheduleId);
        $this->gameTime     = isset($gameTime) ? $gameTime : GameTime::lookupById($gameOrm->gameTimeId);
        $this->homeTeam     = isset($homeTeam) ? $homeTeam : Team::lookupById($gameOrm->homeTeamId);
        $this->visitingTeam = isset($visitingTeam) ? $visitingTeam : Team::lookupById($gameOrm->visitingTeamId);
    }

    /**
     * @param Schedule  $schedule
     * @param GameTime  $gameTime
     * @param Team      $homeTeam
     * @param Team      $visitingTeam
     *
     * @return Game
     */
    public static function create(
        $schedule,
        $gameTime,
        $homeTeam,
        $visitingTeam)
    {
        $gameOrm = GameOrm::create($schedule->id, $gameTime->id, $homeTeam->id, $visitingTeam->id);
        return new static($gameOrm, $schedule, $gameTime, $homeTeam, $visitingTeam);
    }

    /**
     * @param int $gameId
     *
     * @return Game
     */
    public static function lookupById($gameId)
    {
        $gameOrm = GameOrm::loadById($gameId);
        return new static($gameOrm);
    }

    /**
     * @param Schedule      $schedule
     *
     * @return Game
     */
    public static function lookupBySchedule($schedule)
    {
        $games = [];

        $gameOrms = GameOrm::loadByScheduleId($schedule->id);
        foreach ($gameOrms as $gameOrm) {
            $games[] = new static($gameOrm, $schedule);
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
     * @param $propertyName
     * @return int|string
     */
    public function __get($propertyName)
    {
        switch ($propertyName) {
            case "id":
                return $this->gameOrm->{$propertyName};

            case "schedule":
            case "gameTime":
            case "homeTeam":
            case "visitingTeam":
                return $this->{$propertyName};

            default:
                Precondition::isTrue(false, "Unrecognized property: $propertyName");
        }
    }

    /**
     *  Delete the game
     *  TODO: Change to cascading delete
     */
    public function delete()
    {
        $this->gameOrm->delete();
    }
}