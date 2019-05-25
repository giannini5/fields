<?php

namespace DAG\Domain\Schedule;

use DAG\Orm\Schedule\ORM_TestHelper;

require_once dirname(dirname(dirname(__DIR__))) . '/Orm/Schedule/tests/helper.php';



/**
 * @testSuite test GameDateReferee
 */
class GameDateRefereeTest extends ORM_TestHelper
{
    public $gameDate;
    public $referee;

    protected function setUp()
    {
        $this->primeDatabase();

        $this->gameDate = GameDate::lookupById($this->defaultGameDateOrm->id);
        $this->referee  = Referee::lookupById($this->defaultRefereeOrm->id);
    }

    protected function tearDown()
    {
        $this->clearDatabase();
    }

    public function test_lookupById()
    {
        $gameDateReferee = GameDateReferee::lookupById($this->defaultGameDateRefereeOrm->id);
        $this->validateGameDateReferee($gameDateReferee, $this->gameDate, $this->referee);
    }

    public function test_lookupByGameDate()
    {
        $gameDateReferees = GameDateReferee::lookupByGameDate($this->gameDate);
        $this->assertEquals(1, count($gameDateReferees));
        $this->validateGameDateReferee($gameDateReferees[0], $this->gameDate, $this->referee);
    }

    public function test_lookupByReferee()
    {
        $gameDateReferees = GameDateReferee::lookupByReferee($this->referee);
        $this->assertEquals(1, count($gameDateReferees));
        $this->validateGameDateReferee($gameDateReferees[0], $this->gameDate, $this->referee);
    }

    public function test_lookupByGameDateAndReferee()
    {
        $gameDateReferee = GameDateReferee::lookupByGameDateAndReferee($this->gameDate, $this->referee);
        $this->validateGameDateReferee($gameDateReferee, $this->gameDate, $this->referee);
    }

    public function test_findByGameDateAndReferee()
    {
        $gameDateReferee = null;
        $result = GameDateReferee::findByGameDateAndReferee($this->gameDate, $this->referee, $gameDateReferee);
        $this->assertTrue($result, "findByGameDateAndReferee returned false");
        $this->validateGameDateReferee($gameDateReferee, $this->gameDate, $this->referee);
    }

    public function validateGameDateReferee($gameDateReferee, $gameDate, $referee)
    {
        $this->assertTrue($gameDateReferee->id > 0);
        $this->assertEquals($gameDate,  $gameDateReferee->gameDate);
        $this->assertEquals($referee,   $gameDateReferee->referee);
    }
}