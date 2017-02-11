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
            self::SCHEDULE_TYPE     => ScheduleOrm::SCHEDULE_TYPE_LEAGUE,
            self::GAMES_PER_TEAM    => 5,
            self::START_DATE        => '2015-09-03',
            self::END_DATE          => '2015-09-20',
            self::START_TIME        => '10:00:00',
            self::END_TIME          => '15:00:00',
            self::DAYS_OF_WEEK      => '1100000',
            self::PUBLISHED         => 1,
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
            self::$expectedDefaults[self::SCHEDULE_TYPE],
            self::$expectedDefaults[self::GAMES_PER_TEAM],
            self::$expectedDefaults[self::START_DATE],
            self::$expectedDefaults[self::END_DATE],
            self::$expectedDefaults[self::START_TIME],
            self::$expectedDefaults[self::END_TIME],
            self::$expectedDefaults[self::DAYS_OF_WEEK],
            self::$expectedDefaults[self::PUBLISHED]);

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
        $this->assertEquals($this->defaultDivisionOrm->id,      $scheduleOrm->divisionId);
        $this->assertEquals($attributes[self::NAME],            $scheduleOrm->name);
        $this->assertEquals($attributes[self::SCHEDULE_TYPE],   $scheduleOrm->scheduleType);
        $this->assertEquals($attributes[self::START_DATE],      $scheduleOrm->startDate);
        $this->assertEquals($attributes[self::END_DATE],        $scheduleOrm->endDate);
        $this->assertEquals($attributes[self::START_TIME],      $scheduleOrm->startTime);
        $this->assertEquals($attributes[self::END_TIME],        $scheduleOrm->endTime);
        $this->assertEquals($attributes[self::DAYS_OF_WEEK],    $scheduleOrm->daysOfWeek);
        $this->assertEquals($attributes[self::PUBLISHED],       $scheduleOrm->published);
    }
}