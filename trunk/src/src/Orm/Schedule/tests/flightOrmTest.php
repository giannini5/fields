<?php

namespace DAG\Orm\Schedule;

require_once 'helper.php';


/**
 * @testSuite test Facility ORM
 */
class FlightOrmTest extends ORM_TestHelper
{
    /**
     * Expected values on create and load
     */
    protected static $expectedDefaults =
        [
            self::NAME                          => 'TEST Flight',
            self::INCLUDE_5TH_6TH_GAME          => 1,
            self::INCLUDE_3RD_4TH_GAME          => 0,
            self::INCLUDE_SEMI_FINAL_GAMES      => 1,
            self::INCLUDE_CHAMPIONSHIP_GAME     => 0,
        ];

    protected function setUp()
    {
        $this->primeDatabase();
    }

    protected function tearDown()
    {
        $this->clearDatabase();
    }

    public function test_create()
    {
        $flightOrm = FlightOrm::create(
            $this->defaultScheduleOrm->id,
            self::$expectedDefaults[self::NAME],
            self::$expectedDefaults[self::INCLUDE_5TH_6TH_GAME],
            self::$expectedDefaults[self::INCLUDE_3RD_4TH_GAME],
            self::$expectedDefaults[self::INCLUDE_SEMI_FINAL_GAMES],
            self::$expectedDefaults[self::INCLUDE_CHAMPIONSHIP_GAME]);

        $this->verifyExpectedAttributes($flightOrm, self::$expectedDefaults);
    }

    public function test_loadById()
    {
        $flightOrm = FlightOrm::loadById($this->defaultFlightOrm->id);
        $this->verifyExpectedAttributes($flightOrm, self::$defaultFlightOrmAttributes);
    }

    public function test_loadByScheduleName()
    {
        $flightOrm = FlightOrm::loadByScheduleIdAndName($this->defaultScheduleOrm->id, self::$defaultFlightOrmAttributes[self::NAME]);
        $this->verifyExpectedAttributes($flightOrm, self::$defaultFlightOrmAttributes);
    }

    private function verifyExpectedAttributes($flightOrm, $attributes)
    {
        $this->assertTrue($flightOrm->id > 0);
        $this->assertEquals($this->defaultScheduleOrm->id,                  $flightOrm->scheduleId);
        $this->assertEquals($attributes[self::NAME],                        $flightOrm->name);
        $this->assertEquals($attributes[self::INCLUDE_5TH_6TH_GAME],        $flightOrm->include5th6thGame);
        $this->assertEquals($attributes[self::INCLUDE_3RD_4TH_GAME],        $flightOrm->include3rd4thGame);
        $this->assertEquals($attributes[self::INCLUDE_SEMI_FINAL_GAMES],    $flightOrm->includeSemiFinalGames);
        $this->assertEquals($attributes[self::INCLUDE_CHAMPIONSHIP_GAME],   $flightOrm->includeChampionshipGame);
    }
}