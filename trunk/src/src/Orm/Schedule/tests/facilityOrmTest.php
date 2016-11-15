<?php

namespace DAG\Orm\Schedule;

require_once 'helper.php';


/**
 * @testSuite test Facility ORM
 */
class FacilityOrmTest extends ORM_TestHelper
{
    /**
     * Expected values on create and load
     */
    protected static $expectedDefaults =
        [
            self::NAME          => 'TEST Facility',
            self::ADDRESS1      => 'test addr1',
            self::ADDRESS2      => 'test addr2',
            self::CITY          => 'test city',
            self::STATE         => 'test state',
            self::POSTAL_CODE   => 'test postal',
            self::COUNTRY       => 'test country',
            self::CONTACT_NAME  => 'test contact name',
            self::CONTACT_EMAIL => 'test contact email',
            self::CONTACT_PHONE => 'test contact phone',
            self::IMAGE         => 'test image',
            self::ENABLED       => 1,
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
        $facilityOrm = FacilityOrm::create(
            $this->defaultSeasonOrm->id,
            self::$expectedDefaults[self::NAME],
            self::$expectedDefaults[self::ADDRESS1],
            self::$expectedDefaults[self::ADDRESS2],
            self::$expectedDefaults[self::CITY],
            self::$expectedDefaults[self::STATE],
            self::$expectedDefaults[self::POSTAL_CODE],
            self::$expectedDefaults[self::COUNTRY],
            self::$expectedDefaults[self::CONTACT_NAME],
            self::$expectedDefaults[self::CONTACT_EMAIL],
            self::$expectedDefaults[self::CONTACT_PHONE],
            self::$expectedDefaults[self::IMAGE],
            self::$expectedDefaults[self::ENABLED]);
        
        $this->verifyExpectedAttributes($facilityOrm, self::$expectedDefaults);
    }
    
    public function test_loadById()
    {
        $facilityOrm = FacilityOrm::loadById($this->defaultFacilityOrm->id);
        $this->verifyExpectedAttributes($facilityOrm, self::$defaultFacilityOrmAttributes);
    }

    public function test_loadByName()
    {
        $facilityOrm = FacilityOrm::loadBySeasonIdAndName($this->defaultSeasonOrm->id, self::$defaultFacilityOrmAttributes[self::NAME]);
        $this->verifyExpectedAttributes($facilityOrm, self::$defaultFacilityOrmAttributes);
    }

    private function verifyExpectedAttributes($facilityOrm, $attributes)
    {
        $this->assertTrue($facilityOrm->id > 0);
        $this->assertEquals($this->defaultSeasonOrm->id,        $facilityOrm->seasonId);
        $this->assertEquals($attributes[self::NAME],            $facilityOrm->name);
        $this->assertEquals($attributes[self::ADDRESS1],        $facilityOrm->address1);
        $this->assertEquals($attributes[self::ADDRESS2],        $facilityOrm->address2);
        $this->assertEquals($attributes[self::CITY],            $facilityOrm->city);
        $this->assertEquals($attributes[self::STATE],           $facilityOrm->state);
        $this->assertEquals($attributes[self::POSTAL_CODE],     $facilityOrm->postalCode);
        $this->assertEquals($attributes[self::COUNTRY],         $facilityOrm->country);
        $this->assertEquals($attributes[self::CONTACT_NAME],    $facilityOrm->contactName);
        $this->assertEquals($attributes[self::CONTACT_EMAIL],   $facilityOrm->contactEmail);
        $this->assertEquals($attributes[self::CONTACT_PHONE],   $facilityOrm->contactPhone);
        $this->assertEquals($attributes[self::IMAGE],           $facilityOrm->image);
        $this->assertEquals($attributes[self::ENABLED],         $facilityOrm->enabled);
    }
}