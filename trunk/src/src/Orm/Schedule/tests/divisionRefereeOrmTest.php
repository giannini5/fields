<?php

namespace DAG\Orm\Schedule;

require_once 'helper.php';


/**
 * @testSuite test DivisionReferee ORM
 */
class DivisionRefereeOrmTest extends ORM_TestHelper
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
        $this->verifyExpectedAttributes($this->defaultDivisionRefereeOrm);
    }

    public function test_loadById()
    {
        $divisionRefereeOrm = DivisionRefereeOrm::loadById($this->defaultDivisionRefereeOrm->id);
        $this->verifyExpectedAttributes($divisionRefereeOrm);
    }

    public function test_loadByDivisionReferee()
    {
        $divisionRefereeOrm = DivisionRefereeOrm::loadByDivisionIdAndReferee($this->defaultDivisionOrm->id, $this->defaultRefereeOrm->id);
        $this->verifyExpectedAttributes($divisionRefereeOrm);
    }

    public function test_loadByDivision()
    {
        $divisionRefereeOrms = DivisionRefereeOrm::loadByDivisionId($this->defaultDivisionOrm->id);
        $this->assertEquals(1, count($divisionRefereeOrms));
        $this->verifyExpectedAttributes($divisionRefereeOrms[0]);
    }

    public function test_loadByReferee()
    {
        $divisionRefereeOrms = DivisionRefereeOrm::loadByRefereeId($this->defaultRefereeOrm->id);
        $this->assertEquals(1, count($divisionRefereeOrms));
        $this->verifyExpectedAttributes($divisionRefereeOrms[0]);
    }

    private function verifyExpectedAttributes($divisionRefereeOrm)
    {
        $this->assertTrue($divisionRefereeOrm->id > 0);
        $this->assertEquals($this->defaultDivisionRefereeOrm->divisionId, $divisionRefereeOrm->divisionId);
        $this->assertEquals($this->defaultDivisionRefereeOrm->refereeId,  $divisionRefereeOrm->refereeId);
        $this->assertEquals(self::$defaultDivisionRefereeOrmAttributes[self::IS_CENTER], $divisionRefereeOrm->isCenter);
        $this->assertEquals(self::$defaultDivisionRefereeOrmAttributes[self::IS_ASSISTANT], $divisionRefereeOrm->isAssistant);
        $this->assertEquals(self::$defaultDivisionRefereeOrmAttributes[self::IS_MENTOR], $divisionRefereeOrm->isMentor);
    }
}