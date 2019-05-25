<?php

namespace DAG\Orm\Schedule;

require_once 'helper.php';


/**
 * @testSuite test Referee ORM
 */
class RefereeOrmTest extends ORM_TestHelper
{
    /**
     * Expected values on create and load
     */
    protected static $expectedDefaults =
        [
            self::NAME                  => 'TEST Referee',
            self::EMAIL                 => 'TEST Email',
            self::PHONE                 => 'TEST Phone',
            self::BADGE_ID              => RefereeOrm::INTERMEDIATE,
            self::MAX_GAMES_PER_DAY     => 2,
            self::SPECIAL_INSTRUCTIONS  => "Coaches suck!"
        ];

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
        $refereeOrm = RefereeOrm::create(
            $this->defaultSeasonOrm->id,
            $this->defaultFamilyOrm->id,
            self::$expectedDefaults[self::NAME],
            self::$expectedDefaults[self::EMAIL],
            self::$expectedDefaults[self::PHONE],
            self::$expectedDefaults[self::BADGE_ID],
            self::$expectedDefaults[self::MAX_GAMES_PER_DAY],
            self::$expectedDefaults[self::SPECIAL_INSTRUCTIONS]);

        $this->verifyExpectedAttributes($refereeOrm, self::$expectedDefaults);
    }

    public function test_loadById()
    {
        $refereeOrm = RefereeOrm::loadById($this->defaultRefereeOrm->id);
        $this->verifyExpectedAttributes($refereeOrm, self::$defaultRefereeOrmAttributes);
    }

    public function test_loadByEmailAndName()
    {
        $refereeOrm = RefereeOrm::loadBySeasonIdEmailAndName($this->defaultSeasonOrm->id,
            self::$defaultRefereeOrmAttributes[self::EMAIL],
            self::$defaultRefereeOrmAttributes[self::NAME]);
        $this->verifyExpectedAttributes($refereeOrm, self::$defaultRefereeOrmAttributes);
    }

    public function test_loadByEmail()
    {
        $refereeOrms = RefereeOrm::loadBySeasonIdAndEmail($this->defaultSeasonOrm->id, self::$defaultRefereeOrmAttributes[self::EMAIL]);
        $this->assertEquals(1, count($refereeOrms));
        $this->verifyExpectedAttributes($refereeOrms[0], self::$defaultRefereeOrmAttributes);
    }

    public function test_loadByName()
    {
        $refereeOrms = RefereeOrm::loadBySeasonIdAndName($this->defaultSeasonOrm->id, self::$defaultRefereeOrmAttributes[self::NAME]);
        $this->assertEquals(1, count($refereeOrms));
        $this->verifyExpectedAttributes($refereeOrms[0], self::$defaultRefereeOrmAttributes);
    }

    private function verifyExpectedAttributes($refereeOrm, $attributes)
    {
        $this->assertTrue($refereeOrm->id > 0);
        $this->assertEquals($this->defaultSeasonOrm->id,                $refereeOrm->seasonId);
        $this->assertEquals($this->defaultFamilyOrm->id,                $refereeOrm->familyId);
        $this->assertEquals($attributes[self::NAME],                    $refereeOrm->name);
        $this->assertEquals($attributes[self::EMAIL],                   $refereeOrm->email);
        $this->assertEquals($attributes[self::PHONE],                   $refereeOrm->phone);
        $this->assertEquals($attributes[self::BADGE_ID],                $refereeOrm->badgeId);
        $this->assertEquals($attributes[self::MAX_GAMES_PER_DAY],       $refereeOrm->maxGamesPerDay);
        $this->assertEquals($attributes[self::SPECIAL_INSTRUCTIONS],    $refereeOrm->specialInstructions);
    }
}