<?php

namespace DAG\Domain\Schedule;

use DAG\Orm\Schedule\ORM_TestHelper;

require_once dirname(dirname(dirname(__DIR__))) . '/Orm/Schedule/tests/helper.php';



/**
 * @testSuite test Field
 */
class FieldTest extends ORM_TestHelper
{
    /**
     * Expected values on create and load
     */
    protected static $expectedDefaults = array(
        'name'          => 'TEST Domain field name',
        'enabled'       => 0,
    );

    protected $fieldsToCleanup = array();
    protected $facility;

    protected function setUp()
    {
        $this->primeDatabase();

        $this->facility = Facility::lookupById($this->defaultFacilityOrm->id);

        $this->fieldsToCleanup[] = Field::create(
            $this->facility,
            self::$expectedDefaults['name'],
            self::$expectedDefaults['enabled']);
    }

    protected function tearDown()
    {
        foreach ($this->fieldsToCleanup as $entity) {
            $entity->delete();
        }

        $this->clearDatabase();
    }

    public function test_create()
    {
        $field = $this->fieldsToCleanup[0];
        $this->validateField($field, $this->facility, self::$expectedDefaults);
    }

    public function test_lookupById()
    {
        $field = Field::lookupById($this->fieldsToCleanup[0]->id);
        $this->validateField($field, $this->facility, self::$expectedDefaults);
    }

    public function test_lookupByFacility()
    {
        $fields = Field::lookupByFacility($this->facility);
        $this->assertEquals(2, count($fields));
    }

    public function test_lookupByName()
    {
        $field = Field::lookupByName($this->facility, self::$expectedDefaults['name']);
        $this->validateField($field, $this->facility, self::$expectedDefaults);
    }

    public function test_findByNameTrue()
    {
        $result = Field::findByName($this->facility, self::$expectedDefaults['name'], $field);
        $this->assertTrue($result);
        $this->validateField($field, $this->facility, self::$expectedDefaults);
    }

    public function test_findByNameFalse()
    {
        $result = Field::findByName($this->facility, 'Not a field name', $field);
        $this->assertFalse($result);
    }

    public function test_set()
    {
        $field = Field::lookupById($this->fieldsToCleanup[0]->id);
        $field->name    = $expectedValues['name']     = "New name";
        $field->enabled = $expectedValues['enabled']  = "0";
        $this->validateField($field, $this->facility, $expectedValues);

        $field = Field::lookupById($field->id);
        $this->validateField($field, $this->facility, $expectedValues);
    }

    public function validateField($field, $facility, $expectedDefaults)
    {
        $this->assertTrue($field->id > 0);
        $this->assertEquals($expectedDefaults['name'],          $field->name);
        $this->assertEquals($expectedDefaults['enabled'],       $field->enabled);
        $this->assertEquals($facility,                          $field->facility);
    }
}