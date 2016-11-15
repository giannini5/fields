<?php

namespace DAG\Orm\Schedule;

require_once 'helper.php';


/**
 * @testSuite test Facility ORM
 */
class GameDateOrmTest extends ORM_TestHelper
{
    /**
     * Expected values on create and load
     */
    protected static $expectedDefaults =
        [
            self::DAY => '2016-02-16',
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
        $gameDateOrm = GameDateOrm::create(
            $this->defaultSeasonOrm->id,
            self::$expectedDefaults[self::DAY]);

        $this->verifyExpectedAttributes($gameDateOrm, self::$expectedDefaults);
    }

    public function test_loadById()
    {
        $gameDateOrm = GameDateOrm::loadById($this->defaultGameDateOrm->id);
        $this->verifyExpectedAttributes($gameDateOrm, self::$defaultGameDateOrmAttributes);
    }

    public function test_loadByDay()
    {
        $gameDateOrm = GameDateOrm::loadBySeasonIdAndDay($this->defaultSeasonOrm->id, self::$defaultGameDateOrmAttributes[self::DAY]);
        $this->verifyExpectedAttributes($gameDateOrm, self::$defaultGameDateOrmAttributes);
    }

    private function verifyExpectedAttributes($gameDateOrm, $attributes)
    {
        $this->assertTrue($gameDateOrm->id > 0);
        $this->assertEquals($this->defaultSeasonOrm->id,        $gameDateOrm->seasonId);
        $this->assertEquals($attributes[self::DAY],             $gameDateOrm->day);
    }
}