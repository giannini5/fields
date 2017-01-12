<?php

namespace DAG\Orm\Schedule;

require_once 'helper.php';


/**
 * @testSuite test FamilyGame ORM
 */
class FamilyGameOrmTest extends ORM_TestHelper
{
    protected function setUp()
    {
        $this->primeDatabase();
    }

    protected function tearDown()
    {
        $this->clearDatabase();
    }

    public function test_create()
    {
        $this->verifyExpectedAttributes($this->defaultFamilyGameOrm, $this->defaultFamilyOrm->id, $this->defaultGameOrm->id);
    }

    public function test_loadById()
    {
        $familyGameOrm = FamilyGameOrm::loadById($this->defaultFamilyGameOrm->id);
        $this->verifyExpectedAttributes($familyGameOrm, $this->defaultFamilyOrm->id, $this->defaultGameOrm->id);
    }

    public function test_loadByFamilyGame()
    {
        $familyGameOrm = FamilyGameOrm::loadByFamilyIdAndGameId($this->defaultFamilyOrm->id, $this->defaultGameOrm->id);
        $this->verifyExpectedAttributes($familyGameOrm, $this->defaultFamilyOrm->id, $this->defaultGameOrm->id);
    }

    public function test_loadByFamily()
    {
        $familyGameOrms = FamilyGameOrm::loadByFamilyId($this->defaultFamilyOrm->id);
        $this->assertEquals(1, count($familyGameOrms));
        $this->verifyExpectedAttributes($familyGameOrms[0], $this->defaultFamilyOrm->id, $this->defaultGameOrm->id);
    }

    public function test_loadByGame()
    {
        $familyGameOrms = FamilyGameOrm::loadByGameId($this->defaultGameOrm->id);
        $this->assertEquals(1, count($familyGameOrms));
        $this->verifyExpectedAttributes($familyGameOrms[0], $this->defaultFamilyOrm->id, $this->defaultGameOrm->id);
    }

    private function verifyExpectedAttributes($familyGameOrm, $divisionOrmId, $fieldOrmId)
    {
        $this->assertTrue($familyGameOrm->id > 0);
        $this->assertEquals($divisionOrmId, $familyGameOrm->familyId);
        $this->assertEquals($fieldOrmId,    $familyGameOrm->gameId);
    }
}