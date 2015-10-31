<?php
require_once '../../../autoLoader.php';

class Model_TestHelpers extends PHPUnit_Framework_TestCase {
    public $m_league;
    public $m_season;
    public $m_division;
    public $m_coach;
    public $m_team;
    public $m_facility;
    public $m_field;
    public $m_location;

    public $m_leagueName = 'Test AYSO Region 122';
    public $m_seasonName = 'Test 2015';
    public $m_divisionName = 'U10';
    public $m_gender = 'B';
    public $m_email = 'dag@callwave.com';
    public $m_locationName = 'Moon';

    public $m_startDate = '2015-01-15';
    public $m_endDate = '2015-06-30';
    public $m_startTime = '15:30:00';
    public $m_endTime = '19:00:00';

    /**
     * Clear out all entities for the test league
     */
    protected function clearEntities()
    {
        $this->m_league = Model_Fields_League::LookupByName($this->m_leagueName, FALSE);
        if (isset($this->m_league)) {
            $this->m_league->delete();
        }
    }

    /**
     * Prime the database for unit testing
     */
    protected function primeDatabase()
    {
        $this->clearEntities();

        $this->m_league = Model_Fields_League::Create($this->m_leagueName);
        $this->m_season = Model_Fields_Season::Create($this->m_league, $this->m_seasonName, $this->m_startDate, $this->m_endDate, $this->m_startTime, $this->m_endTime, 1);
        $this->m_division = Model_Fields_Division::Create($this->m_league, $this->m_divisionName, 1);
        $this->m_coach = Model_Fields_Coach::Create($this->m_season, $this->m_division, 'Dave', $this->m_email, '8052523944', 'hello');
        $this->m_team = Model_Fields_Team::Create($this->m_division, $this->m_coach, $this->m_gender, 'Test Team');
        $this->m_facility = Model_Fields_Facility::Create($this->m_league, 'Test Facility', 'addr1', 'addr2', 'Test City', 'Test State', '11111', 'USA', 'Dave', 'dag@dave.com', '8052523944', '', 1);
        $this->m_field = Model_Fields_Field::Create($this->m_facility, 'Test Field', 1);
        $this->m_location = Model_Fields_Location::Create($this->m_league->id, $this->m_locationName);
    }

    public function testDummy()
    {
        // Dummy test to make warning that there are no tests is this file go away.
        $this->assertEquals(1, 1);
    }
}