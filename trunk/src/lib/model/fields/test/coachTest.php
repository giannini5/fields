<?php
require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/autoload.php';
require_once 'helper.php';

class Model_CoachTest extends Model_TestHelpers {
    public $m_name = 'David Giannini';
    public $m_email = 'david_giannini@hotmail.com';
    public $m_phone = '8058989551';
    public $m_password = 'hello mom';

    /**
     * Prepare to run test
     */
    protected function setUp()
    {
        $this->primeDatabase(TRUE, TRUE, TRUE, FALSE, FALSE);

        $coach = Model_Fields_Coach::LookupByEmail($this->m_season, $this->m_division, $this->m_email, FALSE);
        if (isset($coach)) {
            $coach->_delete();
        }
    }

    /**
     * Default Construction
     */
    public function testStaticMethods()
    {
        // Test Create
        $coach = Model_Fields_Coach::Create($this->m_season, $this->m_division, $this->m_name, $this->m_email, $this->m_phone, $this->m_password);
        $id = $coach->id;
        $this->assertEquals($coach->seasonId, $this->m_season->id);
        $this->assertEquals($coach->divisionId, $this->m_division->id);
        $this->assertEquals($coach->name, $this->m_name);
        $this->assertEquals($coach->email, $this->m_email);
        $this->assertEquals($coach->phone, $this->m_phone);
        $this->assertEquals($coach->password, $this->m_password);
        $this->assertTrue($coach->isLoaded());
        $this->assertFalse($coach->isModified());

        // Test LookupByEmail
        $coach = Model_Fields_Coach::LookupByEmail($this->m_season, $this->m_division, $this->m_email);
        $this->assertEquals($coach->seasonId, $this->m_season->id);
        $this->assertEquals($coach->divisionId, $this->m_division->id);
        $this->assertEquals($coach->name, $this->m_name);
        $this->assertEquals($coach->email, $this->m_email);
        $this->assertEquals($coach->phone, $this->m_phone);
        $this->assertEquals($coach->password, $this->m_password);
        $this->assertTrue($coach->isLoaded());
        $this->assertFalse($coach->isModified());

        // Test LookupById
        $coach = Model_Fields_Coach::LookupById($id);
        $this->assertEquals($coach->seasonId, $this->m_season->id);
        $this->assertEquals($coach->divisionId, $this->m_division->id);
        $this->assertEquals($coach->name, $this->m_name);
        $this->assertEquals($coach->email, $this->m_email);
        $this->assertEquals($coach->phone, $this->m_phone);
        $this->assertEquals($coach->password, $this->m_password);
        $this->assertTrue($coach->isLoaded());
        $this->assertFalse($coach->isModified());

        // Test modify, save and reload
        $coach->name = 'Dolores Giannini';
        $coach->setModified();
        $this->assertTrue($coach->isModified());
        $coach->setModified();
        $coach->saveModel();
        $coach = Model_Fields_Coach::LookupById($id);
        $this->assertEquals($coach->name, 'Dolores Giannini');
        $this->assertTrue($coach->isLoaded());
        $this->assertFalse($coach->isModified());
    }
}