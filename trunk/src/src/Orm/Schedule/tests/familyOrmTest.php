<?php

namespace DAG\Orm\Schedule;

require_once 'helper.php';


/**
 * @testSuite test Facility ORM
 */
class FamilyOrmTest extends ORM_TestHelper
{
    /**
     * Expected values on create and load
     */
    protected static $expectedDefaults =
        [
            self::PHONE => '14156890644',
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
        $familyOrm = FamilyOrm::create(
            $this->defaultSeasonOrm->id,
            self::$expectedDefaults[self::PHONE]);

        $this->verifyExpectedAttributes($familyOrm, self::$expectedDefaults);
    }

    public function test_loadById()
    {
        $familyOrm = FamilyOrm::loadById($this->defaultFamilyOrm->id);
        $this->verifyExpectedAttributes($familyOrm, self::$defaultFamilyOrmAttributes);
    }

    public function test_loadByPhone()
    {
        $familyOrm = FamilyOrm::loadBySeasonIdAndPhone($this->defaultSeasonOrm->id, self::$defaultFamilyOrmAttributes[self::PHONE]);
        $this->verifyExpectedAttributes($familyOrm, self::$defaultFamilyOrmAttributes);
    }

    private function verifyExpectedAttributes($familyOrm, $attributes)
    {
        $this->assertTrue($familyOrm->id > 0);
        $this->assertEquals($this->defaultSeasonOrm->id,    $familyOrm->seasonId);
        $this->assertEquals($attributes[self::PHONE],       $familyOrm->phone);
    }
}