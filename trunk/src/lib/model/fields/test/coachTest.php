<?php
require_once '../../../autoLoader.php';

class Model_CoachTest extends PHPUnit_Framework_TestCase {

    public $m_league;
    public $m_division;
    public $m_team;

    public $m_leagueName = 'Test AYSO Region 122';
    public $m_divisionName = 'U10G';
    public $m_teamNumber = 25;

    public $m_name = 'David Giannini';
    public $m_email = 'david_giannini@hotmail.com';
    public $m_phone = '8058989551';
    public $m_password = 'hello mom';

    /**
     * Prepare to run test
     */
    protected function setUp()
    {
        $m_league = NULL;
        $m_division = NULL;
        $m_team = NULL;

        $this->m_league = Model_Fields_League::LookupByName($this->m_leagueName, FALSE);
        if (isset($this->m_league)) {
            $this->m_division = Model_Fields_Division::LookupByName($this->m_league, $this->m_divisionName, FALSE);

            if (isset($this->m_division)) {
                $this->m_team = Model_Fields_Team::LookupByNumber($this->m_division, $this->m_teamNumber, FALSE);
            }

            if (isset($this->m_team)) {
                Model_Fields_Team::Delete($this->m_division, $this->m_teamNumber);
            }
            if (isset($this->m_division)) {
                Model_Fields_Division::Delete($this->m_league, $this->m_divisionName);
            }

            Model_Fields_League::Delete($this->m_leagueName);
        }

        $this->m_league = Model_Fields_League::Create($this->m_leagueName);
        $this->m_division = Model_Fields_Division::Create($this->m_league, $this->m_divisionName, 1);
        $this->m_team = Model_Fields_Team::Create($this->m_division, $this->m_teamNumber, 'Thunder');
    }

    /**
     * Default Construction
     */
    public function testStaticMethods()
    {
        // Test Create
        $coach = Model_Fields_Coach::Create($this->m_team, $this->m_name, $this->m_email, $this->m_phone, $this->m_password);
        $id = $coach->id;
        $this->assertEquals($coach->teamId, $this->m_team->id);
        $this->assertEquals($coach->name, $this->m_name);
        $this->assertEquals($coach->email, $this->m_email);
        $this->assertEquals($coach->phone, $this->m_phone);
        $this->assertEquals($coach->password, $this->m_password);
        $this->assertEquals($coach->m_team->name, $this->m_team->name);
        $this->assertTrue($coach->isLoaded());
        $this->assertFalse($coach->isModified());

        // Test LookupByEmail
        $coach = Model_Fields_Coach::LookupByEmail($this->m_team, $this->m_email);
        $this->assertEquals($coach->teamId, $this->m_team->id);
        $this->assertEquals($coach->name, $this->m_name);
        $this->assertEquals($coach->email, $this->m_email);
        $this->assertEquals($coach->phone, $this->m_phone);
        $this->assertEquals($coach->password, $this->m_password);
        $this->assertEquals($coach->m_team->name, $this->m_team->name);
        $this->assertTrue($coach->isLoaded());
        $this->assertFalse($coach->isModified());

        // Test LookupByTeam
        $coach = Model_Fields_Coach::LookupByTeam($this->m_team);
        $this->assertEquals($coach->teamId, $this->m_team->id);
        $this->assertEquals($coach->name, $this->m_name);
        $this->assertEquals($coach->email, $this->m_email);
        $this->assertEquals($coach->phone, $this->m_phone);
        $this->assertEquals($coach->password, $this->m_password);
        $this->assertEquals($coach->m_team->name, $this->m_team->name);
        $this->assertTrue($coach->isLoaded());
        $this->assertFalse($coach->isModified());

        // Test LookupById
        $coach = Model_Fields_Coach::LookupById($id);
        $this->assertEquals($coach->teamId, $this->m_team->id);
        $this->assertEquals($coach->name, $this->m_name);
        $this->assertEquals($coach->email, $this->m_email);
        $this->assertEquals($coach->phone, $this->m_phone);
        $this->assertEquals($coach->password, $this->m_password);
        $this->assertEquals($coach->m_team->name, $this->m_team->name);
        $this->assertTrue($coach->isLoaded());
        $this->assertFalse($coach->isModified());

        // Test modify, save and reload
        $coach->name = 'Dolores Giannini';
        $coach->setModified();
        $this->assertTrue($coach->isModified());
        $coach->saveModel();
        $coach = Model_Fields_Coach::LookupById($id);
        $this->assertEquals($coach->name, 'Dolores Giannini');
        $this->assertTrue($coach->isLoaded());
        $this->assertFalse($coach->isModified());
    }
}