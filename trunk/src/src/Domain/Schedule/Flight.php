<?php

namespace DAG\Domain\Schedule;

use DAG\Domain\Domain;
use DAG\Orm\Schedule\FlightOrm;
use DAG\Framework\Exception\Precondition;


/**
 * @property int        $id
 * @property Schedule   $schedule
 * @property string     $name
 * @property int        $include5th6thGame
 * @property int        $include3rd4thGame
 * @property int        $includeSemiFinalGames
 * @property int        $includeChampionshipGame
 * @property int        $scheduleGames
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
     * @param int       $include5th6thGame;
     * @param int       $include3rd4thGame;
     * @param int       $includeSemiFinalGames;
     * @param int       $includeChampionshipGame;
     * @param int       $scheduleGames
     *
     * @return Flight
     */
    public static function create(
        $schedule,
        $name,
        $include5th6thGame,
        $include3rd4thGame,
        $includeSemiFinalGames,
        $includeChampionshipGame,
        $scheduleGames = 1)
    {
        $flightOrm = FlightOrm::create(
            $schedule->id,
            $name,
            $include5th6thGame,
            $include3rd4thGame,
            $includeSemiFinalGames,
            $includeChampionshipGame,
            $scheduleGames);
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
            case "include5th6thGame":
            case "include3rd4thGame":
            case "includeSemiFinalGames":
            case "includeChampionshipGame":
            case "scheduleGames":
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
            case "include5th6thGame":
            case "include3rd4thGame":
            case "includeSemiFinalGames":
            case "includeChampionshipGame":
            case "scheduleGames":
                $this->flightOrm->{$propertyName} = $value;
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
        // Delete games
        $games = Game::lookupByFlight($this);
        foreach ($games as $game) {
            $game->delete();
        }

        // Delete pools
        $pools = Pool::lookupByFlight($this);
        foreach ($pools as $pool) {
            $pool->delete();
        }

        // Delete flight
        $this->flightOrm->delete();
    }
}