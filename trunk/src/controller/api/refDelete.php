<?php

use \DAG\Domain\Schedule\Coordinator;
use \DAG\Domain\Schedule\Referee;
use \DAG\Domain\Schedule\Game;
use \DAG\Domain\Schedule\GameReferee;
use \DAG\Domain\Schedule\RefereeCrew;

/**
 * Class Controller_Api_RefDelete
 *
 * @brief Assign (or swap or move) a referee
 */
class Controller_Api_RefDelete extends Controller_Api_Base
{
    /** @var string */
    private $cell1Data;

    public function __construct()
    {
        parent::__construct(self::REFEREE_ADMIN_COOKIE, Coordinator::REFEREE_COORDINATOR_USER_TYPE);

        if (isset($_SERVER['REQUEST_METHOD']) and $_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->cell1Data = $this->getRequestAttribute(View_Base::CELL_1_DATA, '');
        }
    }

    /**
     * @brief On POST, complete the transaction and then render the page (with error message if any)
     */
    public function process()
    {
        if (!$this->m_isAuthenticated) {
            print "FAILURE: No longer authenticated - Click on SCHEDULE tab to re-authenticate.";
            return;
        }

        // Get data from cell
        $fromCellData = explode('_', $this->cell1Data);
        switch (count($fromCellData)) {
            case 3:
                $fromGameId     = $fromCellData[1];
                $fromRefereeId  = $fromCellData[2];
                $result = $this->delete($fromRefereeId, $fromGameId);
                break;
            case 4:
                $fromGameId         = $fromCellData[1];
                $fromRefereeSquadId = $fromCellData[2];
                $result = $this->deleteCrew($fromRefereeSquadId, $fromGameId);
                break;
            default:
                print "FAILURE: Invalid cell data: $this->cell1Data";
                return;
        }

        print $result;
    }

    /**
     * @param int       $fromRefereeId
     * @param int       $fromGameId
     *
     * @return string   - Prefixed w/ SUCCESS or FAILURE
     */
    private function delete($fromRefereeId, $fromGameId)
    {
        $referee        = Referee::lookupById($fromRefereeId);
        $game           = Game::lookupById($fromGameId);
        $gameReferee    = GameReferee::lookupByGameAndReferee($game, $referee);

        $gameReferee->delete();
        $game->refereeCrew = null;

        // Yeppers!
        return "SUCCESS: Referee " . $referee->name . " removed from game " . $gameReferee->game->id;
    }

    /**
     * @param int       $refereeCrewId
     * @param int       $gameId
     *
     * @return string   - Prefixed w/ SUCCESS or FAILURE
     */
    private function deleteCrew($refereeCrewId, $gameId)
    {
        $game           = Game::lookupById($gameId);
        $refereeCrew    = RefereeCrew::lookupById($refereeCrewId);

        // Verify referee crew is assigned to game
        if (!isset($game->refereeCrew) or $game->refereeCrew->id != $refereeCrew->id) {
            return "FAILURE: Referee crew $refereeCrew->name is not assigned to game $game->id";
        }

        // Remove referees from the fromGame
        $gameReferees = GameReferee::lookupByGame($game);
        foreach ($gameReferees as $gameReferee) {
            $gameReferee->delete();
        }
        $game->refereeCrew = null;

        // Yeppers!
        return "SUCCESS: Referee " . $refereeCrew->name . " removed from game " . $game->id;
    }
}