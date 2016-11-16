<?php

namespace DAG\Orm\Schedule;

require_once 'helper.php';


/**
 * @testSuite test Facility ORM
 */
class AssistantCoachOrmTest extends ORM_TestHelper
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
        $this->verifyExpectedAttributes($this->defaultAssistantCoachOrm, self::$defaultAssistantCoachOrmAttributes);
    }

    public function test_loadById()
    {
        $assistantCoachOrm = AssistantCoachOrm::loadById($this->defaultAssistantCoachOrm->id);
        $this->verifyExpectedAttributes($assistantCoachOrm, self::$defaultAssistantCoachOrmAttributes);
    }

    public function test_loadByTeam()
    {
        $assistantCoachOrms = AssistantCoachOrm::loadByTeamId($this->defaultTeamOrm->id);
        $this->assertEquals(1, count($assistantCoachOrms));
        $this->verifyExpectedAttributes($assistantCoachOrms[0], self::$defaultAssistantCoachOrmAttributes);
    }

    public function test_loadByTeamAndName()
    {
        $assistantCoachOrm = AssistantCoachOrm::loadByTeamIdAndName($this->defaultTeamOrm->id, $this->defaultAssistantCoachOrm->name);
        $this->verifyExpectedAttributes($assistantCoachOrm, self::$defaultAssistantCoachOrmAttributes);
    }

    public function test_loadByFamily()
    {
        $assistantCoachOrms = AssistantCoachOrm::loadByFamilyId($this->defaultFamilyOrm->id);
        $this->assertEquals(1, count($assistantCoachOrms));
        $this->verifyExpectedAttributes($assistantCoachOrms[0], self::$defaultAssistantCoachOrmAttributes);
    }

    private function verifyExpectedAttributes($assistantCoachOrm, $attributes)
    {
        $this->assertTrue($assistantCoachOrm->id > 0);
        $this->assertEquals($this->defaultTeamOrm->id,      $assistantCoachOrm->teamId);
        $this->assertEquals($this->defaultFamilyOrm->id,    $assistantCoachOrm->familyId);
        $this->assertEquals($attributes[self::NAME],        $assistantCoachOrm->name);
        $this->assertEquals($attributes[self::EMAIL],       $assistantCoachOrm->email);
        $this->assertEquals($attributes[self::PHONE1],      $assistantCoachOrm->phone1);
        $this->assertEquals($attributes[self::PHONE2],      $assistantCoachOrm->phone2);
    }
}