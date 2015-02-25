<?php
require_once '../../../autoLoader.php';

class Model_FacilityTest extends PHPUnit_Framework_TestCase {

    public $m_leagueName = 'Test AYSO Region 122';
    public $m_league;
    public $m_name = 'Girsh Park';
    public $m_address1 = '2910 Paseo del Refugio';
    public $m_address2 = 'Backyard';
    public $m_city = 'Santa Barbara';
    public $m_state = 'California';
    public $m_postalCode = '93105';
    public $m_country = 'USA';
    public $m_contactName = 'David A. Giannini';
    public $m_contactEmail = 'david_giannini@hotmail.com';
    public $m_contactPhone = '8058989551';
    public $m_enabled = 1;


    /**
     * Prepare to run test
     */
    protected function setUp()
    {
        $this->m_league = Model_Fields_League::LookupByName($this->m_leagueName, FALSE);
        if (isset($this->m_league)) {
            Model_Fields_Facility::Delete($this->m_league, $this->m_name);
            Model_Fields_League::Delete($this->m_leagueName);
        }

        $this->m_league = Model_Fields_League::Create($this->m_leagueName);
    }

    /**
     * Default Construction
     */
    public function testStaticMethods()
    {
        // Test Create
        $facility = Model_Fields_Facility::Create($this->m_league, $this->m_name, $this->m_address1, $this->m_address2,
            $this->m_city, $this->m_state, $this->m_postalCode, $this->m_country, $this->m_contactName, $this->m_contactEmail, $this->m_contactPhone, 1);
        $id = $facility->id;
        $this->assertEquals($facility->leagueId, $this->m_league->id);
        $this->assertEquals($facility->name, $this->m_name);
        $this->assertEquals($facility->address1, $this->m_address1);
        $this->assertEquals($facility->address2, $this->m_address2);
        $this->assertEquals($facility->city, $this->m_city);
        $this->assertEquals($facility->state, $this->m_state);
        $this->assertEquals($facility->postalCode, $this->m_postalCode);
        $this->assertEquals($facility->country, $this->m_country);
        $this->assertEquals($facility->contactName, $this->m_contactName);
        $this->assertEquals($facility->contactEmail, $this->m_contactEmail);
        $this->assertEquals($facility->contactPhone, $this->m_contactPhone);
        $this->assertEquals($facility->enabled, $this->m_enabled);
        $this->assertEquals($facility->m_league->name, $this->m_league->name);
        $this->assertTrue($facility->isLoaded());
        $this->assertFalse($facility->isModified());

        // Test LookupByName
        $facility = Model_Fields_Facility::LookupByName($this->m_league, $this->m_name);
        $this->assertEquals($facility->leagueId, $this->m_league->id);
        $this->assertEquals($facility->name, $this->m_name);
        $this->assertEquals($facility->address1, $this->m_address1);
        $this->assertEquals($facility->address2, $this->m_address2);
        $this->assertEquals($facility->city, $this->m_city);
        $this->assertEquals($facility->state, $this->m_state);
        $this->assertEquals($facility->postalCode, $this->m_postalCode);
        $this->assertEquals($facility->country, $this->m_country);
        $this->assertEquals($facility->contactName, $this->m_contactName);
        $this->assertEquals($facility->contactEmail, $this->m_contactEmail);
        $this->assertEquals($facility->contactPhone, $this->m_contactPhone);
        $this->assertEquals($facility->enabled, $this->m_enabled);
        $this->assertEquals($facility->m_league->name, $this->m_league->name);
        $this->assertTrue($facility->isLoaded());
        $this->assertFalse($facility->isModified());

        // Test LookupById
        $facility = Model_Fields_Facility::LookupById($id);
        $this->assertEquals($facility->leagueId, $this->m_league->id);
        $this->assertEquals($facility->name, $this->m_name);
        $this->assertEquals($facility->address1, $this->m_address1);
        $this->assertEquals($facility->address2, $this->m_address2);
        $this->assertEquals($facility->city, $this->m_city);
        $this->assertEquals($facility->state, $this->m_state);
        $this->assertEquals($facility->postalCode, $this->m_postalCode);
        $this->assertEquals($facility->country, $this->m_country);
        $this->assertEquals($facility->contactName, $this->m_contactName);
        $this->assertEquals($facility->contactEmail, $this->m_contactEmail);
        $this->assertEquals($facility->contactPhone, $this->m_contactPhone);
        $this->assertEquals($facility->enabled, $this->m_enabled);
        $this->assertEquals($facility->m_league->name, $this->m_league->name);
        $this->assertTrue($facility->isLoaded());
        $this->assertFalse($facility->isModified());

        // Test modify, save and reload
        $facility->contactPhone = '8008886666';
        $facility->setModified();
        $this->assertTrue($facility->isModified());
        $facility->saveModel();
        $facility = Model_Fields_Facility::LookupById($id);
        $this->assertEquals($facility->contactPhone, '8008886666');
        $this->assertTrue($facility->isLoaded());
        $this->assertFalse($facility->isModified());
    }
}