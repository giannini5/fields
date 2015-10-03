<?php
require_once '../../../autoLoader.php';
require_once 'helper.php';

class Model_FacilityLocationTest extends Model_TestHelpers
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
        $facilityLocation = Model_Fields_FacilityLocation::Create($this->m_facility->id, $this->m_location->id);
        $facilityLocationId = $facilityLocation->id;
        $this->assertEquals($facilityLocation->facilityId, $this->m_facility->id);
        $this->assertEquals($facilityLocation->locationId, $this->m_location->id);

        $facilityLocation = Model_Fields_FacilityLocation::LookupByFacilityLocation($this->m_facility->id, $this->m_location->id);
        $this->assertEquals($facilityLocation->facilityId, $this->m_facility->id);
        $this->assertEquals($facilityLocation->locationId, $this->m_location->id);
        $this->assertEquals($facilityLocation->id, $facilityLocationId);

        $facilityLocation = Model_Fields_FacilityLocation::LookupById($facilityLocationId);
        $this->assertEquals($facilityLocation->facilityId, $this->m_facility->id);
        $this->assertEquals($facilityLocation->locationId, $this->m_location->id);
        $this->assertEquals($facilityLocation->id, $facilityLocationId);

        $locations = Model_Fields_FacilityLocation::GetLocations($this->m_facility->id);
        $this->assertEquals(count($locations), 1);
        $this->assertEquals($this->m_location->name, $locations[0]->name);
    }
}
