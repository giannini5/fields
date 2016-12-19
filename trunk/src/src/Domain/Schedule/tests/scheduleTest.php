<?php

namespace DAG\Domain\Schedule;

use DAG\Orm\Schedule\ORM_TestHelper;

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
            'gamesPerTeam'  => 6,
        );

        $this->schedulesToCleanup[] = Schedule::create(
            $this->division,
            $this->expectedDefaults['name'],
            $this->expectedDefaults['gamesPerTeam']);
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
        $schedules = Schedule::lookupByDivision($this->division, $this->expectedDefaults['name']);
        $this->assertEquals(2, count($schedules));
    }

    public function test_set()
    {
        $schedule = Schedule::lookupById($this->schedulesToCleanup[0]->id);
        $schedule->name         = 'Hello Dave';
        $schedule->gamesPerTeam = 7;
        $schedule = Schedule::lookupById($this->schedulesToCleanup[0]->id);

        $this->expectedDefaults['name']         = 'Hello Dave';
        $this->expectedDefaults['gamesPerTeam'] = 7;
        $this->validateSchedule($schedule, $this->division, $this->expectedDefaults);
    }

    public function validateSchedule($schedule, $division, $expectedDefaults)
    {
        $this->assertTrue($schedule->id > 0);
        $this->assertEquals($expectedDefaults['name'],  $schedule->name);
        $this->assertEquals($division,                  $schedule->division);
    }
}