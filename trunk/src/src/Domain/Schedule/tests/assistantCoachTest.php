<?php

namespace DAG\Domain\Schedule;

use DAG\Orm\Schedule\ORM_TestHelper;

require_once dirname(dirname(dirname(__DIR__))) . '/Orm/Schedule/tests/helper.php';



/**
 * @testSuite test AssistantCoach
 */
class AssistantCoachTest extends ORM_TestHelper
{
    /**
     * Expected values on create and load
     */
    protected static $expectedDefaults = array(
        'name'          => 'TEST Domain assistantCoach name',
        'email'         => 'TEST Domain assistantCoach email',
        'phone1'        => 'TEST Domain assistantCoach phone1',
        'phone2'        => 'TEST Domain assistantCoach phone2',
    );

    protected $assistantCoachesToCleanup = array();
    protected $team;
    protected $family;

    protected function setUp()
    {
        $this->primeDatabase();

        $this->team     = Team::lookupById($this->defaultTeamOrm->id);
        $this->family   = Family::lookupById($this->defaultFamilyOrm->id);

        // Clear out default assistantCoach and create a new one for testing
        $assistantCoach = AssistantCoach::lookupById($this->defaultAssistantCoachOrm->id);
        $assistantCoach->delete();

        $this->assistantCoachesToCleanup[] = AssistantCoach::create(
            $this->team,
            $this->family,
            self::$expectedDefaults['name'],
            self::$expectedDefaults['email'],
            self::$expectedDefaults['phone1'],
            self::$expectedDefaults['phone2']);
    }

    protected function tearDown()
    {
        foreach ($this->assistantCoachesToCleanup as $entity) {
            $entity->delete();
        }

        $this->clearDatabase();
    }

    public function test_create()
    {
        $assistantCoach = $this->assistantCoachesToCleanup[0];
        $this->validateAssistantCoach($assistantCoach, $this->team, $this->family, self::$expectedDefaults);
    }

    public function test_createWithNoFamily()
    {
        $this->assistantCoachesToCleanup[0]->delete();
        $this->assistantCoachesToCleanup = [];

        self::$expectedDefaults['name'] = 'AssistantCoach with no family';
        $assistantCoach = AssistantCoach::create(
            $this->team,
            null,
            self::$expectedDefaults['name'],
            self::$expectedDefaults['email'],
            self::$expectedDefaults['phone1'],
            self::$expectedDefaults['phone2']);
        $this->assistantCoachesToCleanup[] = $assistantCoach;

        $this->validateAssistantCoach($assistantCoach, $this->team, null, self::$expectedDefaults);
    }

    public function test_lookupById()
    {
        $assistantCoach = AssistantCoach::lookupById($this->assistantCoachesToCleanup[0]->id);
        $this->validateAssistantCoach($assistantCoach, $this->team, $this->family, self::$expectedDefaults);
    }

    public function test_lookupByTeamAndEmail()
    {
        $assistantCoach = AssistantCoach::lookupByTeamAndName($this->team, self::$expectedDefaults['name']);
        $this->validateAssistantCoach($assistantCoach, $this->team, $this->family, self::$expectedDefaults);
    }

    public function test_lookupByTeam()
    {
        $assistantCoaches = AssistantCoach::lookupByTeam($this->team);
        $this->assertTrue(count($assistantCoaches) == 1);
        $this->validateAssistantCoach($assistantCoaches[0], $this->team, $this->family, self::$expectedDefaults);
    }

    public function test_lookupByFamily()
    {
        $assistantCoaches = AssistantCoach::lookupByFamily($this->family);
        $this->assertTrue(count($assistantCoaches) == 1);
        $this->validateAssistantCoach($assistantCoaches[0], $this->team, $this->family, self::$expectedDefaults);
    }

    public function validateAssistantCoach($assistantCoach, $team, $family, $expectedDefaults)
    {
        $this->assertTrue($assistantCoach->id > 0);
        $this->assertEquals($expectedDefaults['name'],      $assistantCoach->name);
        $this->assertEquals($expectedDefaults['email'],     $assistantCoach->email);
        $this->assertEquals($expectedDefaults['phone1'],    $assistantCoach->phone1);
        $this->assertEquals($expectedDefaults['phone2'],    $assistantCoach->phone2);
        $this->assertEquals($team,                          $assistantCoach->team);
        $this->assertEquals($family,                        $assistantCoach->family);
    }
}