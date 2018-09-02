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
            self::START_TIME        => '03:30:00',
            self::GENDER_PREFERENCE => GameTimeOrm::BOYS,
            self::ACTUAL_START_TIME => null,
            self::LOCKED            => 0,
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
            $this->defaultFieldOrm->id,
            self::$expectedDefaults[self::START_TIME],
            self::$expectedDefaults[self::GENDER_PREFERENCE]);

        $this->verifyExpectedAttributes($gameTimeOrm, self::$expectedDefaults);
    }

    public function test_createWithActualStartTime()
    {
        $actualStartTime = '04:00:00';

        $gameTimeOrm = GameTimeOrm::create(
            $this->defaultGameDateOrm->id,
            $this->defaultFieldOrm->id,
            self::$expectedDefaults[self::START_TIME],
            self::$expectedDefaults[self::GENDER_PREFERENCE],
            null,
            $actualStartTime);

        $this->verifyExpectedAttributes($gameTimeOrm, self::$expectedDefaults, $actualStartTime);
    }

    public function test_loadById()
    {
        $gameTimeOrm = GameTimeOrm::loadById($this->defaultGameTimeOrm->id);
        $this->verifyExpectedAttributes($gameTimeOrm, self::$defaultGameTimeOrmAttributes);
    }

    public function test_loadByGameId()
    {
        $gameTimeOrm = GameTimeOrm::loadByGameId($this->defaultGameOrm->id);
        $this->verifyExpectedAttributes($gameTimeOrm, self::$defaultGameTimeOrmAttributes);
    }

    public function test_loadByGameDateId()
    {
        $gameTimeOrms = GameTimeOrm::loadByGameDateId($this->defaultGameDateOrm->id);
        $this->assertEquals(1, count($gameTimeOrms));
        $this->verifyExpectedAttributes($gameTimeOrms[0], self::$defaultGameTimeOrmAttributes);
    }

    public function test_loadByGameDateAndFieldId()
    {
        $gameTimeOrms = GameTimeOrm::loadByGameDateIdAndFieldId($this->defaultGameDateOrm->id, $this->defaultFieldOrm->id);
        $this->assertEquals(1, count($gameTimeOrms));
        $this->verifyExpectedAttributes($gameTimeOrms[0], self::$defaultGameTimeOrmAttributes);
    }

    public function test_loadByGameDateAndFieldIdAndGender()
    {
        $gameTimeOrms = GameTimeOrm::loadByGameDateIdAndFieldIdAndGender($this->defaultGameDateOrm->id, $this->defaultFieldOrm->id, GameTimeOrm::BOYS);
        $this->assertEquals(0, count($gameTimeOrms));

        $gameTimeOrms = GameTimeOrm::loadByGameDateIdAndFieldIdAndGender($this->defaultGameDateOrm->id, $this->defaultFieldOrm->id, GameTimeOrm::GIRLS);
        $this->assertEquals(1, count($gameTimeOrms));
        $this->verifyExpectedAttributes($gameTimeOrms[0], self::$defaultGameTimeOrmAttributes);
    }

    public function test_loadByGameDateAndFieldIdAndStartTime()
    {
        $gameTimeOrm = GameTimeOrm::loadByGameDateIdAndFieldIdAndStartTime($this->defaultGameDateOrm->id, $this->defaultFieldOrm->id, self::$defaultGameTimeOrmAttributes[self::START_TIME]);
        $this->verifyExpectedAttributes($gameTimeOrm, self::$defaultGameTimeOrmAttributes);
    }

    public function test_loadByFieldId()
    {
        $gameTimeOrms = GameTimeOrm::loadByFieldId($this->defaultFieldOrm->id);
        $this->assertEquals(1, count($gameTimeOrms));
        $this->verifyExpectedAttributes($gameTimeOrms[0], self::$defaultGameTimeOrmAttributes);
    }

    public function test_getUniqueStartTimes()
    {
        $gameTimes = GameTimeOrm::getUniqueStartTimes($this->defaultFieldOrm->id);
        $this->assertEquals(1, count($gameTimes));
        $this->assertEquals('16:30:00', $gameTimes[0]['startTime']);
    }

    private function verifyExpectedAttributes($gameTimeOrm, $attributes, $actualStartTime = null)
    {
        $this->assertTrue($gameTimeOrm->id > 0);
        $this->assertEquals($this->defaultGameDateOrm->id,          $gameTimeOrm->gameDateId);
        $this->assertEquals($this->defaultFieldOrm->id,             $gameTimeOrm->fieldId);
        $this->assertEquals($attributes[self::START_TIME],          $gameTimeOrm->startTime);
        $this->assertEquals($attributes[self::GENDER_PREFERENCE],   $gameTimeOrm->genderPreference);
        $this->assertEquals($attributes[self::LOCKED],              $gameTimeOrm->locked);
        if (isset($actualStartTime)) {
            $this->assertEquals($actualStartTime, $gameTimeOrm->actualStartTime);
        } else {
            $this->assertTrue(!isset($attributes[self::ACTUAL_START_TIME]), "Unexpected actual start time");
        }
    }
}