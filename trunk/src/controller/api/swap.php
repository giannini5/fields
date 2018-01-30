<?php

use \DAG\Framework\Exception\Precondition;
use \DAG\Domain\Schedule\GameTime;
use \DAG\Domain\Schedule\DivisionField;
use \DAG\Domain\Schedule\Game;
use \DAG\Domain\Schedule\Field;

/**
 * Class Controller_Api_Swap
 *
 * @brief Swap the game for two GameTimes
 */
class Controller_Api_Swap extends Controller_Api_Base
{
    /** @var int */
    private $gameTimeId1;

    /** @var int */
    private $gameTimeId2;

    public function __construct()
    {
        parent::__construct();

        if (isset($_SERVER['REQUEST_METHOD']) and $_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->gameTimeId1 = $this->getPostAttribute(
                View_Base::GAME_TIME_ID1,
                null,
                true,
                true);

            $this->gameTimeId2 = $this->getPostAttribute(
                View_Base::GAME_TIME_ID2,
                null,
                true,
                true);
        }
    }

    /**
     * @brief On POST, complete the transaction and then render the page (with error message if any)
     */
    public function process()
    {
        Precondition::isTrue($this->m_isAuthenticated, 'Sorry dude, you are not authenticated');
        $errorMessage = '';

        // Get GameTimes
        $gameTime1 = GameTime::lookupById($this->gameTimeId1);
        $gameTime2 = GameTime::lookupById($this->gameTimeId2);

        // Verify at least one game exists
        if (!isset($gameTime1->game) and !isset($gameTime2->game)) {
            print "FAILURE: gameTimeId1: $this->gameTimeId1 and gameTimeId2:$this->gameTimeId2 do not have games";
            return;
        }

        // Verify schedule is not published
        $schedule1 = isset($gameTime1->game) ? $gameTime1->game->flight->schedule : null;
        $schedule2 = isset($gameTime2->game) ? $gameTime2->game->flight->schedule : null;
        if ((isset($schedule1) and $schedule1->published == 1)
            or (isset($schedule2) and $schedule2->published == 1)) {
            print "FAILURE: schedule has been published, you must unpublish before you can move a game";
            return;
        }

        // Verify moving in same day
        if (!$this->gameDaysMatch($gameTime1, $gameTime2, $errorMessage)) {
            print $errorMessage;
            return;
        }

        // Verify neither game is locked
        $game1 = isset($gameTime1->game) ? $gameTime1->game : null;
        $game2 = isset($gameTime2->game) ? $gameTime2->game : null;
        if ($this->isGameLocked($game1, $errorMessage)
            or $this->isGameLocked($game2, $errorMessage)) {
            print $errorMessage;
            return;
        }

        // Verify both games allowed on fields
        $field1 = $gameTime1->field;
        $field2 = $gameTime2->field;
        if (!$this->isGameAllowedOnField($game1, $field2, $errorMessage)) {
            print $errorMessage;
            return;
        }
        if (!$this->isGameAllowedOnField($game2, $field1, $errorMessage)) {
            print $errorMessage;
            return;
        }

        // Move (or swap) games
        $gameTime1->game = null;
        $gameTime2->game = null;
        if (isset($game1)) {
            $game1->gameTime = $gameTime2;
        }

        if (isset($game2)) {
            $game2->gameTime = $gameTime1;
        }
        $gameTime1->game = $game2;
        $gameTime2->game = $game1;

        print "SUCCESS: gameTimeId1: $this->gameTimeId1, gameTimeId2: $this->gameTimeId2";
    }

    /**
     * Return true if both gameDays for gameTime match.  Otherwise return false and set the errorMessage
     *
     * @param GameTime  $gameTime1
     * @param GameTime  $gameTime2
     * @param string    $errorMessage
     * @return bool
     */
    private function gameDaysMatch($gameTime1, $gameTime2, &$errorMessage)
    {
        if ($gameTime1->gameDate->day != $gameTime2->gameDate->day) {
            $day1 = $gameTime1->gameDate->day;
            $day2 = $gameTime2->gameDate->day;
            $errorMessage = "FAILURE: Cannot move across day boundaries: day1: $day1, day2: $day2";
            return false;
        }

        return true;
    }

    /**
     * Verify that game (if set) is not locked
     *
     * @param Game      $game
     * @param string    $errorMessage
     *
     * @return bool
     */
    private function isGameLocked($game, &$errorMessage)
    {
        if (isset($game) and $game->isLocked()) {
            $errorMessage = "FAILURE: Cannot move a locked game.  Unlock the game first";
            return true;
        }

        return false;
    }

    /**
     * Return true if game not set or game allowed on field; Otherwise return false and set the error message
     *
     * @param Game      $game
     * @param Field     $field
     * @param string    $errorMessage
     *
     * @return bool
     */
    private function isGameAllowedOnField($game, $field, &$errorMessage)
    {
        if (isset($game)) {
            $divisionField = null;
            if (!DivisionField::findByDivisionAndField($game->flight->schedule->division, $field, $divisionField)) {
                $divisionName = $game->flight->schedule->division->nameWithGender;
                $fieldName = $field->fullName;
                $errorMessage = "FAILURE: $divisionName not allowed to play on $fieldName";
                return false;
            }
        }

        return true;
    }
}