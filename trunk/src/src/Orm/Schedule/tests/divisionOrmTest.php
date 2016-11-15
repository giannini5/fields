<?php

namespace DAG\Orm\Schedule;

require_once 'helper.php';


/**
 * @testSuite test Facility ORM
 */
class DivisionOrmTest extends ORM_TestHelper
{
    /**
     * Expected values on create and load
     */
    protected static $expectedDefaults =
        [
            self::NAME => 'TEST Division',
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
        $divisionOrm = DivisionOrm::create(
            $this->defaultSeasonOrm->id,
            self::$expectedDefaults[self::NAME]);

        $this->verifyExpectedAttributes($divisionOrm, self::$expectedDefaults);
    }

    public function test_loadById()
    {
        $divisionOrm = DivisionOrm::loadById($this->defaultDivisionOrm->id);
        $this->verifyExpectedAttributes($divisionOrm, self::$defaultDivisionOrmAttributes);
    }

    public function test_loadByName()
    {
        $divisionOrm = DivisionOrm::loadBySeasonIdAndName($this->defaultSeasonOrm->id, self::$defaultDivisionOrmAttributes[self::NAME]);
        $this->verifyExpectedAttributes($divisionOrm, self::$defaultDivisionOrmAttributes);
    }

    private function verifyExpectedAttributes($divisionOrm, $attributes)
    {
        $this->assertTrue($divisionOrm->id > 0);
        $this->assertEquals($this->defaultSeasonOrm->id,    $divisionOrm->seasonId);
        $this->assertEquals($attributes[self::NAME],        $divisionOrm->name);
    }
}