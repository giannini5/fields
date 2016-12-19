<?php

namespace DAG\Domain\Schedule;

use DAG\Orm\Schedule\ORM_TestHelper;

require_once dirname(dirname(dirname(__DIR__))) . '/Orm/Schedule/tests/helper.php';



/**
 * @testSuite test DivisionField
 */
class DivisionFieldTest extends ORM_TestHelper
{
    public $division;
    public $field;

    protected function setUp()
    {
        $this->primeDatabase();

        $this->division = Division::lookupById($this->defaultDivisionOrm->id);
        $this->field    = Field::lookupById($this->defaultFieldOrm->id);
    }

    protected function tearDown()
    {
        $this->clearDatabase();
    }

    public function test_lookupById()
    {
        $divisionField = DivisionField::lookupById($this->defaultDivisionFieldOrm->id);
        $this->validateDivisionField($divisionField, $this->division, $this->field);
    }

    public function test_lookupByDivision()
    {
        $divisionFields = DivisionField::lookupByDivision($this->division);
        $this->assertEquals(1, count($divisionFields));
        $this->validateDivisionField($divisionFields[0], $this->division, $this->field);
    }

    public function test_lookupByField()
    {
        $divisionFields = DivisionField::lookupByField($this->field);
        $this->assertEquals(1, count($divisionFields));
        $this->validateDivisionField($divisionFields[0], $this->division, $this->field);
    }

    public function test_lookupByDivisionAndField()
    {
        $divisionField = DivisionField::lookupByDivisionAndField($this->division, $this->field);
        $this->validateDivisionField($divisionField, $this->division, $this->field);
    }

    public function validateDivisionField($divisionField, $division, $field)
    {
        $this->assertTrue($divisionField->id > 0);
        $this->assertEquals($division,  $divisionField->division);
        $this->assertEquals($field,  $divisionField->field);
    }
}