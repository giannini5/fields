<?php
require_once '../../../autoLoader.php';
require_once 'helper.php';

class Model_DivisionFieldTest extends Model_TestHelpers
{
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
        $divisionField = Model_Fields_DivisionField::Create($this->m_division->id, $this->m_facility->id, $this->m_field->id);
        $divisionFieldId = $divisionField->id;
        $this->assertEquals($divisionField->divisionId, $this->m_division->id);
        $this->assertEquals($divisionField->facilityId, $this->m_facility->id);
        $this->assertEquals($divisionField->fieldId, $this->m_field->id);

        $divisionField = Model_Fields_DivisionField::LookupByDivisionField($this->m_division->id, $this->m_facility->id, $this->m_field->id);
        $this->assertEquals($divisionField->divisionId, $this->m_division->id);
        $this->assertEquals($divisionField->facilityId, $this->m_facility->id);
        $this->assertEquals($divisionField->fieldId, $this->m_field->id);
        $this->assertEquals($divisionField->id, $divisionFieldId);

        $divisionField = Model_Fields_DivisionField::LookupById($divisionFieldId);
        $this->assertEquals($divisionField->divisionId, $this->m_division->id);
        $this->assertEquals($divisionField->facilityId, $this->m_facility->id);
        $this->assertEquals($divisionField->fieldId, $this->m_field->id);
        $this->assertEquals($divisionField->id, $divisionFieldId);

        $divisionFields = Model_Fields_DivisionField::GetFacilityFields($this->m_division->id, $this->m_facility->id);
        $this->assertEquals(count($divisionFields), 1);
        $this->assertEquals($this->m_field->name, $divisionFields[0]->name);
    }
}
