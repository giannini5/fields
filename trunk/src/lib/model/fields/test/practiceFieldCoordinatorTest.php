<?php
require_once '../../../autoLoader.php';

class Model_PracticeFieldCoordinatorTest extends PHPUnit_Framework_TestCase {

    public $m_leagueName = 'Test AYSO Region 122';
    public $m_league;
    public $m_email = 'david_giannini@hotmail.com';
    public $m_name = 'David Giannini';
    public $m_password = 'Be 3arefu1';

    /**
     * Prepare to run test
     */
    protected function setUp()
    {
        $this->m_league = Model_Fields_League::LookupByName($this->m_leagueName, FALSE);
        if (isset($this->m_league)) {
            Model_Fields_PracticeFieldCoordinator::Delete($this->m_league, $this->m_email);
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
        $practiceFieldCoordinator = Model_Fields_PracticeFieldCoordinator::Create($this->m_league, $this->m_email, $this->m_name, $this->m_password);
        $id = $practiceFieldCoordinator->id;
        $this->assertEquals($practiceFieldCoordinator->leagueId, $this->m_league->id);
        $this->assertEquals($practiceFieldCoordinator->email, $this->m_email);
        $this->assertEquals($practiceFieldCoordinator->name, $this->m_name);
        $this->assertEquals($practiceFieldCoordinator->password, $this->m_password);
        $this->assertEquals($practiceFieldCoordinator->m_league->name, $this->m_league->name);
        $this->assertTrue($practiceFieldCoordinator->isLoaded());
        $this->assertFalse($practiceFieldCoordinator->isModified());

        // Test LookupByEmail
        $practiceFieldCoordinator = Model_Fields_PracticeFieldCoordinator::LookupByEmail($this->m_league, $this->m_email);
        $this->assertEquals($practiceFieldCoordinator->leagueId, $this->m_league->id);
        $this->assertEquals($practiceFieldCoordinator->email, $this->m_email);
        $this->assertEquals($practiceFieldCoordinator->name, $this->m_name);
        $this->assertEquals($practiceFieldCoordinator->password, $this->m_password);
        $this->assertEquals($practiceFieldCoordinator->m_league->name, $this->m_league->name);
        $this->assertTrue($practiceFieldCoordinator->isLoaded());
        $this->assertFalse($practiceFieldCoordinator->isModified());

        // Test LookupById
        $practiceFieldCoordinator = Model_Fields_PracticeFieldCoordinator::LookupById($id);
        $this->assertEquals($practiceFieldCoordinator->leagueId, $this->m_league->id);
        $this->assertEquals($practiceFieldCoordinator->email, $this->m_email);
        $this->assertEquals($practiceFieldCoordinator->name, $this->m_name);
        $this->assertEquals($practiceFieldCoordinator->password, $this->m_password);
        $this->assertEquals($practiceFieldCoordinator->m_league->name, $this->m_league->name);
        $this->assertTrue($practiceFieldCoordinator->isLoaded());
        $this->assertFalse($practiceFieldCoordinator->isModified());

        // Test modify, save and reload
        $practiceFieldCoordinator->name = 'My New Name';
        $practiceFieldCoordinator->setModified();
        $this->assertTrue($practiceFieldCoordinator->isModified());
        $practiceFieldCoordinator->saveModel();
        $practiceFieldCoordinator = Model_Fields_PracticeFieldCoordinator::LookupById($id);
        $this->assertEquals($practiceFieldCoordinator->name, 'My New Name');
        $this->assertTrue($practiceFieldCoordinator->isLoaded());
        $this->assertFalse($practiceFieldCoordinator->isModified());
    }

}