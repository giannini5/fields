<?php

namespace DAG\Domain\Schedule;

use DAG\Orm\Schedule\ORM_TestHelper;

require_once dirname(dirname(dirname(__DIR__))) . '/Orm/Schedule/tests/helper.php';



/**
 * @testSuite test Facility
 */
class FacilityTest extends ORM_TestHelper
{
    /**
     * Expected values on create and load
     */
    protected static $expectedDefaults = array(
        'name'          => 'TEST Domain facility name',
        'address1'      => 'address 1',
        'address2'      => 'address 2',
        'city'          => 'city',
        'state'         => 'state',
        'postalCode'    => '93105-8020',
        'country'       => 'country',
        'contactName'   => 'contact name',
        'contactEmail'  => 'contact email',
        'contactPhone'  => 'contact phone',
        'image'         => 'image me',
        'enabled'       => 0,
    );

    protected $facilitysToCleanup = array();
    protected $season;

    protected function setUp()
    {
        $this->primeDatabase();

        $this->season = Season::lookupById($this->defaultSeasonOrm->id);

        $this->facilitysToCleanup[] = Facility::create(
            $this->season,
            self::$expectedDefaults['name'],
            self::$expectedDefaults['address1'],
            self::$expectedDefaults['address2'],
            self::$expectedDefaults['city'],
            self::$expectedDefaults['state'],
            self::$expectedDefaults['postalCode'],
            self::$expectedDefaults['country'],
            self::$expectedDefaults['contactName'],
            self::$expectedDefaults['contactEmail'],
            self::$expectedDefaults['contactPhone'],
            self::$expectedDefaults['image'],
            self::$expectedDefaults['enabled']);
    }

    protected function tearDown()
    {
        foreach ($this->facilitysToCleanup as $entity) {
            $entity->delete();
        }

        $this->clearDatabase();
    }

    public function test_create()
    {
        $facility = $this->facilitysToCleanup[0];
        $this->validateFacility($facility, $this->season, self::$expectedDefaults);
    }

    public function test_lookupById()
    {
        $facility = Facility::lookupById($this->facilitysToCleanup[0]->id);
        $this->validateFacility($facility, $this->season, self::$expectedDefaults);
    }

    public function test_lookupByName()
    {
        $facility = Facility::lookupByName($this->season, self::$expectedDefaults['name']);
        $this->validateFacility($facility, $this->season, self::$expectedDefaults);
    }

    public function test_lookupBySeason()
    {
        $facilities = Facility::lookupBySeason($this->season);
        $this->assertTrue(count($facilities) == 2);
    }

    public function validateFacility($facility, $season, $expectedDefaults)
    {
        $this->assertTrue($facility->id > 0);
        $this->assertEquals($expectedDefaults['name'],          $facility->name);
        $this->assertEquals($expectedDefaults['address1'],      $facility->address1);
        $this->assertEquals($expectedDefaults['address2'],      $facility->address2);
        $this->assertEquals($expectedDefaults['city'],          $facility->city);
        $this->assertEquals($expectedDefaults['state'],         $facility->state);
        $this->assertEquals($expectedDefaults['postalCode'],    $facility->postalCode);
        $this->assertEquals($expectedDefaults['country'],       $facility->country);
        $this->assertEquals($expectedDefaults['contactName'],   $facility->contactName);
        $this->assertEquals($expectedDefaults['contactEmail'],  $facility->contactEmail);
        $this->assertEquals($expectedDefaults['contactPhone'],  $facility->contactPhone);
        $this->assertEquals($expectedDefaults['image'],         $facility->image);
        $this->assertEquals($expectedDefaults['enabled'],       $facility->enabled);
        $this->assertEquals($season,                            $facility->season);
    }
}