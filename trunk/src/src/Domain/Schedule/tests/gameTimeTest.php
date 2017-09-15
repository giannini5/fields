<?php

namespace DAG\Domain\Schedule;

use DAG\Orm\Schedule\GameOrm;
use DAG\Orm\Schedule\GameTimeOrm;
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
        'startTime'         => '16:30:00',
        'genderPreference'  => 'Boys',
    );

    protected $gameTimesToCleanup = array();
    protected $gameDate;
    protected $field;

    protected function setUp()
    {
        $this->primeDatabase();

        $this->gameDate = GameDate::lookupById($this->defaultGameDateOrm->id);
        $this->field    = Field::lookupById($this->defaultFieldOrm->id);

        // Clear out default gameTime and create a new one for testing
        $game     = Game::lookupById($this->defaultGameOrm->id);
        $game->delete();

        $gameTime = GameTime::lookupById($this->defaultGameTimeOrm->id);
        $gameTime->delete();

        $this->gameTimesToCleanup[] = GameTime::create(
            $this->gameDate,
            $this->field,
            self::$expectedDefaults['startTime'],
            self::$expectedDefaults['genderPreference']);
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
        $this->validateGameTime($gameTime, $this->gameDate, $this->field, self::$expectedDefaults);
    }

    public function test_lookupById()
    {
        $gameTime = GameTime::lookupById($this->gameTimesToCleanup[0]->id);
        $this->validateGameTime($gameTime, $this->gameDate, $this->field, self::$expectedDefaults);
    }

    public function test_lookupByGameId()
    {
        $this->defaultGameOrm = GameOrm::create(
            $this->defaultFlightOrm->id,
            $this->defaultPoolOrm->id,
            $this->gameTimesToCleanup[0]->id,
            $this->defaultTeamOrm->id,
            $this->defaultVisitingTeamOrm->id);

        $gameTime = GameTime::lookupByGameId($this->defaultGameOrm->id);
        $this->validateGameTime($gameTime, $this->gameDate, $this->field, self::$expectedDefaults);
    }

    public function test_lookupByGameDate()
    {
        $gameTimes = GameTime::lookupByGameDate($this->gameDate);
        $this->assertTrue(count($gameTimes) == 1);
        $this->validateGameTime($gameTimes[0], $this->gameDate, $this->field, self::$expectedDefaults);
    }

    public function test_lookupByGameDateAndField()
    {
        $gameTimes = GameTime::lookupByGameDateAndField($this->gameDate, $this->field);
        $this->assertTrue(count($gameTimes) == 1);
        $this->validateGameTime($gameTimes[0], $this->gameDate, $this->field, self::$expectedDefaults);
    }

    public function test_lookupByGameDateAndFieldAndGender()
    {
        $gameTimes = GameTime::lookupByGameDateAndFieldAndGender($this->gameDate, $this->field, GameTimeOrm::BOYS);
        $this->assertEquals(1, count($gameTimes));
        $this->validateGameTime($gameTimes[0], $this->gameDate, $this->field, self::$expectedDefaults);

        $gameTimes = GameTime::lookupByGameDateAndFieldAndGender($this->gameDate, $this->field, GameTimeOrm::GIRLS);
        $this->assertEquals(0, count($gameTimes));
    }

    public function test_lookupByGameDateAndGenderAndFields()
    {
        $fields = [$this->field];

        $gameTimes = GameTime::lookupByGameDateAndGenderAndFields($this->gameDate, GameTimeOrm::BOYS, $fields);
        $this->assertEquals(1, count($gameTimes));
        $this->validateGameTime($gameTimes[0], $this->gameDate, $this->field, self::$expectedDefaults);

        $gameTimes = GameTime::lookupByGameDateAndGenderAndFields($this->gameDate, GameTimeOrm::GIRLS, $fields);
        $this->assertEquals(0, count($gameTimes));
    }

    public function test_lookupByField()
    {
        $gameTimes = GameTime::lookupByField($this->field);
        $this->assertTrue(count($gameTimes) == 1);
        $this->validateGameTime($gameTimes[0], $this->gameDate, $this->field, self::$expectedDefaults);
    }

    public function test_setActualStartTime()
    {
        $gameTimes = GameTime::lookupByField($this->field);
        $this->assertTrue(count($gameTimes) == 1);
        $gameTime = $gameTimes[0];

        $this->assertEquals(self::$expectedDefaults['startTime'], $gameTime->actualStartTime);
        $gameTime->actualStartTime = '04:00:00';
        $this->assertEquals('04:00:00', $gameTime->actualStartTime);
    }

    public function validateGameTime($gameTime, $gameDate, $field, $expectedDefaults)
    {
        $this->assertTrue($gameTime->id > 0);
        $this->assertEquals($expectedDefaults['startTime'],         $gameTime->startTime);
        $this->assertEquals($expectedDefaults['startTime'],         $gameTime->actualStartTime);
        $this->assertEquals($expectedDefaults['genderPreference'],  $gameTime->genderPreference);
        $this->assertEquals($gameDate,                              $gameTime->gameDate);
        $this->assertEquals($field,                                 $gameTime->field);
        $this->assertFalse(isset($gameTime->actualStartTime), "actualStartTime should not be set");
    }
}