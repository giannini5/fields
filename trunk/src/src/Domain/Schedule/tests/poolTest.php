<?php

namespace DAG\Domain\Schedule;

use DAG\Orm\Schedule\ORM_TestHelper;

require_once dirname(dirname(dirname(__DIR__))) . '/Orm/Schedule/tests/helper.php';



/**
 * @testSuite test Pool
 */
class PoolTest extends ORM_TestHelper
{
    /**
     * Expected values on create and load
     */
    protected static $expectedDefaults = array(
        'name'          => 'TEST Domain pool name',
    );

    protected $poolsToCleanup = array();
    protected $schedule;

    protected function setUp()
    {
        $this->primeDatabase();

        $this->schedule = Schedule::lookupById($this->defaultScheduleOrm->id);

        $this->poolsToCleanup[] = Pool::create(
            $this->schedule,
            self::$expectedDefaults['name']);
    }

    protected function tearDown()
    {
        foreach ($this->poolsToCleanup as $entity) {
            $entity->delete();
        }

        $this->clearDatabase();
    }

    public function test_create()
    {
        $pool = $this->poolsToCleanup[0];
        $this->validatePool($pool, $this->schedule, self::$expectedDefaults);
    }

    public function test_lookupById()
    {
        $pool = Pool::lookupById($this->poolsToCleanup[0]->id);
        $this->validatePool($pool, $this->schedule, self::$expectedDefaults);
    }

    public function test_lookupByScheduleName()
    {
        $pool = Pool::lookupByScheduleName($this->schedule, self::$expectedDefaults['name']);
        $this->validatePool($pool, $this->schedule, self::$expectedDefaults);
    }

    public function test_lookupBySchedule()
    {
        $pools = Pool::lookupBySchedule($this->schedule);
        $this->assertEquals(2, count($pools));
    }

    public function test_gamesAgainstPool()
    {
        $pool1 = $this->poolsToCleanup[0];
        $pool2 = Pool::lookupById($this->defaultPoolOrm->id);

        $this->assertEquals($pool1->id, $pool1->gamesAgainstPool->id);
        $this->assertEquals($pool2->id, $pool2->gamesAgainstPool->id);

        $pool1->gamesAgainstPool = $pool2;
        $this->assertEquals($pool1->id, $pool2->gamesAgainstPool->id);
        $this->assertEquals($pool2->id, $pool1->gamesAgainstPool->id);

        $pool1 = Pool::lookupById($this->poolsToCleanup[0]->id);
        $pool2 = Pool::lookupById($this->defaultPoolOrm->id);
        $this->assertEquals($pool1->id, $pool2->gamesAgainstPool->id);
        $this->assertEquals($pool2->id, $pool1->gamesAgainstPool->id);
    }

    public function validatePool($pool, $schedule, $expectedDefaults)
    {
        $this->assertTrue($pool->id > 0);
        $this->assertEquals($expectedDefaults['name'],  $pool->name);
        $this->assertEquals($schedule,                  $pool->schedule);
    }
}