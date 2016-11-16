<?php

namespace DAG\Orm\Schedule;

require_once 'helper.php';


/**
 * @testSuite test League ORM
 */
class LeagueOrmTest extends ORM_TestHelper
{
    /**
     * Expected values on create and load
     */
    protected static $expectedDefaults = array(
        'name' => 'TEST league name',
    );

    protected $leaguesToCleanup = array();

    protected function setUp()
    {
        $this->primeDatabase();

        $this->leaguesToCleanup[] = LeagueOrm::create(self::$expectedDefaults['name']);
    }

    protected function tearDown()
    {
        foreach ($this->leaguesToCleanup as $entityOrm) {
            $entityOrm->delete();
        }

        $this->clearDatabase();
    }

    public function test_create()
    {
        $league = LeagueOrm::create('TEST Hello World');
        $this->leaguesToCleanup[] = $league;

        $this->assertTrue($league->id > 0);
        $this->assertEquals('TEST Hello World', $league->name);
    }

    public function test_loadById()
    {
        $league = LeagueOrm::loadById($this->leaguesToCleanup[0]->id);

        $this->assertEquals($this->leaguesToCleanup[0]->id, $league->id);
        $this->assertEquals(self::$expectedDefaults['name'], $league->name);
    }

    public function test_loadByName()
    {
        $league = LeagueOrm::loadByName(self::$expectedDefaults['name']);

        $this->assertEquals($this->leaguesToCleanup[0]->id, $league->id);
        $this->assertEquals(self::$expectedDefaults['name'], $league->name);
    }
}