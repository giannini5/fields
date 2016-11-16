<?php

namespace DAG\Domain\Schedule;

use DAG\Orm\Schedule\ORM_TestHelper;

require_once dirname(dirname(dirname(__DIR__))) . '/Orm/Schedule/tests/helper.php';



/**
 * @testSuite test Team
 */
class TeamTest extends ORM_TestHelper
{
    /**
     * Expected values on create and load
     */
    protected static $expectedDefaults = array(
        'name'          => 'TEST Domain team name',
    );

    protected $teamsToCleanup = array();
    protected $division;
    protected $pool;

    protected function setUp()
    {
        $this->primeDatabase();

        $this->division = Division::lookupById($this->defaultDivisionOrm->id);
        $this->pool     = Pool::lookupById($this->defaultPoolOrm->id);

        $this->teamsToCleanup[] = Team::create(
            $this->division,
            $this->pool,
            self::$expectedDefaults['name']);
    }

    protected function tearDown()
    {
        foreach ($this->teamsToCleanup as $entity) {
            $entity->delete();
        }

        $this->clearDatabase();
    }

    public function test_create()
    {
        $team = $this->teamsToCleanup[0];
        $this->validateTeam($team, $this->division, $this->pool, self::$expectedDefaults);
    }

    public function test_createWithNoPool()
    {
        self::$expectedDefaults['name'] = 'Team with no pool';
        $team = Team::create(
            $this->division,
            null,
            self::$expectedDefaults['name']);
        $this->teamsToCleanup[] = $team;

        $this->validateTeam($team, $this->division, null, self::$expectedDefaults);
    }

    public function test_lookupById()
    {
        $team = Team::lookupById($this->teamsToCleanup[0]->id);
        $this->validateTeam($team, $this->division, $this->pool, self::$expectedDefaults);
    }

    public function test_lookupByName()
    {
        $team = Team::lookupByName($this->division, self::$expectedDefaults['name']);
        $this->validateTeam($team, $this->division, $this->pool, self::$expectedDefaults);
    }

    public function validateTeam($team, $division, $pool, $expectedDefaults)
    {
        $this->assertTrue($team->id > 0);
        $this->assertEquals($expectedDefaults['name'],  $team->name);
        $this->assertEquals($division,                  $team->division);
        $this->assertEquals($pool,                      $team->pool);
    }
}