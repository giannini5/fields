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

    public function test_findByNameTrue()
    {
        $result = Facility::findByName($this->season, self::$expectedDefaults['name'], $facility);
        $this->assertTrue($result);
        $this->validateFacility($facility, $this->season, self::$expectedDefaults);
    }

    public function test_findByNameFalse()
    {
        $result = Facility::findByName($this->season, 'Not my Facility Name', $facility);
        $this->assertFalse($result);
    }

    public function test_lookupBySeason()
    {
        $facilities = Facility::lookupBySeason($this->season);
        $this->assertTrue(count($facilities) == 2);
    }

    public function test_set()
    {
        $facility = $this->facilitysToCleanup[0];

        $expectedDefaults = [];
        $facility->name         = $expectedDefaults['name']           = 'dave';
        $facility->address1     = $expectedDefaults['address1']       = 'dave1';
        $facility->address2     = $expectedDefaults['address2']       = 'dave2';
        $facility->city         = $expectedDefaults['city']           = 'daveCity';
        $facility->state        = $expectedDefaults['state']          = 'daveState';
        $facility->country      = $expectedDefaults['country']        = 'daveCountry';
        $facility->postalCode   = $expectedDefaults['postalCode']     = 'davePostal';
        $facility->contactName  = $expectedDefaults['contactName']    = 'daveName';
        $facility->contactEmail = $expectedDefaults['contactEmail']   = 'daveEmail';
        $facility->contactPhone = $expectedDefaults['contactPhone']   = 'davePhone';
        $facility->image        = $expectedDefaults['image']          = 'daveImage';
        $facility->enabled      = $expectedDefaults['enabled']        = 0;

        $this->validateFacility($facility, $this->season, $expectedDefaults);
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