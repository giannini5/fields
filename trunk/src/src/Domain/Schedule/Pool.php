<?php

namespace DAG\Domain\Schedule;

use DAG\Domain\Domain;
use DAG\Orm\Schedule\PoolOrm;
use DAG\Framework\Exception\Precondition;


/**
 * @property int        $id
 * @property Flight     $flight
 * @property Schedule   $schedule
 * @property string     $name
 * @property string     $fullName
 * @property Pool       $gamesAgainstPool
 */
class Pool extends Domain
{
    /** @var PoolOrm */
    private $poolOrm;

    /** @var Flight */
    private $flight;

    /** @var Schedule */
    private $schedule;

    /** @var Pool */
    private $gamesAgainstPool;

    /**
     * @param PoolOrm   $poolOrm
     * @param Flight    $flight (defaults to null)
     * @param Schedule  $schedule (defaults to null)
     * @param Pool      $gamesAgainstPool (defaults to null)
     */
    protected function __construct(PoolOrm $poolOrm, $flight = null, $schedule = null, $gamesAgainstPool = null)
    {
        $this->poolOrm  = $poolOrm;
        $this->flight   = isset($flight) ? $flight : Flight::lookupById($poolOrm->flightId);
        $this->schedule = isset($schedule) ? $schedule : Schedule::lookupById($poolOrm->scheduleId);

        if (isset($gamesAgainstPool)) {
            $this->gamesAgainstPool = $gamesAgainstPool;
        } elseif (isset($this->poolOrm->gamesAgainstPoolId)) {
            $this->gamesAgainstPool = Pool::lookupById($this->poolOrm->gamesAgainstPoolId, $this);
        } else {
            $this->gamesAgainstPool = $this;
        }
    }

    /**
     * @param Flight    $flight
     * @param Schedule  $schedule
     * @param string    $name
     *
     * @return Pool
     */
    public static function create(
        $flight,
        $schedule,
        $name)
    {
        $poolOrm = PoolOrm::create($flight->id, $schedule->id, $name);
        return new static($poolOrm, $flight, $schedule);
    }

    /**
     * @param int   $poolId
     * @param Pool  $gamesAgainstPool (defaults to null)
     *
     * @return Pool
     */
    public static function lookupById($poolId, $gamesAgainstPool = null)
    {
        $poolOrm = PoolOrm::loadById($poolId);
        return new static($poolOrm, null, null, $gamesAgainstPool);
    }

    /**
     * @param Flight    $flight
     * @param string    $name
     *
     * @return Pool
     */
    public static function lookupByFlightName($flight, $name)
    {
        Precondition::isNonEmpty($name, 'name should not be empty');
        $poolOrm = PoolOrm::loadByFlightIdAndName($flight->id, $name);
        return new static($poolOrm, $flight, null);
    }

    /**
     * @param Flight    $flight
     *
     * @return Pool[]
     */
    public static function lookupByFlight($flight)
    {
        $pools = [];

        $poolOrms = PoolOrm::loadByFlightId($flight->id);
        foreach ($poolOrms as $poolOrm) {
            $pools[] = new static($poolOrm, $flight, null);
        }

        return $pools;
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

            case "fullName":
                return $this->flight->name . ":" . $this->poolOrm->name;

            case "flight":
            case "schedule":
            case "gamesAgainstPool":
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
            case "gamesAgainstPool":
                $oldGamesAgainstPoolId  = $this->poolOrm->gamesAgainstPoolId;
                $oldGamesAgainstPool    = $this->gamesAgainstPool;

                // Set to NULL if pass in value is null or id is same as this pool
                if (!isset($value) or $value->id == $this->id) {
                    $this->poolOrm->gamesAgainstPoolId = null;
                    $this->poolOrm->save();
                    $this->gamesAgainstPool = $this;

                    if (isset($oldGamesAgainstPoolId) and $oldGamesAgainstPoolId != $this->id) {
                        $oldGamesAgainstPool->poolOrm->gamesAgainstPoolId = null;
                        $oldGamesAgainstPool->poolOrm->save();
                        $oldGamesAgainstPool->gamesAgainstPool = $oldGamesAgainstPool;
                    }
                } else {
                    $this->poolOrm->gamesAgainstPoolId = $value->id;
                    $this->poolOrm->save();
                    $this->gamesAgainstPool = $value;

                    // Set games against for passed in pool to force reciprocity if not already set
                    if ($value->gamesAgainstPool->id != $this->id) {
                        $value->poolOrm->gamesAgainstPoolId = $this->id;
                        $value->poolOrm->save();
                        $value->gamesAgainstPool = $this;
                    }
                }
                break;

            default:
                Precondition::isTrue(false, "Not allowed to set Pool property: $propertyName");
        }
    }

    /**
     * @param $propertyName
     * @return bool true if set; false otherwise
     */
    public function __isset($propertyName)
    {
        switch ($propertyName) {
            case "id":
            case "name":
            case "schedule":
            case "flight":
                return true;

            case "gamesAgainstPool":
                return isset($this->poolOrm->gamesAgainstPoolId);

            default:
                Precondition::isTrue(false, "Unrecognized isset property: $propertyName");
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