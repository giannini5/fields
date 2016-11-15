<?php
require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/autoload.php';
require_once 'helper.php';

class Model_ManagerTest extends Model_TestHelpers {
    public $m_name = 'David Giannini';
    public $m_email = 'david_giannini@hotmail.com';
    public $m_phone = '8058989551';
    public $m_password = 'hello mom';

    /**
     * Prepare to run test
     */
    protected function setUp()
    {
        $this->primeDatabase();
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