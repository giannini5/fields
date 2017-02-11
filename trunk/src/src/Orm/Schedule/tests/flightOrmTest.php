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
            self::NAME => 'TEST Flight',
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
            self::$expectedDefaults[self::NAME]);

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
        $this->assertEquals($this->defaultScheduleOrm->id,  $flightOrm->scheduleId);
        $this->assertEquals($attributes[self::NAME],        $flightOrm->name);
    }
}