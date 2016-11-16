<?php

namespace DAG\Orm\Schedule;

require_once 'helper.php';


/**
 * @testSuite test Season ORM
 */
class SeasonOrmTest extends ORM_TestHelper
{
    /**
     * Expected values on create and load
     */
    protected static $expectedDefaults = array(
        'name'          => 'TEST season name',
        'startDate'     => '2016-09-01',
        'endDate'       => '2016-11-20',
        'startTime'     => '08:00:00',
        'endTime'       => '19:00:00',
        'daysOfWeek'    => '0000011',
        'enabled'       =>  1);

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
        $seasonOrm = SeasonOrm::create(
            $this->defaultLeagueOrm->id,
            self::$expectedDefaults[self::NAME],
            self::$expectedDefaults[self::START_DATE],
            self::$expectedDefaults[self::END_DATE],
            self::$expectedDefaults[self::START_TIME],
            self::$expectedDefaults[self::END_TIME],
            self::$expectedDefaults[self::DAYS_OF_WEEK],
            self::$expectedDefaults[self::ENABLED]);
        $this->verifyExpectedAttributes($seasonOrm, self::$expectedDefaults);
    }

    public function test_loadById()
    {
        $seasonOrm = SeasonOrm::loadById($this->defaultSeasonOrm->id);
        $this->verifyExpectedAttributes($seasonOrm, self::$defaultSeasonOrmAttributes);;
    }

    public function test_loadByName()
    {
        $seasonOrm = SeasonOrm::loadByLeagueIdAndName($this->defaultLeagueOrm->id, $this->defaultSeasonOrm->name);
        $this->verifyExpectedAttributes($seasonOrm, self::$defaultSeasonOrmAttributes);;
    }

    private function verifyExpectedAttributes($seasonOrm, $attributes)
    {
        $this->assertTrue($seasonOrm->id > 0);
        $this->assertEquals($this->defaultLeagueOrm->id,    $seasonOrm->leagueId);
        $this->assertEquals($attributes['name'],            $seasonOrm->name);
        $this->assertEquals($attributes['startDate'],       $seasonOrm->startDate);
        $this->assertEquals($attributes['endDate'],         $seasonOrm->endDate);
        $this->assertEquals($attributes['startTime'],       $seasonOrm->startTime);
        $this->assertEquals($attributes['endTime'],         $seasonOrm->endTime);
        $this->assertEquals($attributes['daysOfWeek'],      $seasonOrm->daysOfWeek);
        $this->assertEquals($attributes['enabled'],         $seasonOrm->enabled);
    }
}