<?php

namespace DAG\Domain\Schedule;

use DAG\Orm\Schedule\ORM_TestHelper;

require_once dirname(dirname(dirname(__DIR__))) . '/Orm/Schedule/tests/helper.php';

/**
 * @testSuite test League
 */
class LeagueTest extends ORM_TestHelper
{
    /**
     * Expected values on create and load
     */
    protected static $expectedDefaults = array(
        'name' => 'TEST Domain league name',
    );

    protected $leaguesToCleanup = array();

    protected function setUp()
    {
        $this->primeDatabase();

        $this->leaguesToCleanup[] = League::create(self::$expectedDefaults['name']);
    }

    protected function tearDown()
    {
        foreach ($this->leaguesToCleanup as $entity) {
            $entity->delete();
        }

        $this->clearDatabase();
    }

    public function test_create()
    {
        $league = League::create('TEST Domain Hello World');
        $this->leaguesToCleanup[] = $league;

        $this->assertTrue($league->id > 0);
        $this->assertEquals('TEST Domain Hello World', $league->name);
    }

    public function test_lookupById()
    {
        $league = League::lookupById($this->leaguesToCleanup[0]->id);

        $this->assertEquals($this->leaguesToCleanup[0]->id, $league->id);
        $this->assertEquals(self::$expectedDefaults['name'], $league->name);
    }

    public function test_lookupByName()
    {
        $league = League::lookupByName(self::$expectedDefaults['name']);

        $this->assertEquals($this->leaguesToCleanup[0]->id, $league->id);
        $this->assertEquals(self::$expectedDefaults['name'], $league->name);
    }
}