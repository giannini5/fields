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
            self::NAME              => 'TEST Team',
            self::NAME_ID           => 'TEST 20',
            self::COLOR             => 'red',
            self::REGION            => '122',
            self::CITY              => 'Santa Barbara',
            self::VOLUNTEER_POINTS  => 2,
            self::SEED              => 1,
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
            self::$expectedDefaults[self::NAME],
            self::$expectedDefaults[self::NAME_ID],
            self::$expectedDefaults[self::REGION],
            self::$expectedDefaults[self::CITY],
            self::$expectedDefaults[self::VOLUNTEER_POINTS],
            self::$expectedDefaults[self::SEED],
            self::$expectedDefaults[self::COLOR]);

        $this->verifyExpectedAttributes($teamOrm, self::$expectedDefaults);
    }

    public function test_loadById()
    {
        $teamOrm = TeamOrm::loadById($this->defaultTeamOrm->id);
        $this->verifyExpectedAttributes($teamOrm, self::$defaultTeamOrmAttributes);
    }

    public function test_loadByNameId()
    {
        $teamOrm = TeamOrm::loadByDivisionIdAndNameId($this->defaultDivisionOrm->id, self::$defaultTeamOrmAttributes[self::NAME_ID]);
        $this->verifyExpectedAttributes($teamOrm, self::$defaultTeamOrmAttributes);
    }

    private function verifyExpectedAttributes($teamOrm, $attributes)
    {
        $this->assertTrue($teamOrm->id > 0);
        $this->assertEquals($this->defaultDivisionOrm->id,          $teamOrm->divisionId);
        $this->assertEquals($this->defaultPoolOrm->id,              $teamOrm->poolId);
        $this->assertEquals($attributes[self::NAME],                $teamOrm->name);
        $this->assertEquals($attributes[self::NAME_ID],             $teamOrm->nameId);
        $this->assertEquals($attributes[self::COLOR],               $teamOrm->color);
        $this->assertEquals($attributes[self::REGION],              $teamOrm->region);
        $this->assertEquals($attributes[self::CITY],                $teamOrm->city);
        $this->assertEquals($attributes[self::VOLUNTEER_POINTS],    $teamOrm->volunteerPoints);
        $this->assertEquals($attributes[self::SEED],                $teamOrm->seed);
    }
}