<?php

namespace DAG\Domain\Schedule;

use DAG\Orm\Schedule\ORM_TestHelper;

require_once dirname(dirname(dirname(__DIR__))) . '/Orm/Schedule/tests/helper.php';



/**
 * @testSuite test GameTime
 */
class GameTimeTest extends ORM_TestHelper
{
    /**
     * Expected values on create and load
     */
    protected static $expectedDefaults = array(
        'startTime'     => '16:30:00',
    );

    protected $gameTimesToCleanup = array();
    protected $gameDate;
    protected $division;
    protected $field;

    protected function setUp()
    {
        $this->primeDatabase();

        $this->gameDate = GameDate::lookupById($this->defaultGameDateOrm->id);
        $this->division = Division::lookupById($this->defaultDivisionOrm->id);
        $this->field    = Field::lookupById($this->defaultFieldOrm->id);

        // Clear out default gameTime and create a new one for testing
        $gameTime = GameTime::lookupById($this->defaultGameTimeOrm->id);
        $gameTime->delete();

        $this->gameTimesToCleanup[] = GameTime::create(
            $this->gameDate,
            $this->division,
            $this->field,
            self::$expectedDefaults['startTime']);
    }

    protected function tearDown()
    {
        foreach ($this->gameTimesToCleanup as $entity) {
            $entity->delete();
        }

        $this->clearDatabase();
    }

    public function test_create()
    {
        $gameTime = $this->gameTimesToCleanup[0];
        $this->validateGameTime($gameTime, $this->gameDate, $this->division, $this->field, self::$expectedDefaults);
    }

    public function test_lookupById()
    {
        $gameTime = GameTime::lookupById($this->gameTimesToCleanup[0]->id);
        $this->validateGameTime($gameTime, $this->gameDate, $this->division, $this->field, self::$expectedDefaults);
    }

    public function test_lookupByGameDate()
    {
        $gameTimes = GameTime::lookupByGameDate($this->gameDate);
        $this->assertTrue(count($gameTimes) == 1);
        $this->validateGameTime($gameTimes[0], $this->gameDate, $this->division, $this->field, self::$expectedDefaults);
    }

    public function test_lookupByDivision()
    {
        $gameTimes = GameTime::lookupByDivision($this->gameDate, $this->division);
        $this->assertTrue(count($gameTimes) == 1);
        $this->validateGameTime($gameTimes[0], $this->gameDate, $this->division, $this->field, self::$expectedDefaults);
    }

    public function test_lookupByField()
    {
        $gameTimes = GameTime::lookupByField($this->gameDate, $this->field);
        $this->assertTrue(count($gameTimes) == 1);
        $this->validateGameTime($gameTimes[0], $this->gameDate, $this->division, $this->field, self::$expectedDefaults);
    }

    public function validateGameTime($gameTime, $gameDate, $division, $field, $expectedDefaults)
    {
        $this->assertTrue($gameTime->id > 0);
        $this->assertEquals($expectedDefaults['startTime'], $gameTime->startTime);
        $this->assertEquals($gameDate,                      $gameTime->gameDate);
        $this->assertEquals($division,                      $gameTime->division);
        $this->assertEquals($field,                         $gameTime->field);
    }
}