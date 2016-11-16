<?php

namespace DAG\Domain\Schedule;

use DAG\Orm\Schedule\ORM_TestHelper;

require_once dirname(dirname(dirname(__DIR__))) . '/Orm/Schedule/tests/helper.php';



/**
 * @testSuite test GameDate
 */
class GameDateTest extends ORM_TestHelper
{
    /**
     * Expected values on create and load
     */
    protected static $expectedDefaults = array(
        'day'           => '2016-09-23',
    );

    protected $gameDatesToCleanup = array();
    protected $season;

    protected function setUp()
    {
        $this->primeDatabase();

        $this->season = Season::lookupById($this->defaultSeasonOrm->id);

        $this->gameDatesToCleanup[] = GameDate::create(
            $this->season,
            self::$expectedDefaults['day']);
    }

    protected function tearDown()
    {
        foreach ($this->gameDatesToCleanup as $entity) {
            $entity->delete();
        }

        $this->clearDatabase();
    }

    public function test_create()
    {
        $gameDate = $this->gameDatesToCleanup[0];
        $this->validateGameDate($gameDate, $this->season, self::$expectedDefaults);
    }

    public function test_lookupById()
    {
        $gameDate = GameDate::lookupById($this->gameDatesToCleanup[0]->id);
        $this->validateGameDate($gameDate, $this->season, self::$expectedDefaults);
    }

    public function test_lookupByDay()
    {
        $gameDate = GameDate::lookupByDay($this->season, self::$expectedDefaults['day']);
        $this->validateGameDate($gameDate, $this->season, self::$expectedDefaults);
    }

    public function test_lookupBySeason()
    {
        $gameDates = GameDate::lookupBySeason($this->season);
        $this->assertTrue(count($gameDates) == 2);
    }

    public function validateGameDate($gameDate, $season, $expectedDefaults)
    {
        $this->assertTrue($gameDate->id > 0);
        $this->assertEquals($expectedDefaults['day'],          $gameDate->day);
        $this->assertEquals($season,                            $gameDate->season);
    }
}