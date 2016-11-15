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
    protected $division;

    protected function setUp()
    {
        $this->primeDatabase();

        $this->division = Division::lookupById($this->defaultDivisionOrm->id);

        $this->poolsToCleanup[] = Pool::create(
            $this->division,
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
        $this->validatePool($pool, $this->division, self::$expectedDefaults);
    }

    public function test_lookupById()
    {
        $pool = Pool::lookupById($this->poolsToCleanup[0]->id);
        $this->validatePool($pool, $this->division, self::$expectedDefaults);
    }

    public function test_lookupByName()
    {
        $pool = Pool::lookupByName($this->division, self::$expectedDefaults['name']);
        $this->validatePool($pool, $this->division, self::$expectedDefaults);
    }

    public function validatePool($pool, $division, $expectedDefaults)
    {
        $this->assertTrue($pool->id > 0);
        $this->assertEquals($expectedDefaults['name'],  $pool->name);
        $this->assertEquals($division,                  $pool->division);
    }
}