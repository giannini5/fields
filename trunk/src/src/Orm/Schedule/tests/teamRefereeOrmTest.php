<?php

namespace DAG\Orm\Schedule;

require_once 'helper.php';


/**
 * @testSuite test TeamReferee ORM
 */
class TeamRefereeOrmTest extends ORM_TestHelper
{
    protected function setUp()
    {
        $this->primeDatabase();
    }

    protected function tearDown()
    {
        $this->clearDatabase();
    }

    public function test_create()
    {
        $this->verifyExpectedAttributes($this->defaultTeamRefereeOrm, $this->defaultTeamOrm->id, $this->defaultRefereeOrm->id);
    }

    public function test_loadById()
    {
        $teamRefereeOrm = TeamRefereeOrm::loadById($this->defaultTeamRefereeOrm->id);
        $this->verifyExpectedAttributes($teamRefereeOrm, $this->defaultTeamOrm->id, $this->defaultRefereeOrm->id);
    }

    public function test_loadByTeamReferee()
    {
        $teamRefereeOrm = TeamRefereeOrm::loadByTeamIdAndReferee($this->defaultTeamOrm->id, $this->defaultRefereeOrm->id);
        $this->verifyExpectedAttributes($teamRefereeOrm, $this->defaultTeamOrm->id, $this->defaultRefereeOrm->id);
    }

    public function test_loadByTeam()
    {
        $teamRefereeOrms = TeamRefereeOrm::loadByTeamId($this->defaultTeamOrm->id);
        $this->assertEquals(1, count($teamRefereeOrms));
        $this->verifyExpectedAttributes($teamRefereeOrms[0], $this->defaultTeamOrm->id, $this->defaultRefereeOrm->id);
    }

    public function test_loadByReferee()
    {
        $teamRefereeOrms = TeamRefereeOrm::loadByRefereeId($this->defaultRefereeOrm->id);
        $this->assertEquals(1, count($teamRefereeOrms));
        $this->verifyExpectedAttributes($teamRefereeOrms[0], $this->defaultTeamOrm->id, $this->defaultRefereeOrm->id);
    }

    private function verifyExpectedAttributes($teamRefereeOrm, $teamOrmId, $refereeOrmId)
    {
        $this->assertTrue($teamRefereeOrm->id > 0);
        $this->assertEquals($teamOrmId, $teamRefereeOrm->teamId);
        $this->assertEquals($refereeOrmId,    $teamRefereeOrm->refereeId);
    }
}