<?php

namespace DAG\Domain\Schedule;

use DAG\Orm\Schedule\ORM_TestHelper;

require_once dirname(dirname(dirname(__DIR__))) . '/Orm/Schedule/tests/helper.php';



/**
 * @testSuite test TeamPolygon
 */
class TeamPolygonTest extends ORM_TestHelper
{
    /**
     * Expected values on create and load
     */
    protected $division;
    protected $schedule;
    protected $pool;
    protected $teams = [];

    protected function setUp()
    {
        $this->expectedDefaults = array(
            'name' => 'Team 1',
        );

        $this->primeDatabase();

        $this->division = Division::lookupById($this->defaultDivisionOrm->id);
        $this->schedule = Schedule::lookupByName($this->division, $this->defaultScheduleOrm->name);
        $this->pool     = Pool::create($this->schedule, 'Test TeamPolygon');

        // Add six teams to pool
        for ($i = 1; $i <= 6; $i++) {
            $this->teams[] = Team::create($this->division, $this->pool, "TeamPolygon $i");
        }
    }

    protected function tearDown()
    {
        foreach ($this->teams as $team) {
            $team->delete();
        }

        $this->pool->delete();

        $this->clearDatabase();
    }

    public function test_getTeamPairings()
    {
        $teamPolygon    = new TeamPolygon($this->teams);
        $teamPairings   = $teamPolygon->getTeamPairings();

        $this->assertTrue($teamPairings[0] == 5);
        $this->assertTrue($teamPairings[1] == 4);
        $this->assertTrue($teamPairings[2] == 3);
    }

    public function test_shift()
    {
        $teamPolygon = new TeamPolygon($this->teams);
        $teamPolygon->getTeamPairings();
        $teamPolygon->shift();
        $teamPairings = $teamPolygon->getTeamPairings();

        $this->assertTrue($teamPairings[5] == 4);
        $this->assertTrue($teamPairings[0] == 3);
        $this->assertTrue($teamPairings[1] == 2);
    }
}