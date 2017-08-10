<?php

namespace DAG\Domain\Schedule;

use DAG\Orm\Schedule\ORM_TestHelper;

require_once dirname(dirname(dirname(__DIR__))) . '/Orm/Schedule/tests/helper.php';



/**
 * @testSuite test Flight
 */
class FlightTest extends ORM_TestHelper
{
    /**
     * Expected values on create and load
     */
    protected static $expectedDefaults = array(
        'name'                      => 'TEST Domain flight name',
        'include5th6thGame'         => 0,
        'include3rd4thGame'         => 1,
        'includeSemiFinalGames'     => 0,
        'includeChampionshipGame'   => 1,
        'scheduleGames'             => 1,
    );

    protected $flightsToCleanup = array();
    protected $schedule;

    protected function setUp()
    {
        $this->primeDatabase();

        $this->schedule = Schedule::lookupById($this->defaultScheduleOrm->id);

        $this->flightsToCleanup[] = Flight::create(
            $this->schedule,
            self::$expectedDefaults['name'],
            self::$expectedDefaults['include5th6thGame'],
            self::$expectedDefaults['include3rd4thGame'],
            self::$expectedDefaults['includeSemiFinalGames'],
            self::$expectedDefaults['includeChampionshipGame']);
    }

    protected function tearDown()
    {
        foreach ($this->flightsToCleanup as $entity) {
            $entity->delete();
        }

        $this->clearDatabase();
    }

    public function test_create()
    {
        $flight = $this->flightsToCleanup[0];
        $this->validateFlight($flight, $this->schedule, self::$expectedDefaults);
    }

    public function test_lookupById()
    {
        $flight = Flight::lookupById($this->flightsToCleanup[0]->id);
        $this->validateFlight($flight, $this->schedule, self::$expectedDefaults);
    }

    public function test_lookupByScheduleName()
    {
        $flight = Flight::lookupByScheduleName($this->schedule, self::$expectedDefaults['name']);
        $this->validateFlight($flight, $this->schedule, self::$expectedDefaults);
    }

    public function test_setName()
    {
        $flight = Flight::lookupById($this->flightsToCleanup[0]->id);
        $flight->name = "Hey Dave!";
        $expectedValues = self::$expectedDefaults;
        $expectedValues['name'] = 'Hey Dave!';
        $this->validateFlight($flight, $this->schedule, $expectedValues);
    }

    public function test_setScheduleGames()
    {
        $flight = Flight::lookupById($this->flightsToCleanup[0]->id);
        $flight->scheduleGames = 0;
        $expectedValues = self::$expectedDefaults;
        $expectedValues['scheduleGames'] = 0;
        $this->validateFlight($flight, $this->schedule, $expectedValues);
    }

    public function test_lookupBySchedule()
    {
        $flights = Flight::lookupBySchedule($this->schedule);
        $this->assertEquals(2, count($flights));
    }

    public function validateFlight($flight, $schedule, $expectedDefaults)
    {
        $this->assertTrue($flight->id > 0);
        $this->assertEquals($expectedDefaults['name'],                      $flight->name);
        $this->assertEquals($expectedDefaults['include5th6thGame'],         $flight->include5th6thGame);
        $this->assertEquals($expectedDefaults['include3rd4thGame'],         $flight->include3rd4thGame);
        $this->assertEquals($expectedDefaults['includeSemiFinalGames'],     $flight->includeSemiFinalGames);
        $this->assertEquals($expectedDefaults['includeChampionshipGame'],   $flight->includeChampionshipGame);
        $this->assertEquals($expectedDefaults['scheduleGames'],             $flight->scheduleGames);
        $this->assertEquals($schedule,                                      $flight->schedule);
    }
}