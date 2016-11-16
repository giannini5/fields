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
            self::NAME => 'TEST Schedule',
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
            $this->defaultPoolOrm->id,
            self::$expectedDefaults[self::NAME]);

        $this->verifyExpectedAttributes($scheduleOrm, self::$expectedDefaults);
    }

    public function test_loadById()
    {
        $scheduleOrm = ScheduleOrm::loadById($this->defaultScheduleOrm->id);
        $this->verifyExpectedAttributes($scheduleOrm, self::$defaultScheduleOrmAttributes);
    }

    public function test_loadByName()
    {
        $scheduleOrm = ScheduleOrm::loadByPoolIdAndName($this->defaultPoolOrm->id, self::$defaultScheduleOrmAttributes[self::NAME]);
        $this->verifyExpectedAttributes($scheduleOrm, self::$defaultScheduleOrmAttributes);
    }

    private function verifyExpectedAttributes($scheduleOrm, $attributes)
    {
        $this->assertTrue($scheduleOrm->id > 0);
        $this->assertEquals($this->defaultPoolOrm->id,  $scheduleOrm->poolId);
        $this->assertEquals($attributes[self::NAME],        $scheduleOrm->name);
    }
}