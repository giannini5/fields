<?php

namespace DAG\Domain\Schedule;

use DAG\Orm\Schedule\ORM_TestHelper;

require_once dirname(dirname(dirname(__DIR__))) . '/Orm/Schedule/tests/helper.php';



/**
 * @testSuite test Family
 */
class FamilyTest extends ORM_TestHelper
{
    /**
     * Expected values on create and load
     */
    protected static $expectedDefaults = array(
        'phone'         => '14156896044',
    );

    protected $familysToCleanup = array();
    protected $season;

    protected function setUp()
    {
        $this->primeDatabase();

        $this->season = Season::lookupById($this->defaultSeasonOrm->id);

        $this->familysToCleanup[] = Family::create(
            $this->season,
            self::$expectedDefaults['phone']);
    }

    protected function tearDown()
    {
        foreach ($this->familysToCleanup as $entity) {
            $entity->delete();
        }

        $this->clearDatabase();
    }

    public function test_create()
    {
        $family = $this->familysToCleanup[0];
        $this->validateFamily($family, $this->season, self::$expectedDefaults);
    }

    public function test_lookupById()
    {
        $family = Family::lookupById($this->familysToCleanup[0]->id);
        $this->validateFamily($family, $this->season, self::$expectedDefaults);
    }

    public function test_lookupByPhone()
    {
        $family = Family::lookupByPhone($this->season, self::$expectedDefaults['phone']);
        $this->validateFamily($family, $this->season, self::$expectedDefaults);
    }

    public function test_lookupBySeason()
    {
        $families = Family::lookupBySeason($this->season);
        $this->assertTrue(count($families) == 2);
    }

    public function validateFamily($family, $season, $expectedDefaults)
    {
        $this->assertTrue($family->id > 0);
        $this->assertEquals($expectedDefaults['phone'],         $family->phone);
        $this->assertEquals($season,                            $family->season);
    }
}