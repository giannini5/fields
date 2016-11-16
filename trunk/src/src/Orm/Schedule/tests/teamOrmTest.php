<?php

namespace DAG\Orm\Schedule;

require_once 'helper.php';


/**
 * @testSuite test Facility ORM
 */
class TeamOrmTest extends ORM_TestHelper
{
    /**
     * Expected values on create and load
     */
    protected static $expectedDefaults =
        [
            self::NAME => 'TEST Team',
        ];

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
        $teamOrm = TeamOrm::create(
            $this->defaultDivisionOrm->id,
            $this->defaultPoolOrm->id,
            self::$expectedDefaults[self::NAME]);

        $this->verifyExpectedAttributes($teamOrm, self::$expectedDefaults);
    }

    public function test_loadById()
    {
        $teamOrm = TeamOrm::loadById($this->defaultTeamOrm->id);
        $this->verifyExpectedAttributes($teamOrm, self::$defaultTeamOrmAttributes);
    }

    public function test_loadByName()
    {
        $teamOrm = TeamOrm::loadByDivisionIdAndName($this->defaultDivisionOrm->id, self::$defaultTeamOrmAttributes[self::NAME]);
        $this->verifyExpectedAttributes($teamOrm, self::$defaultTeamOrmAttributes);
    }

    private function verifyExpectedAttributes($teamOrm, $attributes)
    {
        $this->assertTrue($teamOrm->id > 0);
        $this->assertEquals($this->defaultDivisionOrm->id,  $teamOrm->divisionId);
        $this->assertEquals($this->defaultPoolOrm->id,      $teamOrm->poolId);
        $this->assertEquals($attributes[self::NAME],        $teamOrm->name);
    }
}