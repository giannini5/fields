<?php

namespace DAG\Orm\Schedule;

require_once 'helper.php';


/**
 * @testSuite test ScheduleCoordinator ORM
 */
class ScheduleCoordinatorOrmTest extends ORM_TestHelper
{
    /**
     * Expected values on create and load
     */
    protected static $expectedDefaults = array(
        'email'         => 'TEST scheduleCoordinator email',
        'name'          => 'TEST scheduleCoordinator name',
        'password'      => 'TEST pwd');

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
        $scheduleCoordinatorOrm = ScheduleCoordinatorOrm::create(
            $this->defaultLeagueOrm->id,
            self::$expectedDefaults[self::EMAIL],
            self::$expectedDefaults[self::NAME],
            self::$expectedDefaults[self::PASSWORD]);
        $this->verifyExpectedAttributes($scheduleCoordinatorOrm, self::$expectedDefaults);
    }

    public function test_loadById()
    {
        $scheduleCoordinatorOrm = ScheduleCoordinatorOrm::loadById($this->defaultScheduleCoordinatorOrm->id);
        $this->verifyExpectedAttributes($scheduleCoordinatorOrm, self::$defaultScheduleCoordinatorOrmAttributes);;
    }

    public function test_loadByEmail()
    {
        $scheduleCoordinatorOrm = ScheduleCoordinatorOrm::loadByLeagueIdAndEmail($this->defaultLeagueOrm->id, $this->defaultScheduleCoordinatorOrm->email);
        $this->verifyExpectedAttributes($scheduleCoordinatorOrm, self::$defaultScheduleCoordinatorOrmAttributes);;
    }

    private function verifyExpectedAttributes($scheduleCoordinatorOrm, $attributes)
    {
        $this->assertTrue($scheduleCoordinatorOrm->id > 0);
        $this->assertEquals($this->defaultLeagueOrm->id,    $scheduleCoordinatorOrm->leagueId);
        $this->assertEquals($attributes['email'],           $scheduleCoordinatorOrm->email);
        $this->assertEquals($attributes['name'],            $scheduleCoordinatorOrm->name);
        $this->assertEquals($attributes['password'],        $scheduleCoordinatorOrm->password);
    }
}