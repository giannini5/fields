<?php

namespace DAG\Orm\Schedule;

require_once 'helper.php';


/**
 * @testSuite test Facility ORM
 */
class ScheduleOrmTest extends ORM_TestHelper
{
    /**
     * Expected values on create and load
     */
    protected static $expectedDefaults =
        [
            self::NAME              => 'TEST Schedule',
            self::GAMES_PER_TEAM    => 5,
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
        $scheduleOrm = ScheduleOrm::create(
            $this->defaultDivisionOrm->id,
            self::$expectedDefaults[self::NAME],
            self::$expectedDefaults[self::GAMES_PER_TEAM]);

        $this->verifyExpectedAttributes($scheduleOrm, self::$expectedDefaults);
    }

    public function test_loadById()
    {
        $scheduleOrm = ScheduleOrm::loadById($this->defaultScheduleOrm->id);
        $this->verifyExpectedAttributes($scheduleOrm, self::$defaultScheduleOrmAttributes);
    }

    public function test_loadByName()
    {
        $scheduleOrm = ScheduleOrm::loadByDivisionIdAndName($this->defaultDivisionOrm->id, self::$defaultScheduleOrmAttributes[self::NAME]);
        $this->verifyExpectedAttributes($scheduleOrm, self::$defaultScheduleOrmAttributes);
    }

    public function test_loadByDivision()
    {
        $scheduleOrms = ScheduleOrm::loadByDivisionId($this->defaultDivisionOrm->id);
        $this->assertEquals(1, count($scheduleOrms));
        $this->verifyExpectedAttributes($scheduleOrms[0], self::$defaultScheduleOrmAttributes);
    }

    private function verifyExpectedAttributes($scheduleOrm, $attributes)
    {
        $this->assertTrue($scheduleOrm->id > 0);
        $this->assertEquals($this->defaultDivisionOrm->id,  $scheduleOrm->divisionId);
        $this->assertEquals($attributes[self::NAME],        $scheduleOrm->name);
    }
}