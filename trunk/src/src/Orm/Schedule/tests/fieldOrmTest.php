<?php

namespace DAG\Orm\Schedule;

require_once 'helper.php';


/**
 * @testSuite test Field ORM
 */
class FieldOrmTest extends ORM_TestHelper
{
    /**
     * Expected values on create and load
     */
    protected static $expectedDefaults =
        [
            self::NAME          => 'TEST Field',
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
        $fieldOrm = FieldOrm::create(
            $this->defaultFacilityOrm->id,
            self::$expectedDefaults[self::NAME],
            self::$expectedDefaults[self::ENABLED]);

        $this->verifyExpectedAttributes($fieldOrm, self::$expectedDefaults);
    }

    public function test_loadById()
    {
        $fieldOrm = FieldOrm::loadById($this->defaultFieldOrm->id);
        $this->verifyExpectedAttributes($fieldOrm, self::$defaultFieldOrmAttributes);
    }

    public function test_loadByName()
    {
        $fieldOrm = FieldOrm::loadByFacilityIdAndName($this->defaultFacilityOrm->id, self::$defaultFieldOrmAttributes[self::NAME]);
        $this->verifyExpectedAttributes($fieldOrm, self::$defaultFieldOrmAttributes);
    }

    private function verifyExpectedAttributes($fieldOrm, $attributes)
    {
        $this->assertTrue($fieldOrm->id > 0);
        $this->assertEquals($this->defaultFacilityOrm->id,      $fieldOrm->facilityId);
        $this->assertEquals($attributes[self::NAME],            $fieldOrm->name);
        $this->assertEquals($attributes[self::ENABLED],         $fieldOrm->enabled);
    }
}