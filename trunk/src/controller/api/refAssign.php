<?php

use \DAG\Domain\Schedule\Coordinator;
use \DAG\Domain\Schedule\Referee;
use \DAG\Domain\Schedule\Game;
use \DAG\Domain\Schedule\GameReferee;
use \DAG\Orm\Schedule\GameRefereeOrm;
use \DAG\Domain\Schedule\RefereeCrew;
use \DAG\Domain\Schedule\StandbyReferee;
use \DAG\Domain\Schedule\Facility;
use \DAG\Domain\Schedule\GameDate;

    /**
 * Class Controller_Api_RefAssign
 *
 * @brief Assign (or swap or move) a referee
 */
class Controller_Api_RefAssign extends Controller_Api_Base
{
    const TITLE_ROW     = 'title';
    const CENTER_ROW    = 'C';
    const AR1_ROW       = 'AR1';
    const AR2_ROW       = 'AR2';
    const MENTOR_ROW    = 'M';
    const LIST_ROW      = 'L';
    const CREW_ROW      = 'S';

    /** @var string */
    private $cell1Data;
    /** @var string */
    private $cell2Data;

    public function __construct()
    {
        parent::__construct(self::REFEREE_ADMIN_COOKIE, Coordinator::REFEREE_COORDINATOR_USER_TYPE);

        if (isset($_SERVER['REQUEST_METHOD']) and $_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->cell1Data = $this->getRequestAttribute(View_Base::CELL_1_DATA, '');
            $this->cell2Data = $this->getRequestAttribute(View_Base::CELL_2_DATA, '');
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

        // Get data from cells
        $toCellData     = explode('_', $this->cell1Data);
        $fromCellData   = explode('_', $this->cell2Data);
        switch (count($toCellData)) {
            case 3:
                if (count($fromCellData) != 3) {
                    print "FAILURE: Invalid cell2 data: $this->cell2Data";
                    return;
                }
                $this->processReferee($fromCellData, $toCellData);
                return;
            case 4:
                if (count($fromCellData) != 4) {
                    $count = count($fromCellData);
                    print "FAILURE: Invalid cell2 data: $this->cell2Data, count=$count";
                    return;
                }
                $this->processRefereeCrew($fromCellData, $toCellData);
                return;
            case 7:
                $this->processStandby($fromCellData, $toCellData);
                return;
            default:
                print "FAILURE: Invalid cell2 data: $this->cell2Data";
                return;
        }
    }

    private function processReferee($fromCellData, $toCellData)
    {
        $fromRowType    = $fromCellData[0];
        $fromGameId     = $fromCellData[1];
        $fromRefereeId  = $fromCellData[2];

        $toRowType    = $toCellData[0];
        $toGameId     = $toCellData[1];
        $toRefereeId  = $toCellData[2];

        if ($fromGameId == 0) {
            $result = $this->assign($fromRefereeId, $toRowType, $toGameId);
        } else if ($toRefereeId == 0) {
            $result = $this->move($fromRefereeId, $fromGameId, $toRowType, $toGameId);
        } else {
            $result = $this->swap($fromRefereeId, $fromRowType, $fromGameId, $toRefereeId, $toRowType, $toGameId);
        }

        print $result;
    }

    private function processStandby($fromCellData, $toCellData)
    {
        if ($fromCellData[0] == 'standby' and $toCellData[0] == 'standby') {
            // Swap of standby's
            // ${rowType}_${facilityId}_{$gameDateId}_${divisionName}_${startTime}_${refereeId}
            $this->moveStandby(
                $fromCellData[2],
                $fromCellData[3],
                $fromCellData[4],
                $fromCellData[5],
                $fromCellData[6],
                $toCellData[1],
                $toCellData[2],
                $toCellData[3],
                $toCellData[4],
                $toCellData[5]);
        } else if ($fromCellData[0] == 'standby' and $toCellData[2] == 0) {
            // Move standby to a game
            print "SUCCESS: sort of - not really implemented yet ;-)";
        } else if ($fromCellData[0] == 'standby' and $toCellData[2] != 0) {
            // Swap standby with game ref
            print "SUCCESS: sort of - not really implemented yet ;-)";
        } else if ($toCellData[0] == 'standby' and $fromCellData[0] == 0) {
            // Assign standby
            print $this->assignStandby($fromCellData[2], $toCellData[1], $toCellData[2], $toCellData[3], $toCellData[4], $toCellData[5]);
        } else if ($toCellData[0] == 'standby' and $fromCellData[0] != 0) {
            // Swap game ref with standby
            print "FAILURE: Swap of game ref with a standby referee is not supported";
        } else {
            print "FAILURE: cell1: $fromCellData, cell2: $toCellData";
        }
    }

    private function processRefereeCrew($fromCellData, $toCellData)
    {
        $fromGameId         = $fromCellData[1];
        $fromRefereeCrewId  = $fromCellData[2];

        $toGameId           = $toCellData[1];
        $toRefereeCrewId    = $toCellData[2];

        if ($fromGameId == 0) {
            $result = $this->assignRefereeCrew($fromRefereeCrewId, $toGameId);
        } else if ($toRefereeCrewId == 0) {
            $result = $this->moveRefereeCrew($fromRefereeCrewId, $fromGameId, $toGameId);
        } else {
            $result = $this->swapRefereeCrew($fromRefereeCrewId, $fromGameId, $toRefereeCrewId, $toGameId);
        }

        print $result;
    }

    /**
     * @param int       $fromRefereeId
     * @param string    $toRowType
     * @param int       $toGameId
     *
     * @return string   - Prefixed w/ SUCCESS or FAILURE
     */
    private function assign($fromRefereeId, $toRowType, $toGameId)
    {
        $referee    = Referee::lookupById($fromRefereeId);
        $game       = Game::lookupById($toGameId);

        // Verify referee not already assigned to game
        if (GameReferee::findByGameAndReferee($game, $referee, $gameReferee)) {
            return "FAILURE: Referee " . $gameReferee->referee->name . " already assigned as " . $gameReferee->role . " to game: " . $gameReferee->game->id;
        }

        // Verify referee not already assigned to a game that overlaps with this game
        $gameReferees = GameReferee::lookupByReferee($referee);
        if (GameReferee::isAlreadyAssignedGame($game, $gameReferees)) {
            return "FAILURE: Referee " . $gameReferee->referee->name . " is already assigned a game that overlaps with this game";
        }

        // Get the role
        $role = GameReferee::getRoleFromRowType($toRowType);

        // Assign referee
        $gameReferee        = GameReferee::create($game, $referee, $role);
        $game->refereeCrew  = null; // Any move, swap or assign for an individual referee eliminates the ref crew short-cut

        // Yeppers!
        return "SUCCESS: Referee " . $gameReferee->referee->name . " assigned as " . $gameReferee->role . " to game: " . $gameReferee->game->id;
    }

    /**
     * @param int       $fromRefereeId
     * @param int       $fromGameId
     * @param string    $toRowType
     * @param int       $toGameId
     *
     * @return string   - Prefixed w/ SUCCESS or FAILURE
     */
    private function move($fromRefereeId, $fromGameId, $toRowType, $toGameId)
    {
        $toRole = GameReferee::getRoleFromRowType($toRowType);

        // Delete the from
        $referee            = Referee::lookupById($fromRefereeId);
        $game               = Game::lookupById($fromGameId);
        $game->refereeCrew  = null; // Any move, swap or assign for an individual referee eliminates the ref crew short-cut
        $gameReferee        = GameReferee::lookupByGameAndReferee($game, $referee);
        $gameReferee->delete();

        // Create the to
        $toGame                 = Game::lookupById($toGameId);
        $toGameReferee          = GameReferee::create($toGame, $referee, $toRole);
        $toGame->refereeCrew    = null; // Any move, swap or assign for an individual referee eliminates the ref crew short-cut

        return "SUCCESS: " . $gameReferee->referee->name . " moved from game "
            . $game->id . ":" . $gameReferee->role . " to game "
            . $toGame->id . ":" . $toGameReferee->role;
    }

    /**
     * @param int       $fromRefereeId
     * @param string    $fromRowType
     * @param int       $fromGameId
     * @param int       $toRefereeId
     * @param string    $toRowType
     * @param int       $toGameId
     *
     * @return string   - Prefixed w/ SUCCESS or FAILURE
     */
    private function swap($fromRefereeId, $fromRowType, $fromGameId, $toRefereeId, $toRowType, $toGameId)
    {
        $fromRole   = GameReferee::getRoleFromRowType($fromRowType);
        $toRole     = GameReferee::getRoleFromRowType($toRowType);

        // Delete the from
        $fromReferee            = Referee::lookupById($fromRefereeId);
        $fromGame               = Game::lookupById($fromGameId);
        $fromGameReferee        = GameReferee::lookupByGameAndReferee($fromGame, $fromReferee);
        $fromGameReferee->delete();
        $fromGame->refereeCrew  = null; // Any move, swap or assign for an individual referee eliminates the ref crew short-cut

        // Delete the to
        $toReferee              = Referee::lookupById($toRefereeId);
        $toGame                 = Game::lookupById($toGameId);
        $toGameReferee          = GameReferee::lookupByGameAndReferee($toGame, $toReferee);
        $toGameReferee->delete();
        $toGame->refereeCrew    = null; // Any move, swap or assign for an individual referee eliminates the ref crew short-cut

        // Create the new game referees (swap)
        $fromGameReferee    = GameReferee::create($fromGame, $toReferee, $fromRole);
        $toGameReferee      = GameReferee::create($toGame, $fromReferee, $toRole);

        return "SUCCESS: " . $toGameReferee->referee->name . " moved to game "
            . $toGameReferee->game->id . ":" . $toGameReferee->role . " and "
            . $fromGameReferee->referee->name . " moved to game "
            . $fromGameReferee->game->id . ":" . $fromGameReferee->role;
    }

    /**
     * @param int       $fromRefereeCrewId
     * @param int       $toGameId
     * @param bool      $verifyNoOverlapAssignments - defaults to true
     *
     * @return string   - Prefixed w/ SUCCESS or FAILURE
     */
    private function assignRefereeCrew($fromRefereeCrewId, $toGameId, $verifyNoOverlapAssignments = true)
    {
        $refereeCrew    = RefereeCrew::lookupById($fromRefereeCrewId);
        $game           = Game::lookupById($toGameId);

        // Verify no referees are currently assigned to the game
        $gameReferees = GameReferee::lookupByGame($game);
        if (count($gameReferees) > 0) {
            return "FAILURE: Referees are already assigned to game: " . $game->id;
        }

        // Verify referees not already assigned to a game that overlaps with this game
        if ($verifyNoOverlapAssignments) {
            $gameReferees = GameReferee::lookupByReferee($refereeCrew->centerReferee);
            if (GameReferee::isAlreadyAssignedGame($game, $gameReferees)) {
                return "FAILURE: Center Referee " . $refereeCrew->centerReferee->name . " is already assigned a game that overlaps with this game";
            }

            $gameReferees = GameReferee::lookupByReferee($refereeCrew->assistantReferee1);
            if (GameReferee::isAlreadyAssignedGame($game, $gameReferees)) {
                return "FAILURE: Assistant Referee " . $refereeCrew->assistantReferee1->name . " is already assigned a game that overlaps with this game";
            }

            $gameReferees = GameReferee::lookupByReferee($refereeCrew->assistantReferee2);
            if (GameReferee::isAlreadyAssignedGame($game, $gameReferees)) {
                return "FAILURE: Assistant Referee " . $refereeCrew->assistantReferee2->name . " is already assigned a game that overlaps with this game";
            }
        }

        // Assign referees to game
        GameReferee::create($game, $refereeCrew->centerReferee, GameRefereeOrm::CENTER_ROLE);
        GameReferee::create($game, $refereeCrew->assistantReferee1, GameRefereeOrm::ASSISTANT_ROLE_1);
        GameReferee::create($game, $refereeCrew->assistantReferee2, GameRefereeOrm::ASSISTANT_ROLE_2);

        // Update the game with the referee cres
        $game->refereeCrew = $refereeCrew;

        // Yeppers!
        return "SUCCESS: Referee crew " . $refereeCrew->name . " assigned to game: " . $game->id;
    }

    /**
     * @param int       $fromRefereeCrewId
     * @param int       $fromGameId
     * @param int       $toGameId
     *
     * @return string   - Prefixed w/ SUCCESS or FAILURE
     */
    private function moveRefereeCrew($fromRefereeCrewId, $fromGameId, $toGameId)
    {
        $refereeCrew    = RefereeCrew::lookupById($fromRefereeCrewId);
        $fromGame       = Game::lookupById($fromGameId);
        $toGame         = Game::lookupById($toGameId);

        // Verify no referees are currently assigned to the toGame
        $gameReferees = GameReferee::lookupByGame($toGame);
        if (count($gameReferees) > 0) {
            return "FAILURE: Referees are already assigned to game: " . $toGame->id;
        }

        // Remove referees from the fromGame
        $gameReferees = GameReferee::lookupByGame($fromGame);
        foreach ($gameReferees as $gameReferee) {
            $gameReferee->delete();
        }
        $fromGame->refereeCrew = null;

        // Verify referees not already assigned to a game that overlaps with this game
        $gameReferees = GameReferee::lookupByReferee($refereeCrew->centerReferee);
        if (GameReferee::isAlreadyAssignedGame($toGame, $gameReferees)) {
            $this->assignRefereeCrew($fromRefereeCrewId, $fromGameId, false);
            return "FAILURE: Center Referee " . $refereeCrew->centerReferee->name . " is already assigned a game that overlaps with this game";
        }

        $gameReferees = GameReferee::lookupByReferee($refereeCrew->assistantReferee1);
        if (GameReferee::isAlreadyAssignedGame($toGame, $gameReferees)) {
            $this->assignRefereeCrew($fromRefereeCrewId, $fromGameId, false);
            return "FAILURE: Assistant Referee " . $refereeCrew->assistantReferee1->name . " is already assigned a game that overlaps with this game";
        }

        $gameReferees = GameReferee::lookupByReferee($refereeCrew->assistantReferee2);
        if (GameReferee::isAlreadyAssignedGame($toGame, $gameReferees)) {
            $this->assignRefereeCrew($fromRefereeCrewId, $fromGameId, false);
            return "FAILURE: Assistant Referee " . $refereeCrew->assistantReferee2->name . " is already assigned a game that overlaps with this game";
        }

        // Assign referees to the toGame
        GameReferee::create($toGame, $refereeCrew->centerReferee, GameRefereeOrm::CENTER_ROLE);
        GameReferee::create($toGame, $refereeCrew->assistantReferee1, GameRefereeOrm::ASSISTANT_ROLE_1);
        GameReferee::create($toGame, $refereeCrew->assistantReferee2, GameRefereeOrm::ASSISTANT_ROLE_2);

        // Update the game with the referee cres
        $toGame->refereeCrew = $refereeCrew;

        // Yeppers!
        return "SUCCESS: " . $refereeCrew->name . " moved from game " . $fromGame->id . " to game " . $toGame->id;
    }

    /**
     * @param int       $fromRefereeCrewId
     * @param int       $fromGameId
     * @param int       $toRefereeCrewId
     * @param int       $toGameId
     *
     * @return string   - Prefixed w/ SUCCESS or FAILURE
     */
    private function swapRefereeCrew($fromRefereeCrewId, $fromGameId, $toRefereeCrewId, $toGameId)
    {
        $fromRefereeCrew    = RefereeCrew::lookupById($fromRefereeCrewId);
        $toRefereeCrew      = RefereeCrew::lookupById($toRefereeCrewId);
        $fromGame           = Game::lookupById($fromGameId);
        $toGame             = Game::lookupById($toGameId);

        // Remove referees from the fromGame
        $gameReferees = GameReferee::lookupByGame($fromGame);
        foreach ($gameReferees as $gameReferee) {
            $gameReferee->delete();
        }
        $fromGame->refereeCrew = null;

        // Remove referees from the toGame
        $gameReferees = GameReferee::lookupByGame($toGame);
        foreach ($gameReferees as $gameReferee) {
            $gameReferee->delete();
        }
        $toGame->refereeCrew = null;

        // Assign fromRefereeCrew to toGame
        $result = $this->assignRefereeCrew($fromRefereeCrewId, $toGameId);
        if (substr($result, 0, 7) != 'SUCCESS') {
            // revert
            $this->assignRefereeCrew($fromRefereeCrewId, $fromGameId, false);
            $this->assignRefereeCrew($toRefereeCrewId, $toGameId, false);

            return $result;
        }

        // Assign toRefereeCrew to fromGame
        $result = $this->assignRefereeCrew($toRefereeCrewId, $fromGameId);
        if (substr($result, 0, 7) != 'SUCCESS') {
            // revert
            $gameReferees = GameReferee::lookupByGame($toGame);
            foreach ($gameReferees as $gameReferee) {
                $gameReferee->delete();
            }
            $toGame->refereeCrew = null;

            $this->assignRefereeCrew($fromRefereeCrewId, $fromGameId, false);
            $this->assignRefereeCrew($toRefereeCrewId, $toGameId, false);

            return $result;
        }

        return "SUCCESS: " . $toRefereeCrew->name . " moved to game " . $fromGame->id . " and "
            . $fromRefereeCrew->name . " moved to game " . $toGame->id;
    }

    private function assignStandby($fromRefereeId, $rowType, $facilityId, $gameDateId, $divisionName, $startTime)
    {
        $referee        = Referee::lookupById($fromRefereeId);
        $role           = GameReferee::getRoleFromRowType($rowType);
        $facility       = Facility::lookupById($facilityId);
        $gameDate       = GameDate::lookupById($gameDateId);

        // TODO: Fail if already assigned to avoid duplicate assignments
        StandbyReferee::create($facility, $gameDate, $divisionName, $startTime, $referee, $role);

        return "SUCCESS: " . $referee->name . " assigned as a standby for $startTime";
    }

    private function moveStandby(
        $fromFacilityId,
        $fromGameDateId,
        $fromDivisionName,
        $fromStartTime,
        $fromRefereeId,
        $toRowType,
        $toFacilityId,
        $toGameDateId,
        $toDivisionName,
        $toStartTime)
    {
        $fromFacility       = Facility::lookupById($fromFacilityId);
        $fromGameDate       = GameDate::lookupById($fromGameDateId);
        $fromReferee        = Referee::lookupById($fromRefereeId);

        /** @var StandbyReferee $fromStandbyReferee */
        $fromStandbyReferee = null;
        if (!StandbyReferee::findByStartTimeReferee($fromFacility, $fromGameDate, $fromDivisionName, $fromStartTime, $fromReferee, $fromStandbyReferee)) {
            return "FAILURE: Unable to find 'from' standby referee";
        }

        $toFacility       = Facility::lookupById($toFacilityId);
        $toGameDate       = GameDate::lookupById($toGameDateId);
        $toRole           = GameReferee::getRoleFromRowType($toRowType);

        // Delete existing assignments
        $fromStandbyReferee->delete();

        // Create new assignments
        StandbyReferee::create($toFacility, $toGameDate, $toDivisionName, $toStartTime, $fromReferee, $toRole);

        return "SUCCESS: " . $fromReferee->name . " moved to new standby assignemnt";
    }
}