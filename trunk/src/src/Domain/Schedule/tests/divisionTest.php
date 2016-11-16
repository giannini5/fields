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
        'name'          => 'TEST Domain division name',
    );

    protected $divisionsToCleanup = array();
    protected $season;

    protected function setUp()
    {
        $this->primeDatabase();

        $this->season = Season::lookupById($this->defaultSeasonOrm->id);

        $this->divisionsToCleanup[] = Division::create(
            $this->season,
            self::$expectedDefaults['name']);
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

    public function test_lookupByName()
    {
        $division = Division::lookupByName($this->season, self::$expectedDefaults['name']);
        $this->validateDivision($division, $this->season, self::$expectedDefaults);
    }

    public function test_lookupBySeason()
    {
        $divisions = Division::lookupBySeason($this->season);
        $this->assertTrue(count($divisions) == 2);
    }

    public function validateDivision($division, $season, $expectedDefaults)
    {
        $this->assertTrue($division->id > 0);
        $this->assertEquals($expectedDefaults['name'],          $division->name);
        $this->assertEquals($season,                            $division->season);
    }
}