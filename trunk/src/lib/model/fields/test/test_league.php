<?php
require_once '../../../autoLoader.php';

class Model_LeagueTest extends PHPUnit_Framework_TestCase
{
    public $leagueName = 'Test AYSO Region 122';

    /**
     * Prepare to run test
     */
    protected function setUp()
    {
        Model_Fields_League::Delete($this->leagueName);
    }

    /**
     * Default Construction
     */
    public function testStaticMethods()
    {
        $league = Model_Fields_League::Create($this->leagueName);
        $leagueId = $league->id;
        $this->assertEquals($league->name, $this->leagueName);

        $league = Model_Fields_League::LookupByName($this->leagueName);
        $this->assertEquals($league->name, $this->leagueName);
        $this->assertEquals($league->id, $leagueId);

        $league = Model_Fields_League::LookupById($leagueId);
        $this->assertEquals($league->name, $this->leagueName);
        $this->assertEquals($league->id, $leagueId);
    }
}
?>
