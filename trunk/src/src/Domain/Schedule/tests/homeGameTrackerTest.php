<?php

namespace DAG\Domain\Schedule;

use DAG\Orm\Schedule\ORM_TestHelper;

require_once dirname(dirname(dirname(__DIR__))) . '/Orm/Schedule/tests/helper.php';

/**
 * @testSuite test TeamHomeGameTracker
 */
class HomeGameTrackerTest extends ORM_TestHelper
{
    protected $team1;
    protected $team2;

    protected function setUp()
    {
        $this->primeDatabase();

        $this->team1    = Team::lookupById($this->defaultTeamOrm->id);
        $this->team2    = Team::lookupById($this->defaultVisitingTeamOrm->id);
    }

    protected function tearDown()
    {
        $this->clearDatabase();
    }

    public function test_homeTeam()
    {
        $homeTeamTracker = new HomeGameTracker([$this->team1, $this->team2]);
        list($homeTeam, $visitingTeam) = $homeTeamTracker->getHomeVisitorTeams($this->team1, $this->team2);
        $this->assertEquals($homeTeam->id, $this->team2->id);
        $this->assertEquals($visitingTeam->id, $this->team1->id);

        list($homeTeam, $visitingTeam) = $homeTeamTracker->getHomeVisitorTeams($this->team1, $this->team2);
        $this->assertEquals($homeTeam->id, $this->team1->id);
        $this->assertEquals($visitingTeam->id, $this->team2->id);

        list($homeTeam, $visitingTeam) = $homeTeamTracker->getHomeVisitorTeams($this->team1, $this->team2);
        $this->assertEquals($homeTeam->id, $this->team2->id);
        $this->assertEquals($visitingTeam->id, $this->team1->id);
    }
}