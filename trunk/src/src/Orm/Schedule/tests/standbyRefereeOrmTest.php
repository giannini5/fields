<?php

namespace DAG\Orm\Schedule;

require_once 'helper.php';


/**
 * @testSuite test StandbyReferee ORM
 */
class StandbyRefereeOrmTest extends ORM_TestHelper
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
        $this->verifyExpectedAttributes($this->defaultStandbyRefereeOrm);
    }

    public function test_loadById()
    {
        $standbyRefereeOrm = StandbyRefereeOrm::loadById($this->defaultStandbyRefereeOrm->id);
        $this->verifyExpectedAttributes($standbyRefereeOrm);
    }

    public function test_loadByStandbyReferee()
    {
        $standbyRefereeOrm = StandbyRefereeOrm::loadByFacilityIdGameDateIdDivisionNameStartTimeRefereeId(
            $this->defaultFacilityOrm->id,
            $this->defaultGameDateOrm->id,
            self::$defaultStandbyRefereeOrmAttributes[self::DIVISION_NAME],
            self::$defaultStandbyRefereeOrmAttributes[self::START_TIME],
            $this->defaultRefereeOrm->id);
        $this->verifyExpectedAttributes($standbyRefereeOrm);
    }

    public function test_loadByStartTime()
    {
        $standbyRefereeOrms = StandbyRefereeOrm::loadByFacilityIdGameDateIdDivisionNameStartTime(
            $this->defaultFacilityOrm->id,
            $this->defaultGameDateOrm->id,
            self::$defaultStandbyRefereeOrmAttributes[self::DIVISION_NAME],
            self::$defaultStandbyRefereeOrmAttributes[self::START_TIME]);
        $this->assertEquals(1, count($standbyRefereeOrms));
        $this->verifyExpectedAttributes($standbyRefereeOrms[0]);
    }

    public function test_loadByGameDateReferee()
    {
        $standbyRefereeOrms = StandbyRefereeOrm::loadByGameDateIdRefereeId(
            $this->defaultFacilityOrm->id,
            $this->defaultGameDateOrm->id,
            $this->defaultRefereeOrm->id);
        $this->assertEquals(1, count($standbyRefereeOrms));
        $this->verifyExpectedAttributes($standbyRefereeOrms[0]);
    }

    private function verifyExpectedAttributes($standbyRefereeOrm)
    {
        $this->assertTrue($standbyRefereeOrm->id > 0);
        $this->assertEquals($this->defaultFacilityOrm->id,                                  $standbyRefereeOrm->facilityId);
        $this->assertEquals($this->defaultGameDateOrm->id,                                  $standbyRefereeOrm->gameDateId);
        $this->assertEquals(self::$defaultStandbyRefereeOrmAttributes[self::DIVISION_NAME], $standbyRefereeOrm->divisionName);
        $this->assertEquals(self::$defaultStandbyRefereeOrmAttributes[self::START_TIME],    $standbyRefereeOrm->startTime);
        $this->assertEquals($this->defaultRefereeOrm->id,                                   $standbyRefereeOrm->refereeId);
        $this->assertEquals(self::$defaultStandbyRefereeOrmAttributes[self::REFEREE_ROLE],  $standbyRefereeOrm->role);
    }
}