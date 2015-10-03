<?php
require_once '../../../autoLoader.php';
require_once 'helper.php';

class Model_LeagueTest extends Model_TestHelpers
{
    /**
     * Prepare to run test
     */
    protected function setUp()
    {
        $this->clearEntities();
    }

    /**
     * Default Construction
     */
    public function testStaticMethods()
    {
        $league = Model_Fields_League::Create($this->m_leagueName);
        $leagueId = $league->id;
        $this->assertEquals($league->name, $this->m_leagueName);

        $league = Model_Fields_League::LookupByName($this->m_leagueName);
        $this->assertEquals($league->name, $this->m_leagueName);
        $this->assertEquals($league->id, $leagueId);

        $league = Model_Fields_League::LookupById($leagueId);
        $this->assertEquals($league->name, $this->m_leagueName);
        $this->assertEquals($league->id, $leagueId);
    }
}
?>
