<?php

namespace DAG\Orm\Schedule;

require_once 'helper.php';


/**
 * @testSuite test GameDateReferee ORM
 */
class GameDateRefereeOrmTest extends ORM_TestHelper
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
        $this->verifyExpectedAttributes($this->defaultGameDateRefereeOrm);
    }

    public function test_loadById()
    {
        $gameDateRefereeOrm = GameDateRefereeOrm::loadById($this->defaultGameDateRefereeOrm->id);
        $this->verifyExpectedAttributes($gameDateRefereeOrm);
    }

    public function test_loadByGameDateReferee()
    {
        $gameDateRefereeOrm = GameDateRefereeOrm::loadByGameDateIdAndReferee($this->defaultGameDateOrm->id, $this->defaultRefereeOrm->id);
        $this->verifyExpectedAttributes($gameDateRefereeOrm);
    }

    public function test_loadByGameDate()
    {
        $gameDateRefereeOrms = GameDateRefereeOrm::loadByGameDateId($this->defaultGameDateOrm->id);
        $this->assertEquals(1, count($gameDateRefereeOrms));
        $this->verifyExpectedAttributes($gameDateRefereeOrms[0]);
    }

    public function test_loadByReferee()
    {
        $gameDateRefereeOrms = GameDateRefereeOrm::loadByRefereeId($this->defaultRefereeOrm->id);
        $this->assertEquals(1, count($gameDateRefereeOrms));
        $this->verifyExpectedAttributes($gameDateRefereeOrms[0]);
    }

    private function verifyExpectedAttributes($gameDateRefereeOrm)
    {
        $this->assertTrue($gameDateRefereeOrm->id > 0);
        $this->assertEquals($this->defaultGameDateRefereeOrm->gameDateId, $gameDateRefereeOrm->gameDateId);
        $this->assertEquals($this->defaultGameDateRefereeOrm->refereeId,  $gameDateRefereeOrm->refereeId);
    }
}