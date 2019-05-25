<?php

namespace DAG\Orm\Schedule;

require_once 'helper.php';


/**
 * @testSuite test RefereeCrew ORM
 */
class RefereeCrewOrmTest extends ORM_TestHelper
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
        $this->verifyExpectedAttributes($this->defaultRefereeCrewOrm);
    }

    public function test_loadById()
    {
        $refereeCrewOrm = RefereeCrewOrm::loadById($this->defaultRefereeCrewOrm->id);
        $this->verifyExpectedAttributes($refereeCrewOrm);
    }

    public function test_loadByTeamId()
    {
        $refereeCrewOrms = RefereeCrewOrm::loadByTeamId($this->defaultTeamOrm->id);
        $this->assertEquals(1, count($refereeCrewOrms));
        $this->verifyExpectedAttributes($refereeCrewOrms[0]);
    }

    public function test_loadByDivisionId()
    {
        $refereeCrewOrms = RefereeCrewOrm::loadByDivisionId($this->defaultDivisionOrm->id);
        $this->assertEquals(1, count($refereeCrewOrms));
        $this->verifyExpectedAttributes($refereeCrewOrms[0]);
    }

    private function verifyExpectedAttributes($refereeCrewOrm)
    {
        $this->assertTrue($refereeCrewOrm->id > 0);
        $this->assertEquals($this->defaultCenterRefereeOrm->id,     $refereeCrewOrm->centerRefereeId);
        $this->assertEquals($this->defaultAssistantReferee1Orm->id, $refereeCrewOrm->assistantReferee1Id);
        $this->assertEquals($this->defaultAssistantReferee2Orm->id, $refereeCrewOrm->assistantReferee2Id);
        $this->assertEquals($this->defaultDivisionOrm->id,          $refereeCrewOrm->divisionId);
        $this->assertEquals($this->defaultTeamOrm->id,              $refereeCrewOrm->teamId);
    }
}