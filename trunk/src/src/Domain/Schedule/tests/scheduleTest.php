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
    protected static $expectedDefaults = array(
        'name'          => 'TEST Domain schedule name',
    );

    protected $schedulesToCleanup = array();
    protected $pool;

    protected function setUp()
    {
        $this->primeDatabase();

        $this->pool = Pool::lookupById($this->defaultPoolOrm->id);

        $this->schedulesToCleanup[] = Schedule::create(
            $this->pool,
            self::$expectedDefaults['name']);
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
        $this->validateSchedule($schedule, $this->pool, self::$expectedDefaults);
    }

    public function test_lookupById()
    {
        $schedule = Schedule::lookupById($this->schedulesToCleanup[0]->id);
        $this->validateSchedule($schedule, $this->pool, self::$expectedDefaults);
    }

    public function test_lookupByName()
    {
        $schedule = Schedule::lookupByName($this->pool, self::$expectedDefaults['name']);
        $this->validateSchedule($schedule, $this->pool, self::$expectedDefaults);
    }

    public function validateSchedule($schedule, $pool, $expectedDefaults)
    {
        $this->assertTrue($schedule->id > 0);
        $this->assertEquals($expectedDefaults['name'],          $schedule->name);
        $this->assertEquals($pool,                              $schedule->pool);
    }
}