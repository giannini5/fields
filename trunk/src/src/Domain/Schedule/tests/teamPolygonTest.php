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
    protected $flight;
    protected $pool;
    protected $crossPool;
    protected $oddPool;
    protected $teams            = [];
    protected $crossPoolTeams   = [];
    protected $oddPoolTeams   = [];

    protected function setUp()
    {
        $this->primeDatabase();

        $this->division     = Division::lookupById($this->defaultDivisionOrm->id);
        $this->schedule     = Schedule::lookupByName($this->division, $this->defaultScheduleOrm->name);
        $this->flight       = Flight::lookupByScheduleName($this->schedule, $this->defaultFlightOrm->name);
        $this->pool         = Pool::create($this->flight, $this->schedule, 'Test TeamPolygon');
        $this->crossPool    = Pool::create($this->flight, $this->schedule, 'Test TeamPolygon Cross Pool');
        $this->oddPool      = Pool::create($this->flight, $this->schedule, 'Test TeamPolygon Odd Pool');

        // Add six teams to pool
        for ($i = 1; $i <= 6; $i++) {
            $this->teams[] = Team::create($this->division, $this->pool, "TeamPolygon $i", '', '', '');
        }

        // Add six teams to cross pool
        for ($i = 7; $i <= 12; $i++) {
            $this->crossPoolTeams[] = Team::create($this->division, $this->pool, "Corss Pool TeamPolygon $i", '', '', '');
        }

        // Add five teams to odd pool
        for ($i = 13; $i <= 17; $i++) {
            $this->oddPoolTeams[] = Team::create($this->division, $this->oddPool, "Odd Pool TeamPolygon $i", '', '', '');
        }
    }

    protected function tearDown()
    {
        foreach ($this->teams as $team) {
            $team->delete();
        }

        foreach ($this->crossPoolTeams as $team) {
            $team->delete();
        }

        foreach ($this->oddPoolTeams as $team) {
            $team->delete();
        }

        $this->pool->delete();
        $this->crossPool->delete();
        $this->oddPool->delete();

        $this->clearDatabase();
    }

    public function test_getTeamPairingsRoundRobinEven()
    {
        $teamPolygon    = new TeamPolygon($this->teams);
        $teamPairings   = $teamPolygon->getTeamPairings();

        $this->verifyParing($teamPairings, 0, 5);
        $this->verifyParing($teamPairings, 1, 4);
        $this->verifyParing($teamPairings, 2, 3);
    }

    public function test_getTeamPairingsRoundRobinOdd()
    {
        $teamPolygon    = new TeamPolygon($this->oddPoolTeams, TeamPolygon::ROUND_ROBIN_ODD);
        $teamPairings   = $teamPolygon->getTeamPairings();

        $this->verifyParing($teamPairings, 0, 3);
        $this->verifyParing($teamPairings, 1, 2);
        $this->verifyParing($teamPairings, 4, 0);
    }

    public function test_getTeamPairingsRoundRobinOddTournament()
    {
        $teamPolygon    = new TeamPolygon($this->oddPoolTeams, TeamPolygon::ROUND_ROBIN_ODD_TOURNAMENT);
        $teamPairings   = $teamPolygon->getTeamPairings();

        $this->assertEquals(2, count($teamPairings));

        $this->verifyParing($teamPairings, 1, 4);
        $this->verifyParing($teamPairings, 2, 3);
    }

    public function test_getTeamPairingsRoundRobinOddTournamentWithShift()
    {
        $teamPolygon = new TeamPolygon($this->oddPoolTeams, TeamPolygon::ROUND_ROBIN_ODD_TOURNAMENT);

        $teamPairings = $teamPolygon->getTeamPairings();
        $this->assertEquals(2, count($teamPairings));
        $this->verifyParing($teamPairings, 1, 4);
        $this->verifyParing($teamPairings, 2, 3);

        $teamPolygon->shift();
        $teamPairings = $teamPolygon->getTeamPairings();
        $this->assertEquals(2, count($teamPairings));
        $this->verifyParing($teamPairings, 0, 3);
        $this->verifyParing($teamPairings, 1, 2);

        $teamPolygon->shift();
        $teamPairings = $teamPolygon->getTeamPairings();
        $this->assertEquals(2, count($teamPairings));
        $this->verifyParing($teamPairings, 4, 2);
        $this->verifyParing($teamPairings, 0, 1);

        $teamPolygon->shift();
        $teamPairings = $teamPolygon->getTeamPairings();
        $this->assertEquals(2, count($teamPairings));
        $this->verifyParing($teamPairings, 3, 1);
        $this->verifyParing($teamPairings, 4, 0);

        $teamPolygon->shift();
        $teamPairings = $teamPolygon->getTeamPairings();
        $this->assertEquals(2, count($teamPairings));
        $this->verifyParing($teamPairings, 2, 0);
        $this->verifyParing($teamPairings, 3, 4);
    }

    public function test_getTeamPairingsRoundRobinOddTournamentWithShiftThreeTeams()
    {
        $oddPoolTeams = [];
        for ($i = 0; $i < 3; $i++) {
            $oddPoolTeams[] = $this->oddPoolTeams[$i];
        }

        $teamPolygon = new TeamPolygon($oddPoolTeams, TeamPolygon::ROUND_ROBIN_ODD_TOURNAMENT);
        $teamPairings = $teamPolygon->getTeamPairings();

        $this->assertEquals(1, count($teamPairings));
        $this->verifyParing($teamPairings, 1, 2);

        $teamPolygon->shift();
        $teamPairings = $teamPolygon->getTeamPairings();
        $this->assertEquals(1, count($teamPairings));
        $this->verifyParing($teamPairings, 0, 1);

        $teamPolygon->shift();
        $teamPairings = $teamPolygon->getTeamPairings();
        $this->assertEquals(1, count($teamPairings));
        $this->verifyParing($teamPairings, 2, 0);
    }

    public function test_getTeamPairingsCrossPoolEven()
    {
        $teamPolygon    = new TeamPolygon($this->teams, TeamPolygon::CROSS_POOL_EVEN, $this->crossPoolTeams);
        $teamPairings   = $teamPolygon->getTeamPairings();

        $this->verifyParing($teamPairings, 0, 0);
        $this->verifyParing($teamPairings, 1, 1);
        $this->verifyParing($teamPairings, 2, 2);
        $this->verifyParing($teamPairings, 3, 3);
        $this->verifyParing($teamPairings, 4, 4);
        $this->verifyParing($teamPairings, 5, 5);
    }

    public function test_shiftRoundRobinEven()
    {
        $teamPolygon = new TeamPolygon($this->teams);
        $teamPolygon->getTeamPairings();
        $teamPolygon->shift();
        $teamPairings = $teamPolygon->getTeamPairings();

        $this->verifyParing($teamPairings, 4, 5);
        $this->verifyParing($teamPairings, 0, 3);
        $this->verifyParing($teamPairings, 1, 2);
    }

    public function test_shiftRoundRobinOdd()
    {
        $teamPolygon = new TeamPolygon($this->oddPoolTeams, TeamPolygon::ROUND_ROBIN_ODD);
        $teamPolygon->getTeamPairings();
        $teamPolygon->shift();
        $teamPairings = $teamPolygon->getTeamPairings();

        $this->verifyParing($teamPairings, 3, 4);
        $this->verifyParing($teamPairings, 4, 2);
        $this->verifyParing($teamPairings, 0, 1);
    }

    public function test_shiftCrossPoolEvent()
    {
        $teamPolygon = new TeamPolygon($this->teams, TeamPolygon::CROSS_POOL_EVEN, $this->crossPoolTeams);
        $teamPolygon->getTeamPairings();
        $teamPolygon->shift();
        $teamPairings = $teamPolygon->getTeamPairings();

        $this->verifyParing($teamPairings, 0, 1);
        $this->verifyParing($teamPairings, 1, 2);
        $this->verifyParing($teamPairings, 2, 3);
        $this->verifyParing($teamPairings, 3, 4);
        $this->verifyParing($teamPairings, 4, 5);
        $this->verifyParing($teamPairings, 5, 0);
    }

    private function verifyParing($paring, $point1, $point2, $multiGameSupport = false)
    {
        $this->assertTrue($paring[$point1] == $point2);
    }
}