<?php

namespace DAG\Domain\Schedule;

use DAG\Orm\Schedule\ORM_TestHelper;

require_once dirname(dirname(dirname(__DIR__))) . '/Orm/Schedule/tests/helper.php';



/**
 * @testSuite test Coach
 */
class CoachTest extends ORM_TestHelper
{
    /**
     * Expected values on create and load
     */
    protected static $expectedDefaults = array(
        'name'          => 'TEST Domain coach name',
        'email'         => 'TEST Domain coach email',
        'phone1'        => 'TEST Domain coach phone1',
        'phone2'        => 'TEST Domain coach phone2',
    );

    protected $coachesToCleanup = array();
    protected $team;
    protected $family;

    protected function setUp()
    {
        $this->primeDatabase();

        $this->team     = Team::lookupById($this->defaultTeamOrm->id);
        $this->family   = Family::lookupById($this->defaultFamilyOrm->id);

        // Clear out default coach and create a new one for testing
        $coach = Coach::lookupById($this->defaultCoachOrm->id);
        $coach->delete();

        $this->coachesToCleanup[] = Coach::create(
            $this->team,
            $this->family,
            self::$expectedDefaults['name'],
            self::$expectedDefaults['email'],
            self::$expectedDefaults['phone1'],
            self::$expectedDefaults['phone2']);
    }

    protected function tearDown()
    {
        foreach ($this->coachesToCleanup as $entity) {
            $entity->delete();
        }

        $this->clearDatabase();
    }

    public function test_create()
    {
        $coach = $this->coachesToCleanup[0];
        $this->validateCoach($coach, $this->team, $this->family, self::$expectedDefaults);
    }

    public function test_createWithNoFamily()
    {
        $this->coachesToCleanup[0]->delete();
        $this->coachesToCleanup = [];

        self::$expectedDefaults['name'] = 'Coach with no family';
        $coach = Coach::create(
            $this->team,
            null,
            self::$expectedDefaults['name'],
            self::$expectedDefaults['email'],
            self::$expectedDefaults['phone1'],
            self::$expectedDefaults['phone2']);
        $this->coachesToCleanup[] = $coach;

        $this->validateCoach($coach, $this->team, null, self::$expectedDefaults);
    }

    public function test_lookupById()
    {
        $coach = Coach::lookupById($this->coachesToCleanup[0]->id);
        $this->validateCoach($coach, $this->team, $this->family, self::$expectedDefaults);
    }

    public function test_lookupByTeam()
    {
        $coach = Coach::lookupByTeam($this->team);
        $this->validateCoach($coach, $this->team, $this->family, self::$expectedDefaults);
    }

    public function test_findCoachByTeamTrue()
    {
        $coach = null;
        $result = Coach::findCoachForTeam($this->team, $coach);
        $this->assertTrue($result, 'Unable to find Coach for team');
        $this->validateCoach($coach, $this->team, $this->family, self::$expectedDefaults);
    }

    public function test_findCoachByTeamFalse()
    {
        // Setup
        $coach = array_shift($this->coachesToCleanup);
        $coach->delete();
        $coach = null;

        // Run test
        $result = Coach::findCoachForTeam($this->team, $coach);

        // Verify results
        $this->assertFalse($result, 'Found Coach for team');
        $this->assertTrue(!isset($coach), 'Coach was set, but not found');
    }

    public function test_lookupByFamily()
    {
        $coaches = Coach::lookupByFamily($this->family);
        $this->assertTrue(count($coaches) == 1);
        $this->validateCoach($coaches[0], $this->team, $this->family, self::$expectedDefaults);
    }

    public function validateCoach($coach, $team, $family, $expectedDefaults)
    {
        $this->assertTrue($coach->id > 0);
        $this->assertEquals($expectedDefaults['name'],      $coach->name);
        $this->assertEquals($expectedDefaults['email'],     $coach->email);
        $this->assertEquals($expectedDefaults['phone1'],    $coach->phone1);
        $this->assertEquals($expectedDefaults['phone2'],    $coach->phone2);
        $this->assertEquals($team,                          $coach->team);
        $this->assertEquals($family,                        $coach->family);
    }
}