<?php

use \DAG\Framework\Exception\Precondition;
use \DAG\Domain\Schedule\Game;

/**
 * Class Controller_Api_Toggle
 *
 * @brief Toggle the lock setting for a game
 */
class Controller_Api_Toggle extends Controller_Api_Base
{
    /** @var int */
    private $gameId;

    public function __construct()
    {
        parent::__construct();

        if (isset($_SERVER['REQUEST_METHOD']) and $_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->gameId = $this->getPostAttribute(
                View_Base::GAME_ID,
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
        $game = Game::lookupById($this->gameId);

        // Verify schedule is not published
        $schedule = $game->flight->schedule;
        if ($schedule->isPublished()) {
            print "FAILURE: schedule has been published, you must unpublish before you can toggle a games lock";
            return;
        }

        // Toggle the game lock
        $lockMessage = $game->isLocked() ? "Unlocked" : "Locked";
        $game->locked = $game->isLocked() ? 0 : 1;

        print "SUCCESS: gameId: $this->gameId $lockMessage";
    }
}