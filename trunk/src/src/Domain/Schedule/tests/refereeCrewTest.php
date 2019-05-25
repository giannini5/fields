<?php

namespace DAG\Domain\Schedule;

use DAG\Orm\Schedule\ORM_TestHelper;

require_once dirname(dirname(dirname(__DIR__))) . '/Orm/Schedule/tests/helper.php';



/**
 * @testSuite test RefereeCrew
 */
class RefereeCrewTest extends ORM_TestHelper
{
    public $centerReferee;
    public $assistantReferee1;
    public $assistantReferee2;
    public $division;
    public $team;

    protected function setUp()
    {
        $this->primeDatabase();

        $this->centerReferee        = Referee::lookupById($this->defaultCenterRefereeOrm->id);
        $this->assistantReferee1    = Referee::lookupById($this->defaultAssistantReferee1Orm->id);
        $this->assistantReferee2    = Referee::lookupById($this->defaultAssistantReferee2Orm->id);
        $this->division             = Division::lookupById($this->defaultDivisionOrm->id);
        $this->team                 = Team::lookupById($this->defaultTeamOrm->id);
    }

    protected function tearDown()
    {
        $this->clearDatabase();
    }

    public function test_lookupById()
    {
        $refereeCrew = RefereeCrew::lookupById($this->defaultRefereeCrewOrm->id);
        $this->validateRefereeCrew($refereeCrew);
    }

    public function test_lookupByTeam()
    {
        $refereeCrews = RefereeCrew::lookupByTeam($this->team);
        $this->assertEquals(1, count($refereeCrews));
        $this->validateRefereeCrew($refereeCrews[0]);
    }

    public function test_lookupByDivision()
    {
        $refereeCrews = RefereeCrew::lookupByDivision($this->division);
        $this->assertEquals(1, count($refereeCrews));
        $this->validateRefereeCrew($refereeCrews[0]);
    }

    public function test_findAndFound()
    {
        $refereeCrew = null;
        $result = RefereeCrew::findByRefereesDivisionAndTeam(
            $this->centerReferee, $this->assistantReferee1, $this->assistantReferee2, $this->division, $this->team, $refereeCrew);
        $this->assertTrue($result);
        $this->validateRefereeCrew($refereeCrew);

        $result = RefereeCrew::findByRefereesDivisionAndTeam(
            $this->centerReferee, $this->assistantReferee2, $this->assistantReferee1, $this->division, $this->team, $refereeCrew);
        $this->assertTrue($result);
        $this->validateRefereeCrew($refereeCrew);

        $result = RefereeCrew::findByRefereesDivisionAndTeam(
            $this->assistantReferee2, $this->centerReferee, $this->assistantReferee1, $this->division, $this->team, $refereeCrew);
        $this->assertTrue($result);
        $this->validateRefereeCrew($refereeCrew);

        $result = RefereeCrew::findByRefereesDivisionAndTeam(
            $this->assistantReferee2, $this->assistantReferee1, $this->centerReferee, $this->division, $this->team, $refereeCrew);
        $this->assertTrue($result);
        $this->validateRefereeCrew($refereeCrew);

        $result = RefereeCrew::findByRefereesDivisionAndTeam(
            $this->assistantReferee1, $this->assistantReferee2, $this->centerReferee, $this->division, $this->team, $refereeCrew);
        $this->assertTrue($result);
        $this->validateRefereeCrew($refereeCrew);

        $result = RefereeCrew::findByRefereesDivisionAndTeam(
            $this->assistantReferee1, $this->centerReferee, $this->assistantReferee2, $this->division, $this->team, $refereeCrew);
        $this->assertTrue($result);
        $this->validateRefereeCrew($refereeCrew);
    }

    public function test_findAndNotFound()
    {
        $refereeCrew   = null;
        $referee        = Referee::lookupById($this->defaultRefereeOrm->id);
        $result         = RefereeCrew::findByRefereesDivisionAndTeam(
            $referee, $this->assistantReferee1, $this->assistantReferee2, $this->division, $this->team, $refereeCrew);
        $this->assertFalse($result);
    }

    public function test_issetTrue()
    {
        $refereeCrew = RefereeCrew::lookupById($this->defaultRefereeCrewOrm->id);
        $this->assertTrue(isset($refereeCrew->id));
        $this->assertTrue(isset($refereeCrew->centerReferee));
        $this->assertTrue(isset($refereeCrew->assistantReferee1));
        $this->assertTrue(isset($refereeCrew->assistantReferee2));
        $this->assertTrue(isset($refereeCrew->division));
        $this->assertTrue(isset($refereeCrew->team));
    }

    public function test_issetFalse()
    {
        $referee        = Referee::lookupById($this->defaultRefereeOrm->id);
        $refereeCrew = RefereeCrew::create($referee, $this->assistantReferee1, $this->assistantReferee2,
            $this->division, null);
        $this->assertFalse(isset($refereeCrew->team));
    }

    /**
     * @param RefereeCrew $refereeCrew
     */
    public function validateRefereeCrew($refereeCrew)
    {
        $this->assertTrue($refereeCrew->id > 0);
        $this->assertEquals($this->centerReferee->id,       $refereeCrew->centerReferee->id);
        $this->assertEquals($this->assistantReferee1->id,   $refereeCrew->assistantReferee1->id);
        $this->assertEquals($this->assistantReferee2->id,   $refereeCrew->assistantReferee2->id);
        $this->assertEquals($this->division->id,            $refereeCrew->division->id);
        $this->assertEquals($this->team->id,                $refereeCrew->team->id);
    }
}