<?php

namespace DAG\Domain\Schedule;

use DAG\Orm\Schedule\ORM_TestHelper;
use DAG\Orm\Schedule\ScheduleOrm;

require_once dirname(dirname(dirname(__DIR__))) . '/Orm/Schedule/tests/helper.php';



/**
 * @testSuite test Schedule
 */
class ScheduleTest extends ORM_TestHelper
{
    /**
     * Expected values on create and load
     */
    protected $expectedDefaults;
    protected $schedulesToCleanup = array();
    protected $division;

    protected function setUp()
    {
        $this->primeDatabase();

        $this->division = Division::lookupById($this->defaultDivisionOrm->id);

        $this->expectedDefaults = array(
            'name'          => 'TEST Domain schedule name',
            'scheduleType'  => ScheduleOrm::SCHEDULE_TYPE_LEAGUE,
            'gamesPerTeam'  => 6,
            'startDate'     => '2015-09-10',
            'endDate'       => '2015-11-01',
            'startTime'     => '09:00:00',
            'endTime'       => '15:30:00',
            'daysOfWeek'    => '0000001',
            'published'     => 1,
        );

        $this->schedulesToCleanup[] = Schedule::create(
            $this->division,
            $this->expectedDefaults['name'],
            $this->expectedDefaults['scheduleType'],
            $this->expectedDefaults['gamesPerTeam'],
            $this->expectedDefaults['startDate'],
            $this->expectedDefaults['endDate'],
            $this->expectedDefaults['startTime'],
            $this->expectedDefaults['endTime'],
            $this->expectedDefaults['daysOfWeek'],
            $this->expectedDefaults['published']);
    }

    protected function tearDown()
    {
        foreach ($this->schedulesToCleanup as $entity) {
            $entity->delete();
        }

        $this->clearDatabase();
    }

    public function test_create()
    {
        $schedule = $this->schedulesToCleanup[0];
        $this->validateSchedule($schedule, $this->division, $this->expectedDefaults);
    }

    public function test_lookupById()
    {
        $schedule = Schedule::lookupById($this->schedulesToCleanup[0]->id);
        $this->validateSchedule($schedule, $this->division, $this->expectedDefaults);
    }

    public function test_lookupByName()
    {
        $schedule = Schedule::lookupByName($this->division, $this->expectedDefaults['name']);
        $this->validateSchedule($schedule, $this->division, $this->expectedDefaults);
    }

    public function test_lookupByDivision()
    {
        $schedules = Schedule::lookupByDivision($this->division);
        $this->assertEquals(2, count($schedules));
    }

    public function test_lookupByDivisionPublishedOnly()
    {
        $schedules = Schedule::lookupByDivision($this->division, $this->expectedDefaults['name'], true);
        $this->assertEquals(1, count($schedules));
    }

    public function test_set()
    {
        $schedule = Schedule::lookupById($this->schedulesToCleanup[0]->id);
        $schedule->name         = 'Hello Dave';
        $schedule->scheduleType = ScheduleOrm::SCHEDULE_TYPE_TOURNAMENT;
        $schedule->gamesPerTeam = 7;
        $schedule->startDate    = '2015-09-11';
        $schedule->endDate      = '2015-10-31';
        $schedule->startTime    = '09:30:00';
        $schedule->endTime      = '15:15:00';
        $schedule->daysOfWeek   = '0000010';
        $schedule->published    = 0;
        $schedule = Schedule::lookupById($this->schedulesToCleanup[0]->id);

        $this->expectedDefaults['name']         = 'Hello Dave';
        $this->expectedDefaults['scheduleType'] = ScheduleOrm::SCHEDULE_TYPE_TOURNAMENT;
        $this->expectedDefaults['gamesPerTeam'] = 7;
        $this->expectedDefaults['startDate']    = '2015-09-11';
        $this->expectedDefaults['endDate']      = '2015-10-31';
        $this->expectedDefaults['startTime']    = '09:30:00';
        $this->expectedDefaults['endTime']      = '15:15:00';
        $this->expectedDefaults['daysOfWeek']   = '0000010';
        $this->expectedDefaults['published']    = 0;
        $this->validateSchedule($schedule, $this->division, $this->expectedDefaults);
    }

    public function validateSchedule($schedule, $division, $expectedDefaults)
    {
        $this->assertTrue($schedule->id > 0);
        $this->assertEquals($expectedDefaults['name'],          $schedule->name);
        $this->assertEquals($expectedDefaults['scheduleType'],  $schedule->scheduleType);
        $this->assertEquals($division,                          $schedule->division);
        $this->assertEquals($expectedDefaults['gamesPerTeam'],  $schedule->gamesPerTeam);
        $this->assertEquals($expectedDefaults['startDate'],     $schedule->startDate);
        $this->assertEquals($expectedDefaults['endDate'],       $schedule->endDate);
        $this->assertEquals($expectedDefaults['startTime'],       $schedule->startTime);
        $this->assertEquals($expectedDefaults['endTime'],       $schedule->endTime);
        $this->assertEquals($expectedDefaults['daysOfWeek'],    $schedule->daysOfWeek);
        $this->assertEquals($expectedDefaults['published'],    $schedule->published);
    }
}