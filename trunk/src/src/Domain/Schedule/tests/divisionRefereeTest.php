<?php

namespace DAG\Domain\Schedule;

use DAG\Orm\Schedule\ORM_TestHelper;

require_once dirname(dirname(dirname(__DIR__))) . '/Orm/Schedule/tests/helper.php';



/**
 * @testSuite test DivisionReferee
 */
class DivisionRefereeTest extends ORM_TestHelper
{
    public $division;
    public $referee;

    protected function setUp()
    {
        $this->primeDatabase();

        $this->division = Division::lookupById($this->defaultDivisionOrm->id);
        $this->referee  = Referee::lookupById($this->defaultRefereeOrm->id);
    }

    protected function tearDown()
    {
        $this->clearDatabase();
    }

    public function test_lookupById()
    {
        $divisionReferee = DivisionReferee::lookupById($this->defaultDivisionRefereeOrm->id);
        $this->validateDivisionReferee($divisionReferee, $this->division, $this->referee);
    }

    public function test_lookupByDivision()
    {
        $divisionReferees = DivisionReferee::lookupByDivision($this->division);
        $this->assertEquals(1, count($divisionReferees));
        $this->validateDivisionReferee($divisionReferees[0], $this->division, $this->referee);
    }

    public function test_lookupByReferee()
    {
        $divisionReferees = DivisionReferee::lookupByReferee($this->referee);
        $this->assertEquals(1, count($divisionReferees));
        $this->validateDivisionReferee($divisionReferees[0], $this->division, $this->referee);
    }

    public function test_lookupByDivisionAndReferee()
    {
        $divisionReferee = DivisionReferee::lookupByDivisionAndReferee($this->division, $this->referee);
        $this->validateDivisionReferee($divisionReferee, $this->division, $this->referee);
    }

    public function test_findByDivisionAndReferee()
    {
        $divisionReferee = null;
        $result = DivisionReferee::findByDivisionAndReferee($this->division, $this->referee, $divisionReferee);
        $this->assertTrue($result, "findByDivisionAndReferee returned false");
        $this->validateDivisionReferee($divisionReferee, $this->division, $this->referee);
    }

    public function test_set()
    {
        $divisionReferee = DivisionReferee::lookupByDivisionAndReferee($this->division, $this->referee);
        $divisionReferee->isCenter      = true;
        $divisionReferee->isAssistant   = false;
        $divisionReferee->isMentor      = false;

        $divisionReferee = DivisionReferee::lookupByDivisionAndReferee($this->division, $this->referee);
        $this->assertTrue($divisionReferee->isCenter);
        $this->assertFalse($divisionReferee->isAssistant);
        $this->assertFalse($divisionReferee->isMentor);
    }

    public function validateDivisionReferee($divisionReferee, $division, $referee)
    {
        $this->assertTrue($divisionReferee->id > 0);
        $this->assertEquals($division,  $divisionReferee->division);
        $this->assertEquals($referee,   $divisionReferee->referee);
        $this->assertFalse($divisionReferee->isCenter);
        $this->assertTrue($divisionReferee->isAssistant);
        $this->assertTrue($divisionReferee->isMentor);
    }
}