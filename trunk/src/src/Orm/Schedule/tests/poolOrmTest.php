<?php

namespace DAG\Orm\Schedule;

require_once 'helper.php';


/**
 * @testSuite test Facility ORM
 */
class PoolOrmTest extends ORM_TestHelper
{
    /**
     * Expected values on create and load
     */
    protected static $expectedDefaults =
        [
            self::NAME => 'TEST Pool',
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
        $poolOrm = PoolOrm::create(
            $this->defaultDivisionOrm->id,
            self::$expectedDefaults[self::NAME]);

        $this->verifyExpectedAttributes($poolOrm, self::$expectedDefaults);
    }

    public function test_loadById()
    {
        $poolOrm = PoolOrm::loadById($this->defaultPoolOrm->id);
        $this->verifyExpectedAttributes($poolOrm, self::$defaultPoolOrmAttributes);
    }

    public function test_loadByName()
    {
        $poolOrm = PoolOrm::loadByDivisionIdAndName($this->defaultDivisionOrm->id, self::$defaultPoolOrmAttributes[self::NAME]);
        $this->verifyExpectedAttributes($poolOrm, self::$defaultPoolOrmAttributes);
    }

    private function verifyExpectedAttributes($poolOrm, $attributes)
    {
        $this->assertTrue($poolOrm->id > 0);
        $this->assertEquals($this->defaultDivisionOrm->id,  $poolOrm->divisionId);
        $this->assertEquals($attributes[self::NAME],        $poolOrm->name);
    }
}