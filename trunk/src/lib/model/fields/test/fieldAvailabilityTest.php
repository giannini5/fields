<?php
require_once '../../../autoLoader.php';
require_once 'helper.php';

class Model_FieldAvailabilityTest extends Model_TestHelpers {

    /**
     * Prepare to run test
     */
    protected function setUp()
    {
        $this->primeDatabase();
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