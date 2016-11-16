<?php

namespace DAG\Domain\Schedule;

use DAG\Domain\Domain;
use DAG\Orm\Schedule\ScheduleOrm;
use DAG\Framework\Exception\Precondition;


/**
 * @property int    $id
 * @property Pool   $pool
 * @property string $name
 */
class Schedule extends Domain
{
    /** @var ScheduleOrm */
    private $scheduleOrm;

    /** @var Pool */
    private $pool;

    /**
     * @param ScheduleOrm   $scheduleOrm
     * @param Pool          $pool (defaults to null)
     */
    protected function __construct(ScheduleOrm $scheduleOrm, $pool = null)
    {
        $this->scheduleOrm = $scheduleOrm;
        $this->pool = isset($pool) ? $pool : Pool::lookupById($scheduleOrm->poolId);
    }

    /**
     * @param Pool $pool
     * @param string $name
     *
     * @return Schedule
     */
    public static function create(
        $pool,
        $name)
    {
        $scheduleOrm = ScheduleOrm::create($pool->id, $name);
        return new static($scheduleOrm, $pool);
    }

    /**
     * @param int $scheduleId
     *
     * @return Schedule
     */
    public static function lookupById($scheduleId)
    {
        $scheduleOrm = ScheduleOrm::loadById($scheduleId);
        return new static($scheduleOrm);
    }

    /**
     * @param Pool $pool
     * @param string $name
     *
     * @return Schedule
     */
    public static function lookupByName($pool, $name)
    {
        Precondition::isNonEmpty($name, 'name should not be empty');
        $scheduleOrm = ScheduleOrm::loadByPoolIdAndName($pool->id, $name);
        return new static($scheduleOrm, $pool);
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
                return $this->scheduleOrm->{$propertyName};

            case "pool":
                return $this->{$propertyName};

            default:
                Precondition::isTrue(false, "Unrecognized property: $propertyName");
        }
    }

    /**
     *  Delete the schedule
     *  TODO: Change to cascading delete
     */
    public function delete()
    {
        $this->scheduleOrm->delete();
    }
}