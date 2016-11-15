<?php
require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/autoload.php';
require_once 'helper.php';

class Model_FacilityTest extends Model_TestHelpers {

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
    public $m_image = 'hello.jpg';
    public $m_preApproved = 0;
    public $m_enabled = 1;


    /**
     * Prepare to run test
     */
    protected function setUp()
    {
        $this->primeDatabase();
        $this->m_facility->_delete();
    }

    /**
     * Default Construction
     */
    public function testStaticMethods()
    {
        // Test Create
        $facility = Model_Fields_Facility::Create($this->m_league, $this->m_name, $this->m_address1, $this->m_address2,
            $this->m_city, $this->m_state, $this->m_postalCode, $this->m_country, $this->m_contactName, $this->m_contactEmail,
            $this->m_contactPhone, $this->m_image, $this->m_preApproved, 1);
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
        $this->assertEquals($facility->image, $this->m_image);
        $this->assertEquals($facility->preApproved, $this->m_preApproved);
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
        $this->assertEquals($facility->image, $this->m_image);
        $this->assertEquals($facility->preApproved, $this->m_preApproved);
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
        $this->assertEquals($facility->image, $this->m_image);
        $this->assertEquals($facility->preApproved, $this->m_preApproved);
        $this->assertEquals($facility->enabled, $this->m_enabled);
        $this->assertEquals($facility->m_league->name, $this->m_league->name);
        $this->assertTrue($facility->isLoaded());
        $this->assertFalse($facility->isModified());

        // Test LookupById
        $facilities = Model_Fields_Facility::LookupByLeague($this->m_league);
        $this->assertTrue(count($facilities) == 1);
        $facility = $facilities[0];
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
        $this->assertEquals($facility->image, $this->m_image);
        $this->assertEquals($facility->preApproved, $this->m_preApproved);
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