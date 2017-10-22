<?php

namespace DAG\Domain\Schedule;

use DAG\Orm\Schedule\ORM_TestHelper;

require_once dirname(dirname(dirname(__DIR__))) . '/Orm/Schedule/tests/helper.php';



/**
 * @testSuite test Division
 */
class DivisionTest extends ORM_TestHelper
{
    /**
     * Expected values on create and load
     */
    protected static $expectedDefaults = array(
        'name'                      => 'TEST Domain division name',
        'gender'                    => 'TEST gender',
        'gameDurationMinutes'       => 120,
        'scoringTracked'            => 1,
        'displayOrder'              => 14,
        'combineLeagueSchedules'    => 0,
    );

    protected $divisionsToCleanup = array();
    protected $season;

    protected function setUp()
    {
        $this->primeDatabase();

        $this->season = Season::lookupById($this->defaultSeasonOrm->id);

        $this->divisionsToCleanup[] = Division::create(
            $this->season,
            self::$expectedDefaults['name'],
            self::$expectedDefaults['gender'],
            self::$expectedDefaults['gameDurationMinutes'],
            self::$expectedDefaults['displayOrder'],
            self::$expectedDefaults['scoringTracked']);
    }

    protected function tearDown()
    {
        foreach ($this->divisionsToCleanup as $entity) {
            $entity->delete();
        }

        $this->clearDatabase();
    }

    public function test_create()
    {
        $division = $this->divisionsToCleanup[0];
        $this->validateDivision($division, $this->season, self::$expectedDefaults);
    }

    public function test_lookupById()
    {
        $division = Division::lookupById($this->divisionsToCleanup[0]->id);
        $this->validateDivision($division, $this->season, self::$expectedDefaults);
    }

    public function test_lookupByNameAndGender()
    {
        $division = Division::lookupByNameAndGender(
            $this->season,
            self::$expectedDefaults['name'],
            self::$expectedDefaults['gender']);
        $this->validateDivision($division, $this->season, self::$expectedDefaults);
    }

    public function test_findByNameAndGender()
    {
        $result = Division::findByNameAndGender(
            $this->season,
            self::$expectedDefaults['name'],
            self::$expectedDefaults['gender'],
            $division);

        $this->assertTrue($result, "Division not found");
        $this->validateDivision($division, $this->season, self::$expectedDefaults);
    }

    public function test_findByNameAndGenderNotFound()
    {
        $result = Division::findByNameAndGender(
            $this->season,
            '99U',
            'OldPeople',
            $division);

        $this->assertFalse($result, "Division unexpectedly found");
    }

    public function test_lookupByName()
    {
        $divisions = Division::lookupByName($this->season, self::$expectedDefaults['name']);
        $this->assertEquals(1, count($divisions));
        $this->validateDivision($divisions[0], $this->season, self::$expectedDefaults);
    }

    public function test_lookupBySeason()
    {
        $divisions = Division::lookupBySeason($this->season);
        $this->assertTrue(count($divisions) == 2);
    }

    public function test_setScoringTracked()
    {
        $division = Division::lookupById($this->divisionsToCleanup[0]->id);
        $this->assertTrue($division->isScoringTracked);

        $division->scoringTracked = 0;
        $this->assertFalse($division->isScoringTracked);
    }

    public function test_setCombineLeagueSchedules()
    {
        $division = Division::lookupById($this->divisionsToCleanup[0]->id);
        $this->assertEquals(0, $division->combineLeagueSchedules);

        $division->combineLeagueSchedules = 1;
        $this->assertEquals(1, $division->combineLeagueSchedules);
    }

    public function validateDivision($division, $season, $expectedDefaults)
    {
        $this->assertTrue($division->id > 0);
        $this->assertEquals($expectedDefaults['name'],                      $division->name);
        $this->assertEquals($expectedDefaults['gender'],                    $division->gender);
        $this->assertEquals($expectedDefaults['gameDurationMinutes'],       $division->gameDurationMinutes);
        $this->assertEquals($expectedDefaults['displayOrder'],              $division->displayOrder);
        $this->assertEquals($expectedDefaults['combineLeagueSchedules'],    $division->combineLeagueSchedules);
        $this->assertEquals($season,                                        $division->season);
        $this->assertTrue($division->isScoringTracked);
    }
}