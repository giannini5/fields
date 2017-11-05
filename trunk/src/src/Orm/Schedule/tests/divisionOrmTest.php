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
            self::NAME                      => 'TEST Division',
            self::GENDER                    => 'TEST Boy',
            self::DISPLAY_ORDER             => '5',
            self::GAME_DURATION_MINUTES     => '25',
            self::MINUTES_BETWEEN_GAMES        => '60',
            self::SCORING_TRACKED           => '0',
            self::COMBINE_LEAGUE_SCHEDULES  => '1',
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
            self::$expectedDefaults[self::NAME],
            self::$expectedDefaults[self::GENDER],
            self::$expectedDefaults[self::GAME_DURATION_MINUTES],
            self::$expectedDefaults[self::MINUTES_BETWEEN_GAMES],
            self::$expectedDefaults[self::DISPLAY_ORDER],
            self::$expectedDefaults[self::SCORING_TRACKED],
            self::$expectedDefaults[self::COMBINE_LEAGUE_SCHEDULES]);

        $this->verifyExpectedAttributes($divisionOrm, self::$expectedDefaults);
    }

    public function test_loadById()
    {
        $divisionOrm = DivisionOrm::loadById($this->defaultDivisionOrm->id);
        $this->verifyExpectedAttributes($divisionOrm, self::$defaultDivisionOrmAttributes);
    }

    public function test_loadByNameGender()
    {
        $divisionOrm = DivisionOrm::loadBySeasonIdAndNameAndGender(
            $this->defaultSeasonOrm->id,
            self::$defaultDivisionOrmAttributes[self::NAME],
            self::$defaultDivisionOrmAttributes[self::GENDER]);
        $this->verifyExpectedAttributes($divisionOrm, self::$defaultDivisionOrmAttributes);
    }

    public function test_loadByName()
    {
        $divisionOrms = DivisionOrm::loadBySeasonIdAndName($this->defaultSeasonOrm->id, self::$defaultDivisionOrmAttributes[self::NAME]);
        $this->assertEquals(1, count($divisionOrms));
        $this->verifyExpectedAttributes($divisionOrms[0], self::$defaultDivisionOrmAttributes);
    }

    private function verifyExpectedAttributes($divisionOrm, $attributes)
    {
        $this->assertTrue($divisionOrm->id > 0);
        $this->assertEquals($this->defaultSeasonOrm->id,                    $divisionOrm->seasonId);
        $this->assertEquals($attributes[self::NAME],                        $divisionOrm->name);
        $this->assertEquals($attributes[self::GENDER],                      $divisionOrm->gender);
        $this->assertEquals($attributes[self::GAME_DURATION_MINUTES],       $divisionOrm->gameDurationMinutes);
        $this->assertEquals($attributes[self::MINUTES_BETWEEN_GAMES],       $divisionOrm->minutesBetweenGames);
        $this->assertEquals($attributes[self::DISPLAY_ORDER],               $divisionOrm->displayOrder);
        $this->assertEquals($attributes[self::SCORING_TRACKED],             $divisionOrm->scoringTracked);
        $this->assertEquals($attributes[self::COMBINE_LEAGUE_SCHEDULES],    $divisionOrm->combineLeagueSchedules);
    }
}