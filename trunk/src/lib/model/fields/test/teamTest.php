<?php
require_once '../../../autoLoader.php';

class Model_TeamTest extends PHPUnit_Framework_TestCase {

    public $m_leagueName = 'Test AYSO Region 122';
    public $m_league;
    public $m_divisionName = 'U10 Girls';
    public $m_division;
    public $m_teamNumber = 9;
    public $m_name = 'Flying Hawks';

    /**
     * Prepare to run test
     */
    protected function setUp()
    {
        $this->m_league = Model_Fields_League::LookupByName($this->m_leagueName, FALSE);
        if (isset($this->m_league)) {
            $this->m_division = Model_Fields_Division::LookupByName($this->m_league, $this->m_divisionName, FALSE);
            if (isset($this->m_division)) {
                Model_Fields_Team::Delete($this->m_division, $this->m_teamNumber);
                Model_Fields_Division::Delete($this->m_league, $this->m_divisionName);
            }

            Model_Fields_League::Delete($this->m_leagueName);
        }

        $this->m_league = Model_Fields_League::Create($this->m_leagueName);
        $this->m_division = Model_Fields_Division::Create($this->m_league, $this->m_divisionName, 1);
    }

    /**
     * Default Construction
     */
    public function testStaticMethods()
    {
        // Test Create
        $team = Model_Fields_Team::Create($this->m_division, $this->m_teamNumber, $this->m_name);
        $id = $team->id;
        $this->assertEquals($team->divisionId, $this->m_division->id);
        $this->assertEquals($team->teamNumber, $this->m_teamNumber);
        $this->assertEquals($team->name, $this->m_name);
        $this->assertEquals($team->m_division->name, $this->m_division->name);
        $this->assertTrue($team->isLoaded());
        $this->assertFalse($team->isModified());

        // Test LookupByNumber
        $team = Model_Fields_Team::LookupByNumber($this->m_division, $this->m_teamNumber);
        $this->assertEquals($team->divisionId, $this->m_division->id);
        $this->assertEquals($team->teamNumber, $this->m_teamNumber);
        $this->assertEquals($team->name, $this->m_name);
        $this->assertEquals($team->m_division->name, $this->m_division->name);
        $this->assertTrue($team->isLoaded());
        $this->assertFalse($team->isModified());

        // Test LookupById
        $team = Model_Fields_Team::LookupById($id);
        $this->assertEquals($team->divisionId, $this->m_division->id);
        $this->assertEquals($team->teamNumber, $this->m_teamNumber);
        $this->assertEquals($team->name, $this->m_name);
        $this->assertEquals($team->m_division->name, $this->m_division->name);
        $this->assertTrue($team->isLoaded());
        $this->assertFalse($team->isModified());

        // Test modify, save and reload
        $team->name = 'Pink Flamingos';
        $team->setModified();
        $this->assertTrue($team->isModified());
        $team->saveModel();
        $team = Model_Fields_Team::LookupById($id);
        $this->assertEquals($team->name, 'Pink Flamingos');
        $this->assertTrue($team->isLoaded());
        $this->assertFalse($team->isModified());
    }
}