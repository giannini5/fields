<?php

namespace DAG\Orm\Schedule;

require_once 'helper.php';


/**
 * @testSuite test Facility ORM
 */
class PlayerOrmTest extends ORM_TestHelper
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
        $this->verifyExpectedAttributes($this->defaultPlayerOrm, self::$defaultPlayerOrmAttributes);
    }

    public function test_loadById()
    {
        $playerOrm = PlayerOrm::loadById($this->defaultPlayerOrm->id);
        $this->verifyExpectedAttributes($playerOrm, self::$defaultPlayerOrmAttributes);
    }

    public function test_loadByName()
    {
        $playerOrm = PlayerOrm::loadByName($this->defaultPlayerOrm->teamId, $this->defaultPlayerOrm->name);
        $this->verifyExpectedAttributes($playerOrm, self::$defaultPlayerOrmAttributes);
    }

    public function test_loadByTeam()
    {
        $playerOrms = PlayerOrm::loadByTeamId($this->defaultTeamOrm->id);
        $this->assertEquals(1, count($playerOrms));
        $this->verifyExpectedAttributes($playerOrms[0], self::$defaultPlayerOrmAttributes);
    }

    public function test_loadByFamily()
    {
        $playerOrms = PlayerOrm::loadByFamilyId($this->defaultFamilyOrm->id);
        $this->assertEquals(1, count($playerOrms));
        $this->verifyExpectedAttributes($playerOrms[0], self::$defaultPlayerOrmAttributes);
    }

    private function verifyExpectedAttributes($playerOrm, $attributes)
    {
        $this->assertTrue($playerOrm->id > 0);
        $this->assertEquals($this->defaultTeamOrm->id,           $playerOrm->teamId);
        $this->assertEquals($this->defaultFamilyOrm->id,         $playerOrm->familyId);
        $this->assertEquals($attributes[self::NAME],             $playerOrm->name);
        $this->assertEquals($attributes[self::EMAIL],            $playerOrm->email);
        $this->assertEquals($attributes[self::PHONE],            $playerOrm->phone);
        $this->assertNull($attributes[self::NUMBER],             $playerOrm->number);
        $this->assertEquals($attributes[self::GOALS],            $playerOrm->goals);
        $this->assertEquals($attributes[self::QUARTERS_SUB],     $playerOrm->quartersSub);
        $this->assertEquals($attributes[self::QUARTERS_KEEP],    $playerOrm->quartersKeep);
        $this->assertEquals($attributes[self::QUARTERS_INJURED], $playerOrm->quartersInjured);
        $this->assertEquals($attributes[self::QUARTERS_ABSENT],  $playerOrm->quartersAbsent);
        $this->assertEquals($attributes[self::YELLOW_CARDS],     $playerOrm->yellowCards);
        $this->assertEquals($attributes[self::RED_CARDS],        $playerOrm->redCards);
    }
}