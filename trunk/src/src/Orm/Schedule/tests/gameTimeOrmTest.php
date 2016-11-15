<?php

namespace DAG\Orm\Schedule;

require_once 'helper.php';


/**
 * @testSuite test Facility ORM
 */
class GameTimeOrmTest extends ORM_TestHelper
{
    /**
     * Expected values on create and load
     */
    protected static $expectedDefaults =
        [
            self::START_TIME => '03:30:00',
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
        $gameTimeOrm = GameTimeOrm::create(
            $this->defaultGameDateOrm->id,
            $this->defaultDivisionOrm->id,
            $this->defaultFieldOrm->id,
            self::$expectedDefaults[self::START_TIME]);

        $this->verifyExpectedAttributes($gameTimeOrm, self::$expectedDefaults);
    }

    public function test_loadById()
    {
        $gameTimeOrm = GameTimeOrm::loadById($this->defaultGameTimeOrm->id);
        $this->verifyExpectedAttributes($gameTimeOrm, self::$defaultGameTimeOrmAttributes);
    }

    public function test_loadByGameDateId()
    {
        $gameTimeOrms = GameTimeOrm::loadByGameDateId($this->defaultGameDateOrm->id);
        $this->assertEquals(1, count($gameTimeOrms));
        $this->verifyExpectedAttributes($gameTimeOrms[0], self::$defaultGameTimeOrmAttributes);
    }

    public function test_loadByDivisionId()
    {
        $gameTimeOrms = GameTimeOrm::loadByDivisionId($this->defaultGameDateOrm->id, $this->defaultDivisionOrm->id);
        $this->assertEquals(1, count($gameTimeOrms));
        $this->verifyExpectedAttributes($gameTimeOrms[0], self::$defaultGameTimeOrmAttributes);
    }

    public function test_loadByFieldId()
    {
        $gameTimeOrms = GameTimeOrm::loadByFieldId($this->defaultGameDateOrm->id, $this->defaultFieldOrm->id);
        $this->assertEquals(1, count($gameTimeOrms));
        $this->verifyExpectedAttributes($gameTimeOrms[0], self::$defaultGameTimeOrmAttributes);
    }

    private function verifyExpectedAttributes($gameTimeOrm, $attributes)
    {
        $this->assertTrue($gameTimeOrm->id > 0);
        $this->assertEquals($this->defaultGameDateOrm->id,  $gameTimeOrm->gameDateId);
        $this->assertEquals($this->defaultDivisionOrm->id,  $gameTimeOrm->divisionId);
        $this->assertEquals($this->defaultFieldOrm->id,     $gameTimeOrm->fieldId);
        $this->assertEquals($attributes[self::START_TIME],  $gameTimeOrm->startTime);
    }
}