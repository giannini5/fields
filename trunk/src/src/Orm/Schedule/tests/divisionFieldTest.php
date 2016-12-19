<?php

namespace DAG\Orm\Schedule;

require_once 'helper.php';


/**
 * @testSuite test DivisionField ORM
 */
class DivisionFieldOrmTest extends ORM_TestHelper
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
        $this->verifyExpectedAttributes($this->defaultDivisionFieldOrm, $this->defaultDivisionOrm->id, $this->defaultFieldOrm->id);
    }

    public function test_loadById()
    {
        $divisionFieldOrm = DivisionFieldOrm::loadById($this->defaultDivisionFieldOrm->id);
        $this->verifyExpectedAttributes($divisionFieldOrm, $this->defaultDivisionOrm->id, $this->defaultFieldOrm->id);
    }

    public function test_loadByDivisionField()
    {
        $divisionFieldOrm = DivisionFieldOrm::loadByDivisionIdAndField($this->defaultDivisionOrm->id, $this->defaultFieldOrm->id);
        $this->verifyExpectedAttributes($divisionFieldOrm, $this->defaultDivisionOrm->id, $this->defaultFieldOrm->id);
    }

    public function test_loadByDivision()
    {
        $divisionFieldOrms = DivisionFieldOrm::loadByDivisionId($this->defaultDivisionOrm->id);
        $this->assertEquals(1, count($divisionFieldOrms));
        $this->verifyExpectedAttributes($divisionFieldOrms[0], $this->defaultDivisionOrm->id, $this->defaultFieldOrm->id);
    }

    public function test_loadByField()
    {
        $divisionFieldOrms = DivisionFieldOrm::loadByFieldId($this->defaultFieldOrm->id);
        $this->assertEquals(1, count($divisionFieldOrms));
        $this->verifyExpectedAttributes($divisionFieldOrms[0], $this->defaultDivisionOrm->id, $this->defaultFieldOrm->id);
    }

    private function verifyExpectedAttributes($divisionFieldOrm, $divisionOrmId, $fieldOrmId)
    {
        $this->assertTrue($divisionFieldOrm->id > 0);
        $this->assertEquals($divisionOrmId, $divisionFieldOrm->divisionId);
        $this->assertEquals($fieldOrmId,    $divisionFieldOrm->fieldId);
    }
}