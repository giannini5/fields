<?php
require_once '../../../autoLoader.php';

class Model_SeasonTest extends PHPUnit_Framework_TestCase {

    public $m_leagueName = 'Test AYSO Region 122';
    public $m_league;
    public $m_name = 'Fall 2010';
    public $m_enabled = 1;


    /**
     * Prepare to run test
     */
    protected function setUp()
    {
        $this->m_league = Model_Fields_League::LookupByName($this->m_leagueName, FALSE);
        if (isset($this->m_league)) {
            Model_Fields_Season::Delete($this->m_league, $this->m_name);
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
        $season = Model_Fields_Season::Create($this->m_league, $this->m_name, 1);
        $id = $season->id;
        $this->assertEquals($season->leagueId, $this->m_league->id);
        $this->assertEquals($season->name, $this->m_name);
        $this->assertEquals($season->enabled, $this->m_enabled);
        $this->assertEquals($season->m_league->name, $this->m_league->name);
        $this->assertTrue($season->isLoaded());
        $this->assertFalse($season->isModified());

        // Test LookupByName
        $season = Model_Fields_Season::LookupByName($this->m_league, $this->m_name);
        $this->assertEquals($season->leagueId, $this->m_league->id);
        $this->assertEquals($season->name, $this->m_name);
        $this->assertEquals($season->enabled, $this->m_enabled);
        $this->assertEquals($season->m_league->name, $this->m_league->name);
        $this->assertTrue($season->isLoaded());
        $this->assertFalse($season->isModified());

        // Test LookupById
        $season = Model_Fields_Season::LookupById($id);
        $this->assertEquals($season->leagueId, $this->m_league->id);
        $this->assertEquals($season->name, $this->m_name);
        $this->assertEquals($season->enabled, $this->m_enabled);
        $this->assertEquals($season->m_league->name, $this->m_league->name);
        $this->assertTrue($season->isLoaded());
        $this->assertFalse($season->isModified());

        // Test modify, save and reload
        $season->enabled = 0;
        $season->setModified();
        $this->assertTrue($season->isModified());
        $season->saveModel();
        $season = Model_Fields_Season::LookupById($id);
        $this->assertEquals($season->enabled, 0);
        $this->assertTrue($season->isLoaded());
        $this->assertFalse($season->isModified());
    }
}