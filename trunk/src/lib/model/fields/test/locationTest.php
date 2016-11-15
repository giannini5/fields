<?php
require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/autoload.php';
require_once 'helper.php';

class Model_LocationTest extends Model_TestHelpers
{
    public $m_locationName = 'San Francisco';

    /**
     * Prepare to run test
     */
    protected function setUp()
    {
        $this->primeDatabase();
        $this->m_location->_delete();
    }

    /**
     * Default Construction
     */
    public function testStaticMethods()
    {
        $location = Model_Fields_Location::Create($this->m_league->id, $this->m_locationName);
        $locationId = $location->id;
        $this->assertEquals($location->name, $this->m_locationName);

        $location = Model_Fields_Location::LookupByName($this->m_league->id, $this->m_locationName);
        $this->assertEquals($location->name, $this->m_locationName);
        $this->assertEquals($location->id, $locationId);

        $location = Model_Fields_Location::LookupById($locationId);
        $this->assertEquals($location->name, $this->m_locationName);
        $this->assertEquals($location->id, $locationId);

        $locations = Model_Fields_Location::GetLocations($this->m_league->id);
        $this->assertEquals(count($locations), 1);
        $this->assertEquals($locations[0]->name, $this->m_locationName);
        $this->assertEquals($locations[0]->id, $locationId);
    }
}
?>
