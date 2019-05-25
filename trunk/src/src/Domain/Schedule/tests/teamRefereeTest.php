<?php

namespace DAG\Domain\Schedule;

use DAG\Orm\Schedule\ORM_TestHelper;

require_once dirname(dirname(dirname(__DIR__))) . '/Orm/Schedule/tests/helper.php';



/**
 * @testSuite test TeamReferee
 */
class TeamRefereeTest extends ORM_TestHelper
{
    public $team;
    public $referee;

    protected function setUp()
    {
        $this->primeDatabase();

        $this->team     = Team::lookupById($this->defaultTeamOrm->id);
        $this->referee  = Referee::lookupById($this->defaultRefereeOrm->id);
    }

    protected function tearDown()
    {
        $this->clearDatabase();
    }

    public function test_lookupById()
    {
        $teamReferee = TeamReferee::lookupById($this->defaultTeamRefereeOrm->id);
        $this->validateTeamReferee($teamReferee, $this->team, $this->referee);
    }

    public function test_lookupByTeam()
    {
        $teamReferees = TeamReferee::lookupByTeam($this->team);
        $this->assertEquals(1, count($teamReferees));
        $this->validateTeamReferee($teamReferees[0], $this->team, $this->referee);
    }

    public function test_lookupByReferee()
    {
        $teamReferees = TeamReferee::lookupByReferee($this->referee);
        $this->assertEquals(1, count($teamReferees));
        $this->validateTeamReferee($teamReferees[0], $this->team, $this->referee);
    }

    public function test_lookupByTeamAndReferee()
    {
        $teamReferee = TeamReferee::lookupByTeamAndReferee($this->team, $this->referee);
        $this->validateTeamReferee($teamReferee, $this->team, $this->referee);
    }

    public function test_findByTeamAndReferee()
    {
        $teamReferee = null;
        $result = TeamReferee::findByTeamAndReferee($this->team, $this->referee, $teamReferee);
        $this->assertTrue($result, "findByTeamAndReferee returned false");
        $this->validateTeamReferee($teamReferee, $this->team, $this->referee);
    }

    public function validateTeamReferee($teamReferee, $team, $referee)
    {
        $this->assertTrue($teamReferee->id > 0);
        $this->assertEquals($team,      $teamReferee->team);
        $this->assertEquals($referee,   $teamReferee->referee);
    }
}