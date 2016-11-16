<?php

namespace DAG\Orm\Schedule;

require_once 'helper.php';


/**
 * @testSuite test Facility ORM
 */
class CoachOrmTest extends ORM_TestHelper
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
        $this->verifyExpectedAttributes($this->defaultCoachOrm, self::$defaultCoachOrmAttributes);
    }

    public function test_loadById()
    {
        $coachOrm = CoachOrm::loadById($this->defaultCoachOrm->id);
        $this->verifyExpectedAttributes($coachOrm, self::$defaultCoachOrmAttributes);
    }

    public function test_loadByTeam()
    {
        $coachOrm = CoachOrm::loadByTeamId($this->defaultTeamOrm->id);
        $this->verifyExpectedAttributes($coachOrm, self::$defaultCoachOrmAttributes);
    }

    public function test_loadByFamily()
    {
        $coachOrms = CoachOrm::loadByFamilyId($this->defaultFamilyOrm->id);
        $this->assertEquals(1, count($coachOrms));
        $this->verifyExpectedAttributes($coachOrms[0], self::$defaultCoachOrmAttributes);
    }

    private function verifyExpectedAttributes($coachOrm, $attributes)
    {
        $this->assertTrue($coachOrm->id > 0);
        $this->assertEquals($this->defaultTeamOrm->id,      $coachOrm->teamId);
        $this->assertEquals($this->defaultFamilyOrm->id,    $coachOrm->familyId);
        $this->assertEquals($attributes[self::NAME],        $coachOrm->name);
        $this->assertEquals($attributes[self::EMAIL],       $coachOrm->email);
        $this->assertEquals($attributes[self::PHONE1],      $coachOrm->phone1);
        $this->assertEquals($attributes[self::PHONE2],      $coachOrm->phone2);
    }
}