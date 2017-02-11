<?php

namespace DAG\Domain\Schedule;

use DAG\Domain\Domain;
use DAG\Orm\Schedule\FlightOrm;
use DAG\Framework\Exception\Precondition;


/**
 * @property int        $id
 * @property Schedule   $schedule
 * @property string     $name
 */
class Flight extends Domain
{
    /** @var FlightOrm */
    private $flightOrm;

    /** @var Schedule */
    private $schedule;

    /**
     * @param FlightOrm   $flightOrm
     * @param Schedule    $schedule (defaults to null)
     */
    protected function __construct(FlightOrm $flightOrm, $schedule = null)
    {
        $this->flightOrm = $flightOrm;
        $this->schedule = isset($schedule) ? $schedule : Schedule::lookupById($flightOrm->scheduleId);
    }

    /**
     * @param Schedule  $schedule
     * @param string    $name
     *
     * @return Flight
     */
    public static function create(
        $schedule,
        $name)
    {
        $flightOrm = FlightOrm::create($schedule->id, $name);
        return new static($flightOrm, $schedule);
    }

    /**
     * @param int   $flightId
     *
     * @return Flight
     */
    public static function lookupById($flightId)
    {
        $flightOrm = FlightOrm::loadById($flightId);
        return new static($flightOrm);
    }

    /**
     * @param Schedule  $schedule
     * @param string    $name
     *
     * @return Flight
     */
    public static function lookupByScheduleName($schedule, $name)
    {
        Precondition::isNonEmpty($name, 'name should not be empty');
        $flightOrm = FlightOrm::loadByScheduleIdAndName($schedule->id, $name);
        return new static($flightOrm, $schedule);
    }

    /**
     * @param Schedule  $schedule
     *
     * @return Flight[]
     */
    public static function lookupBySchedule($schedule)
    {
        $flights = [];

        $flightOrms = FlightOrm::loadByScheduleId($schedule->id);
        foreach ($flightOrms as $flightOrm) {
            $flights[] = new static($flightOrm, $schedule);
        }

        return $flights;
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
                return $this->flightOrm->{$propertyName};

            case "schedule":
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
            case "name":
                $this->flightOrm->name = $value;
                $this->flightOrm->save();
                break;

            default:
                Precondition::isTrue(false, "Unrecognized property: $propertyName");
        }
    }

    /**
     *  Delete the flight
     */
    public function delete()
    {
        // TODO: Delete all pools

        // Delete flight
        $this->flightOrm->delete();
    }
}