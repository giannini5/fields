<?php
require_once '../../../autoLoader.php';
require_once 'helper.php';

class Model_SeasonTest extends Model_TestHelpers {

    public $m_name = 'Fall 2010';
    public $m_enabled = 1;

    /**
     * Prepare to run test
     */
    protected function setUp()
    {
        $this->primeDatabase();
        $this->m_season->_delete();
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