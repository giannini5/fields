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
    protected $expectedDefaults = [];
    protected $teamsToCleanup = array();
    protected $division;
    protected $pool;

    protected function setUp()
    {
        $this->expectedDefaults = array(
            'name'   => 'TEST Domain team name',
            'nameId' => 'TEST 1',
            'region' => '4',
            'city'   => 'Temecula',
        );

        $this->primeDatabase();

        $this->division = Division::lookupById($this->defaultDivisionOrm->id);
        $this->pool     = Pool::lookupById($this->defaultPoolOrm->id);

        $this->teamsToCleanup[] = Team::create(
            $this->division,
            $this->pool,
            $this->expectedDefaults['name'],
            $this->expectedDefaults['nameId'],
            $this->expectedDefaults['region'],
            $this->expectedDefaults['city']);
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
        $this->validateTeam($team, $this->division, $this->pool, $this->expectedDefaults);
    }

    public function test_createWithNoPool()
    {
        $this->expectedDefaults['name'] = 'Team with no fool';
        $team = Team::create(
            $this->division,
            null,
            $this->expectedDefaults['name'],
            $this->expectedDefaults['nameId'],
            $this->expectedDefaults['region'],
            $this->expectedDefaults['city']);
        $this->teamsToCleanup[] = $team;

        $this->validateTeam($team, $this->division, null, $this->expectedDefaults);
    }

    public function test_lookupById()
    {
        $team = Team::lookupById($this->teamsToCleanup[0]->id);
        $this->validateTeam($team, $this->division, $this->pool, $this->expectedDefaults);
    }

    public function test_lookupByName()
    {
        $team = Team::lookupByName($this->division, $this->expectedDefaults['name']);
        $this->validateTeam($team, $this->division, $this->pool, $this->expectedDefaults);
    }

    public function test_lookupByDivision()
    {
        $teams = Team::lookupByDivision($this->division);
        $this->assertEquals(3, count($teams));
    }

    public function test_lookupByPool()
    {
        $teams = Team::lookupByPool($this->pool);
        $this->assertEquals(3, count($teams));
    }

    public function validateTeam($team, $division, $pool, $expectedDefaults)
    {
        $this->assertTrue($team->id > 0);
        $this->assertEquals($expectedDefaults['name'],   $team->name);
        $this->assertEquals($expectedDefaults['nameId'], $team->nameId);
        $this->assertEquals($expectedDefaults['region'], $team->region);
        $this->assertEquals($expectedDefaults['city'],   $team->city);
        $this->assertEquals($division,                   $team->division);
        $this->assertEquals($pool,                       $team->pool);
    }
}