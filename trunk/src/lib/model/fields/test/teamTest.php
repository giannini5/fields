<?php
require_once '../../../autoLoader.php';
require_once 'helper.php';

class Model_TeamTest extends Model_TestHelpers {

    public $m_name = 'Flying Hawks';

    /**
     * Prepare to run test
     */
    protected function setUp()
    {
        $this->primeDatabase();

        $team = Model_Fields_Team::LookupByCoach($this->m_coach, $this->m_gender, FALSE);
        if (isset($team)) {
            $team->_delete();
        }
    }

    /**
     * Default Construction
     */
    public function testStaticMethods()
    {
        // Test Create
        $team = Model_Fields_Team::Create($this->m_division, $this->m_coach, $this->m_gender, $this->m_name);
        $id = $team->id;
        $this->assertEquals($team->divisionId, $this->m_division->id);
        $this->assertEquals($team->coachId, $this->m_coach->id);
        $this->assertEquals($team->gender, $this->m_gender);
        $this->assertEquals($team->name, $this->m_name);
        $this->assertEquals($team->m_division->name, $this->m_division->name);
        $this->assertEquals($team->m_coach->name, $this->m_coach->name);
        $this->assertTrue($team->isLoaded());
        $this->assertFalse($team->isModified());

        // Test LookupById
        $team = Model_Fields_Team::LookupById($id);
        $this->assertEquals($team->divisionId, $this->m_division->id);
        $this->assertEquals($team->coachId, $this->m_coach->id);
        $this->assertEquals($team->gender, $this->m_gender);
        $this->assertEquals($team->name, $this->m_name);
        $this->assertEquals($team->m_division->name, $this->m_division->name);
        $this->assertEquals($team->m_coach->name, $this->m_coach->name);
        $this->assertTrue($team->isLoaded());
        $this->assertFalse($team->isModified());

        // Test LookupByCoach
        $team = Model_Fields_Team::LookupByCoach($this->m_coach, $this->m_gender);
        $this->assertEquals($team->divisionId, $this->m_division->id);
        $this->assertEquals($team->coachId, $this->m_coach->id);
        $this->assertEquals($team->gender, $this->m_gender);
        $this->assertEquals($team->name, $this->m_name);
        $this->assertEquals($team->m_division->name, $this->m_division->name);
        $this->assertEquals($team->m_coach->name, $this->m_coach->name);
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