<?php

use \DAG\Framework\Exception\Precondition;
use \DAG\Domain\Schedule\GameTime;

/**
 * Class Controller_Api_ToggleGameTime
 *
 * @brief Toggle the lock setting for a game
 */
class Controller_Api_ToggleGameTime extends Controller_Api_Base
{
    /** @var int */
    private $gameTimeId;

    public function __construct()
    {
        parent::__construct();

        if (isset($_SERVER['REQUEST_METHOD']) and $_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->gameTimeId = $this->getPostAttribute(
                View_Base::GAME_TIME_ID,
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

        // Get Game
        $gameTime = GameTime::lookupById($this->gameTimeId);

        // Verify no game associated with gameTime
        if (isset($gameTime->game)) {
            $lockMessage = $gameTime->game->isLocked() ? "Unlocked" : "Locked";
            $gameTime->game->locked = $gameTime->isLocked() ? 0 : 1;
            print "SUCCESS: gameId: $gameTime->game->id $lockMessage";
            return;
        }

        // Toggle the game time lock
        $lockMessage = $gameTime->isLocked() ? "Unlocked" : "Locked";
        $gameTime->locked = $gameTime->isLocked() ? 0 : 1;

        print "SUCCESS: gameTimeId: $this->gameTimeId $lockMessage";
    }
}