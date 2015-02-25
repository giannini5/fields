<?php
require_once '../../../autoLoader.php';

class Model_FieldAvailabilityTest extends PHPUnit_Framework_TestCase {


    public $m_leagueName = 'Test AYSO Region 122';
    public $m_league;
    public $m_facilityName = 'Girsh Park';
    public $m_facility;
    public $m_fieldName = 'Field B';
    public $m_field;

    public $m_startDate = '2015-01-15';
    public $m_endDate = '2015-06-30';
    public $m_startTime = '15:30:00';
    public $m_endTime = '19:00:00';


    /**
     * Prepare to run test
     */
    protected function setUp()
    {
        $this->m_league = Model_Fields_League::LookupByName($this->m_leagueName, FALSE);
        if (isset($this->m_league)) {
            $this->m_facility = Model_Fields_Facility::LookupByName($this->m_league, $this->m_facilityName, FALSE);
            if (isset($this->m_facility)) {
                $this->m_field = Model_Fields_Field::LookupByName($this->m_facility, $this->m_fieldName, FALSE);
                if (isset($this->m_field)) {
                    Model_Fields_FieldAvailability::Delete($this->m_field);
                }
                Model_Fields_Field::Delete($this->m_facility, $this->m_fieldName);
                Model_Fields_Facility::Delete($this->m_league, $this->m_facilityName);
            }

            Model_Fields_League::Delete($this->m_leagueName);
        }

        $this->m_league = Model_Fields_League::Create($this->m_leagueName);
        $this->m_facility = Model_Fields_Facility::Create($this->m_league, $this->m_facilityName,
            'address1', 'address2', 'city', 'state', 'p-code', 'country', 'contactName', 'contactEmail', 'contactPhone', 1);
        $this->m_field = Model_Fields_Field::Create($this->m_facility, $this->m_fieldName, 1);
    }

    /**
     * Default Construction
     */
    public function testStaticMethods()
    {
        // Test Create
        $fieldAvailability = Model_Fields_FieldAvailability::Create($this->m_field, $this->m_startDate, $this->m_endDate,
            $this->m_startTime, $this->m_endTime);
        $id = $fieldAvailability->id;
        $this->assertEquals($fieldAvailability->fieldId, $this->m_field->id);
        $this->assertEquals($fieldAvailability->startDate, $this->m_startDate);
        $this->assertEquals($fieldAvailability->endDate, $this->m_endDate);
        $this->assertEquals($fieldAvailability->startTime, $this->m_startTime);
        $this->assertEquals($fieldAvailability->endTime, $this->m_endTime);
        $this->assertEquals($fieldAvailability->m_field->name, $this->m_field->name);
        $this->assertTrue($fieldAvailability->isLoaded());
        $this->assertFalse($fieldAvailability->isModified());

        // Test LookupByName
        $fieldAvailability = Model_Fields_FieldAvailability::LookupByFieldId($this->m_field->id);
        $this->assertEquals($fieldAvailability->fieldId, $this->m_field->id);
        $this->assertEquals($fieldAvailability->startDate, $this->m_startDate);
        $this->assertEquals($fieldAvailability->endDate, $this->m_endDate);
        $this->assertEquals($fieldAvailability->startTime, $this->m_startTime);
        $this->assertEquals($fieldAvailability->endTime, $this->m_endTime);
        $this->assertEquals($fieldAvailability->m_field->name, $this->m_field->name);
        $this->assertTrue($fieldAvailability->isLoaded());
        $this->assertFalse($fieldAvailability->isModified());

        // Test LookupById
        $fieldAvailability = Model_Fields_FieldAvailability::LookupById($id);
        $this->assertEquals($fieldAvailability->fieldId, $this->m_field->id);
        $this->assertEquals($fieldAvailability->startDate, $this->m_startDate);
        $this->assertEquals($fieldAvailability->endDate, $this->m_endDate);
        $this->assertEquals($fieldAvailability->startTime, $this->m_startTime);
        $this->assertEquals($fieldAvailability->endTime, $this->m_endTime);
        $this->assertEquals($fieldAvailability->m_field->name, $this->m_field->name);
        $this->assertTrue($fieldAvailability->isLoaded());
        $this->assertFalse($fieldAvailability->isModified());

        // Test modify, save and reload
        $fieldAvailability->startDate = '2015-02-14';
        $fieldAvailability->setModified();
        $this->assertTrue($fieldAvailability->isModified());
        $fieldAvailability->saveModel();
        $fieldAvailability = Model_Fields_FieldAvailability::LookupById($id);
        $this->assertEquals($fieldAvailability->startDate, '2015-02-14');
        $this->assertTrue($fieldAvailability->isLoaded());
        $this->assertFalse($fieldAvailability->isModified());
    }
}