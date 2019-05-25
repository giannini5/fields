<?php

namespace DAG\Domain\Schedule;

use DAG\Orm\Schedule\ORM_TestHelper;

require_once dirname(dirname(dirname(__DIR__))) . '/Orm/Schedule/tests/helper.php';



/**
 * @testSuite test StandbyReferee
 */
class StandbyRefereeTest extends ORM_TestHelper
{
    /** @var  Facility */
    public $facility;
    /** @var  GameDate */
    public $gameDate;
    /** @var  Referee */
    public $referee;
    /** @var RefereeCrew */
    public $refereeCrew;

    protected function setUp()
    {
        $this->primeDatabase();

        $this->facility     = Facility::lookupById($this->defaultFacilityOrm->id);
        $this->gameDate     = GameDate::lookupById($this->defaultGameDateOrm->id);
        $this->referee      = Referee::lookupById($this->defaultRefereeOrm->id);
        $this->refereeCrew  = RefereeCrew::lookupById($this->defaultRefereeCrewOrm->id);
    }

    protected function tearDown()
    {
        $this->clearDatabase();
    }

    public function test_lookupById()
    {
        $standbyReferee = StandbyReferee::lookupById($this->defaultStandbyRefereeOrm->id);
        $this->validateStandbyReferee($standbyReferee);
    }

    public function test_lookupByStartTime()
    {
        $standbyReferees = StandbyReferee::lookupByStartTime(
            $this->facility,
            $this->gameDate,
            self::$defaultStandbyRefereeOrmAttributes[self::DIVISION_NAME],
            self::$defaultStandbyRefereeOrmAttributes[self::START_TIME]);
        $this->assertEquals(1, count($standbyReferees));
        $this->validateStandbyReferee($standbyReferees[0]);
    }

    public function test_findByStartTimeAndReferee()
    {
        $standbyReferee = null;
        $result = StandbyReferee::findByStartTimeReferee(
            $this->facility,
            $this->gameDate,
            self::$defaultStandbyRefereeOrmAttributes[self::DIVISION_NAME],
            self::$defaultStandbyRefereeOrmAttributes[self::START_TIME],
            $this->referee,
            $standbyReferee);
        $this->assertTrue($result, "findByGameAndReferee returned false");
        $this->validateStandbyReferee($standbyReferee);
    }

    public function test_findByStartTimeAndRefereeNotFound()
    {
        $referee = Referee::lookupById($this->defaultCenterRefereeOrm->id);

        $standbyReferee = null;
        $result = StandbyReferee::findByStartTimeReferee(
            $this->facility,
            $this->gameDate,
            self::$defaultStandbyRefereeOrmAttributes[self::DIVISION_NAME],
            self::$defaultStandbyRefereeOrmAttributes[self::START_TIME],
            $referee,
            $standbyReferee);
        $this->assertFalse($result, "findByGameAndReferee returned true");
    }

    public function test_lookupByReferee()
    {
        $standbyReferees = StandbyReferee::lookupByReferee(
            $this->facility,
            $this->gameDate,
            $this->referee);
        $this->assertEquals(1, count($standbyReferees));
        $this->validateStandbyReferee($standbyReferees[0]);
    }

    public function validateStandbyReferee($standbyReferee, $refereeCrew = null)
    {
        $this->assertTrue($standbyReferee->id > 0);
        $this->assertEquals($this->facility->id,                                            $standbyReferee->facility->id);
        $this->assertEquals($this->gameDate->id,                                            $standbyReferee->gameDate->id);
        $this->assertEquals($this->referee->id,                                             $standbyReferee->referee->id);
        $this->assertEquals(self::$defaultStandbyRefereeOrmAttributes[self::DIVISION_NAME], $standbyReferee->divisionName);
        $this->assertEquals(self::$defaultStandbyRefereeOrmAttributes[self::START_TIME],    $standbyReferee->startTime);
        $this->assertEquals(self::$defaultStandbyRefereeOrmAttributes[self::REFEREE_ROLE],  $standbyReferee->role);

        if (isset($refereeCrew)) {
            $this->assertEquals($this->refereeCrew->id, $standbyReferee->refereeCrew->id);
        } else {
            $this->assertNull($standbyReferee->refereeCrew);
        }
    }
}