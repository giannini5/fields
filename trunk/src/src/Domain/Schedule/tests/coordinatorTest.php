<?php

namespace DAG\Domain\Schedule;

use DAG\Orm\Schedule\ORM_TestHelper;

require_once dirname(dirname(dirname(__DIR__))) . '/Orm/Schedule/tests/helper.php';



/**
 * @testSuite test Coordinator
 */
class CoordinatorTest extends ORM_TestHelper
{
    /**
     * Expected values on create and load
     */
    protected static $expectedDefaults = array(
        'email'         => 'TEST email',
        'name'          => 'TEST name',
        'password'      => 'password',
    );

    protected $coordinatorsToCleanup = array();
    protected $league;

    protected function setUp()
    {
        $this->primeDatabase();

        $this->league = League::lookupById($this->defaultLeagueOrm->id);

        $this->coordinatorsToCleanup[] = Coordinator::create(
            $this->league,
            self::$expectedDefaults['email'],
            self::$expectedDefaults['name'],
            self::$expectedDefaults['password']);
    }

    protected function tearDown()
    {
        foreach ($this->coordinatorsToCleanup as $entity) {
            $entity->delete();
        }

        $this->clearDatabase();
    }

    public function test_create()
    {
        $coordinator = $this->coordinatorsToCleanup[0];
        $this->validateCoordinator($coordinator, $this->league, self::$expectedDefaults);
    }

    public function test_lookupById()
    {
        $coordinator = Coordinator::lookupById($this->coordinatorsToCleanup[0]->id);
        $this->validateCoordinator($coordinator, $this->league, self::$expectedDefaults);
    }

    public function test_lookupByEmail()
    {
        $coordinator = Coordinator::lookupByEmail($this->league, self::$expectedDefaults['email']);
        $this->validateCoordinator($coordinator, $this->league, self::$expectedDefaults);
    }

    public function test_lookupByLeague()
    {
        $coordinators = Coordinator::lookupByLeague($this->league);
        $this->assertTrue(count($coordinators) == 2);
    }

    public function validateCoordinator($coordinator, $league, $expectedDefaults)
    {
        $this->assertTrue($coordinator->id > 0);
        $this->assertEquals($expectedDefaults['email'],     $coordinator->email);
        $this->assertEquals($expectedDefaults['name'],      $coordinator->name);
        $this->assertEquals($expectedDefaults['password'],  $coordinator->password);
        $this->assertEquals($league,                        $coordinator->league);
    }
}