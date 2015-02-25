<?php
require_once '../../../autoLoader.php';

class Model_ManagerTest extends PHPUnit_Framework_TestCase {

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
        $manager = Model_Fields_Manager::Create($this->m_team, $this->m_name, $this->m_email, $this->m_phone, $this->m_password);
        $id = $manager->id;
        $this->assertEquals($manager->teamId, $this->m_team->id);
        $this->assertEquals($manager->name, $this->m_name);
        $this->assertEquals($manager->email, $this->m_email);
        $this->assertEquals($manager->phone, $this->m_phone);
        $this->assertEquals($manager->password, $this->m_password);
        $this->assertEquals($manager->m_team->name, $this->m_team->name);
        $this->assertTrue($manager->isLoaded());
        $this->assertFalse($manager->isModified());

        // Test LookupByEmail
        $manager = Model_Fields_Manager::LookupByEmail($this->m_team, $this->m_email);
        $this->assertEquals($manager->teamId, $this->m_team->id);
        $this->assertEquals($manager->name, $this->m_name);
        $this->assertEquals($manager->email, $this->m_email);
        $this->assertEquals($manager->phone, $this->m_phone);
        $this->assertEquals($manager->password, $this->m_password);
        $this->assertEquals($manager->m_team->name, $this->m_team->name);
        $this->assertTrue($manager->isLoaded());
        $this->assertFalse($manager->isModified());

        // Test LookupById
        $manager = Model_Fields_Manager::LookupById($id);
        $this->assertEquals($manager->teamId, $this->m_team->id);
        $this->assertEquals($manager->name, $this->m_name);
        $this->assertEquals($manager->email, $this->m_email);
        $this->assertEquals($manager->phone, $this->m_phone);
        $this->assertEquals($manager->password, $this->m_password);
        $this->assertEquals($manager->m_team->name, $this->m_team->name);
        $this->assertTrue($manager->isLoaded());
        $this->assertFalse($manager->isModified());

        // Test modify, save and reload
        $manager->name = 'Dolores Giannini';
        $manager->setModified();
        $this->assertTrue($manager->isModified());
        $manager->saveModel();
        $manager = Model_Fields_Manager::LookupById($id);
        $this->assertEquals($manager->name, 'Dolores Giannini');
        $this->assertTrue($manager->isLoaded());
        $this->assertFalse($manager->isModified());
    }
}