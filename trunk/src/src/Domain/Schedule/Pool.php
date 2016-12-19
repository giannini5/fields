<?php

namespace DAG\Domain\Schedule;

use DAG\Domain\Domain;
use DAG\Orm\Schedule\PoolOrm;
use DAG\Framework\Exception\Precondition;


/**
 * @property int        $id
 * @property Schedule   $schedule
 * @property string     $name
 */
class Pool extends Domain
{
    /** @var PoolOrm */
    private $poolOrm;

    /** @var Schedule */
    private $schedule;

    /**
     * @param PoolOrm   $poolOrm
     * @param Schedule  $schedule (defaults to null)
     */
    protected function __construct(PoolOrm $poolOrm, $division = null, $schedule = null)
    {
        $this->poolOrm = $poolOrm;
        $this->schedule = isset($schedule) ? $schedule : Schedule::lookupById($poolOrm->scheduleId);
    }

    /**
     * @param Schedule  $schedule
     * @param string    $name
     *
     * @return Pool
     */
    public static function create(
        $schedule,
        $name)
    {
        $poolOrm = PoolOrm::create($schedule->id, $name);
        return new static($poolOrm, $schedule);
    }

    /**
     * @param int $poolId
     *
     * @return Pool
     */
    public static function lookupById($poolId)
    {
        $poolOrm = PoolOrm::loadById($poolId);
        return new static($poolOrm);
    }

    /**
     * @param Schedule  $schedule
     * @param string    $name
     *
     * @return Pool
     */
    public static function lookupByScheduleName($schedule, $name)
    {
        Precondition::isNonEmpty($name, 'name should not be empty');
        $poolOrm = PoolOrm::loadByScheduleIdAndName($schedule->id, $name);
        return new static($poolOrm, null, $schedule);
    }

    /**
     * @param Schedule  $schedule
     *
     * @return Pool[]
     */
    public static function lookupBySchedule($schedule)
    {
        $pools = [];

        $poolOrms = PoolOrm::loadByScheduleId($schedule->id);
        foreach ($poolOrms as $poolOrm) {
            $pools[] = new static($poolOrm, null, $schedule);
        }

        return $pools;
    }

    /**
     * @param $propertyName
     * @return int|string
     */
    public function __get($propertyName)
    {
        switch ($propertyName) {
            case "id":
            case "name":
                return $this->poolOrm->{$propertyName};

            case "schedule":
                return $this->{$propertyName};

            default:
                Precondition::isTrue(false, "Unrecognized property: $propertyName");
        }
    }

    /**
     *  Delete the pool
     */
    public function delete()
    {
        // Delete all games
        $games = Game::lookupByPool($this);
        foreach ($games as $game) {
            $game->delete();
        }

        // Delete pool
        $this->poolOrm->delete();
    }
}