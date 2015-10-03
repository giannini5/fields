<?php
require_once '../../../autoLoader.php';
require_once 'helper.php';

class Model_FieldTest extends Model_TestHelpers {
    public $m_name = 'Field B';
    public $m_enabled = 1;

    /**
     * Prepare to run test
     */
    protected function setUp()
    {
        $this->primeDatabase();
        $this->m_field->_delete();
    }

    /**
     * Default Construction
     */
    public function testStaticMethods()
    {
        // Test Create
        $field = Model_Fields_Field::Create($this->m_facility, $this->m_name, $this->m_enabled);
        $id = $field->id;
        $this->assertEquals($field->facilityId, $this->m_facility->id);
        $this->assertEquals($field->name, $this->m_name);
        $this->assertEquals($field->enabled, $this->m_enabled);
        $this->assertEquals($field->m_facility->name, $this->m_facility->name);
        $this->assertTrue($field->isLoaded());
        $this->assertFalse($field->isModified());

        // Test LookupByName
        $field = Model_Fields_Field::LookupByName($this->m_facility, $this->m_name);
        $this->assertEquals($field->facilityId, $this->m_facility->id);
        $this->assertEquals($field->name, $this->m_name);
        $this->assertEquals($field->enabled, $this->m_enabled);
        $this->assertEquals($field->m_facility->name, $this->m_facility->name);
        $this->assertTrue($field->isLoaded());
        $this->assertFalse($field->isModified());

        // Test LookupByFacility
        $fields = Model_Fields_Field::LookupByFacility($this->m_facility);
        $this->assertTrue(count($fields) == 1);
        $field = $fields[0];
        $this->assertEquals($field->facilityId, $this->m_facility->id);
        $this->assertEquals($field->name, $this->m_name);
        $this->assertEquals($field->enabled, $this->m_enabled);
        $this->assertEquals($field->m_facility->name, $this->m_facility->name);
        $this->assertTrue($field->isLoaded());
        $this->assertFalse($field->isModified());

        // Test LookupById
        $field = Model_Fields_Field::LookupById($id);
        $this->assertEquals($field->facilityId, $this->m_facility->id);
        $this->assertEquals($field->name, $this->m_name);
        $this->assertEquals($field->enabled, $this->m_enabled);
        $this->assertEquals($field->m_facility->name, $this->m_facility->name);
        $this->assertTrue($field->isLoaded());
        $this->assertFalse($field->isModified());

        // Test modify, save and reload
        $field->enabled = 0;
        $field->setModified();
        $this->assertTrue($field->isModified());
        $field->saveModel();
        $field = Model_Fields_Field::LookupById($id);
        $this->assertEquals($field->enabled, 0);
        $this->assertTrue($field->isLoaded());
        $this->assertFalse($field->isModified());
    }
}