<?php
require_once '../../../autoLoader.php';

class Model_FieldTest extends PHPUnit_Framework_TestCase {


    public $m_leagueName = 'Test AYSO Region 122';
    public $m_league;
    public $m_facilityName = 'Girsh Park';
    public $m_facility;
    public $m_name = 'Field B';
    public $m_enabled = 1;


    /**
     * Prepare to run test
     */
    protected function setUp()
    {
        $this->m_league = Model_Fields_League::LookupByName($this->m_leagueName, FALSE);
        if (isset($this->m_league)) {
            $this->m_facility = Model_Fields_Facility::LookupByName($this->m_league, $this->m_facilityName, FALSE);
            if (isset($this->m_facility)) {
                Model_Fields_Field::Delete($this->m_facility, $this->m_name);
                Model_Fields_Facility::Delete($this->m_league, $this->m_facilityName);
            }

            Model_Fields_League::Delete($this->m_leagueName);
        }

        $this->m_league = Model_Fields_League::Create($this->m_leagueName);
        $this->m_facility = Model_Fields_Facility::Create($this->m_league, $this->m_facilityName,
            'address1', 'address2', 'city', 'state', 'p-code', 'country', 'contactName', 'contactEmail', 'contactPhone', 1);
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