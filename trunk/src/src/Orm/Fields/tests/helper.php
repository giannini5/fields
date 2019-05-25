<?php

namespace DAG\Orm\Fields;

require_once dirname(dirname(dirname(dirname(__DIR__)))) . '/lib/autoload.php';


abstract class ORM_TestHelper extends \PHPUnit_Framework_TestCase {
    // Database column names:
    const USER_ID           = 'userId';
    const USER_TYPE         = 'userType';
    const TEAM_ID           = 'teamId';

    protected static $defaultSessionOrmAttributes =
        [
            self::USER_ID   => 4,
            self::USER_TYPE => SessionOrm::COACH,
            self::TEAM_ID   => 5,
        ];

    /** @var  SessionOrm */
    public $defaultSessionOrm;

    /**
     * Prime the database for unit testing
     */
    protected function primeDatabase()
    {
        $this->clearDatabase();

        $this->defaultSessionOrm = SessionOrm::create(
            self::$defaultSessionOrmAttributes[self::USER_ID],
            self::$defaultSessionOrmAttributes[self::USER_TYPE],
            self::$defaultSessionOrmAttributes[self::TEAM_ID]);
    }

    /**
     * Clear out all entities for the test league
     */
    protected function clearDatabase()
    {
        $this->clearSession();
    }

    /**
     * Clear the test session
     */
    protected function clearSession()
    {
        if (isset($this->defaultSessionOrm)) {
            $this->defaultSessionOrm->delete();
            $this->defaultSessionOrm = null;
        }
    }
}