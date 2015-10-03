<?php
require_once '../../../autoLoader.php';
require_once 'helper.php';

class Model_DivisionTest extends Model_TestHelpers {

    public $m_name = 'Fall 2010';
    public $m_enabled = 1;

    /**
     * Prepare to run test
     */
    protected function setUp()
    {
        $this->primeDatabase();
        $this->m_division->_delete();
    }

    /**
     * Default Construction
     */
    public function testStaticMethods()
    {
        // Test Create
        $division = Model_Fields_Division::Create($this->m_league, $this->m_name, 1);
        $id = $division->id;
        $this->assertEquals($division->leagueId, $this->m_league->id);
        $this->assertEquals($division->name, $this->m_name);
        $this->assertEquals($division->enabled, $this->m_enabled);
        $this->assertEquals($division->m_league->name, $this->m_league->name);
        $this->assertTrue($division->isLoaded());
        $this->assertFalse($division->isModified());

        // Test LookupByName
        $division = Model_Fields_Division::LookupByName($this->m_league, $this->m_name);
        $this->assertEquals($division->leagueId, $this->m_league->id);
        $this->assertEquals($division->name, $this->m_name);
        $this->assertEquals($division->enabled, $this->m_enabled);
        $this->assertEquals($division->m_league->name, $this->m_league->name);
        $this->assertTrue($division->isLoaded());
        $this->assertFalse($division->isModified());

        // Test LookupById
        $division = Model_Fields_Division::LookupById($id);
        $this->assertEquals($division->leagueId, $this->m_league->id);
        $this->assertEquals($division->name, $this->m_name);
        $this->assertEquals($division->enabled, $this->m_enabled);
        $this->assertEquals($division->m_league->name, $this->m_league->name);
        $this->assertTrue($division->isLoaded());
        $this->assertFalse($division->isModified());

        // Test modify, save and reload
        $division->enabled = 0;
        $division->setModified();
        $this->assertTrue($division->isModified());
        $division->saveModel();
        $division = Model_Fields_Division::LookupById($id);
        $this->assertEquals($division->enabled, 0);
        $this->assertTrue($division->isLoaded());
        $this->assertFalse($division->isModified());

        // Test GetList - 0 in list
        $divisions = Model_Fields_Division::GitList($this->m_league);
        $this->assertEquals(count($divisions), 0);

        // Test GetList - 1 in list
        $division->enabled = 1;
        $division->setModified();
        $division->saveModel();
        $divisions = Model_Fields_Division::GitList($this->m_league);
        $this->assertEquals(count($divisions), 1);

        // Test GetList - 5 in list
        $division = Model_Fields_Division::Create($this->m_league, 'testDiv2', 1);
        $division = Model_Fields_Division::Create($this->m_league, 'testDiv3', 1);
        $division = Model_Fields_Division::Create($this->m_league, 'testDiv4', 1);
        $division = Model_Fields_Division::Create($this->m_league, 'testDiv5', 1);
        $divisions = Model_Fields_Division::GitList($this->m_league);
        $this->assertEquals(count($divisions), 5);
    }
}