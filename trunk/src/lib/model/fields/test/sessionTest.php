<?php
require_once '../../../autoLoader.php';

class Model_SessionTest extends PHPUnit_Framework_TestCase {

    public $m_userId = 12345;
    public $m_userType = 0;

    /**
     * Prepare to run test
     */
    protected function setUp()
    {
        $session = Model_Fields_Session::LookupByUser($this->m_userId, $this->m_userType, FALSE);
        if (isset($session)) {
            $session->_delete();
        }

        $session = Model_Fields_Session::LookupByUser($this->m_userId, 1, FALSE);
        if (isset($session)) {
            $session->_delete();
        }
    }

    /**
     * Default Construction
     */
    public function testStaticMethods()
    {
        // Test Create
        $session = Model_Fields_Session::Create($this->m_userId, $this->m_userType);
        $id = $session->id;
        $this->assertEquals($session->userId, $this->m_userId);
        $this->assertEquals($session->userType, $this->m_userType);
        $this->assertTrue($session->isLoaded());
        $this->assertFalse($session->isModified());

        // Test LookupByUser
        $session = Model_Fields_Session::LookupByUser($this->m_userId, $this->m_userType);
        $this->assertEquals($session->id, $id);
        $this->assertEquals($session->userId, $this->m_userId);
        $this->assertEquals($session->userType, $this->m_userType);
        $this->assertTrue($session->isLoaded());
        $this->assertFalse($session->isModified());

        // Test LookupById
        $session = Model_Fields_Session::LookupById($id);
        $this->assertEquals($session->id, $id);
        $this->assertEquals($session->userId, $this->m_userId);
        $this->assertEquals($session->userType, $this->m_userType);
        $this->assertTrue($session->isLoaded());
        $this->assertFalse($session->isModified());

        // Test modify, save and reload
        $session->userType = 1;
        $session->setModified();
        $this->assertTrue($session->isModified());
        $session->saveModel();
        $session = Model_Fields_Session::LookupById($id);
        $this->assertEquals($session->userType, 1);
        $this->assertTrue($session->isLoaded());
        $this->assertFalse($session->isModified());
    }
}