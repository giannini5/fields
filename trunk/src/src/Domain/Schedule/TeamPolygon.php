<?php

namespace DAG\Domain\Schedule;

use \DAG\Framework\Exception\Assertion;
use \DAG\Framework\Exception\Precondition;

/**
 * Class TeamPolygon
 * 
 * Represents an event number of teams as a Polygon for game scheduling.
 * See https://nrich.maths.org/1443 for more information.
 */
class TeamPolygon
{
    const ROUND_ROBIN_EVEN  = 'roundRobinEven'; // Even number of teams
    const ROUND_ROBIN_ODD   = 'roundRobinOdd';  // Odd number of teams, one team plays two games each round
    const CROSS_POOL_EVEN   = 'crossPoolEven';  // Same number of teams in each pool w/ cross pool play
    const CROSS_POOL_ODD    = 'crossPoolOdd';   // Different number of teams in each pool (by one team).
                                                // One team in pool with less teams plays two games each round

    private $points;
    private $pointPairings      = [];
    private $pointAssignments   = [];
    private $poolType;

    public function __construct($teams, $poolType = self::ROUND_ROBIN_EVEN, $crossPoolTeams = null)
    {
        $this->poolType     = $poolType;
        $this->points       = count($teams);

        // Set up pairings based on pool type
        switch ($this->poolType) {
            case self::ROUND_ROBIN_EVEN:
                Precondition::isTrue(count($teams) % 2 == 0, "Number of teams must be an even number: " . count($teams));
                Precondition::isTrue(!isset($crossPoolTeams), "Round Robin pool type does not support cross pool teams");

                for ($i = 0; $i < $this->points/2; $i++) {
                    // For 6 points
                    // 1 => 8, 2 => 7, 3 => 6, 4 => 5
                    $this->pointPairings[$i+1] = $this->points - $i;
                }
                break;

            case self::ROUND_ROBIN_ODD:
                Precondition::isTrue(count($teams) % 2 == 1, "Number of teams must be an odd number: " . count($teams));
                Precondition::isTrue(!isset($crossPoolTeams), "Round Robin pool type does not support cross pool teams");

                $this->pointPairings[1] = floor(($this->points / 2) + 1); // Second game pairing
                for ($i = 1; $i <= floor($this->points / 2); $i++) {
                    // For 5 points
                    // 1 => 3, 2 => 5, 3 => 4
                    $this->pointPairings[$i+1] = $this->points - $i + 1;
                }
                break;

            case self::CROSS_POOL_EVEN:
                Precondition::isTrue(isset($crossPoolTeams), "Cross Pool pool type requires cross pool teams");

                $teamCount          = count($teams);
                $crossPoolTeamCount = count($crossPoolTeams);
                Precondition::isTrue($teamCount == $crossPoolTeamCount, "Count of teams ($teamCount) must equal count of cross-pool teams ($crossPoolTeamCount)");
                Precondition::isTrue($teamCount == $crossPoolTeamCount, "Count of teams ($teamCount) must equal count of cross-pool teams ($crossPoolTeamCount)");

                for ($i = 1; $i <= $this->points; $i++) {
                    // For 5 points - cross pool pairings
                    // 1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5
                    $this->pointPairings[$i] = $i;
                }
                break;

            default:
                Precondition::isTrue(false, "$this->poolType is not yet supported");
        }

        // Initialize polygon point assignments (1-based points, w/ 0-based team numbers)
        for ($i = 1; $i <= $this->points; $i++) {
            $this->pointAssignments[$i] = $i-1;
        }
    }

    /**
     * Get team pairings based on polygon point team assignments.
     *
     * @return int[] indexTeam1 => indexTeam2 where indexTeam1 is the index into the $teams array passed to constructor
     *                             and indexTeam2 is the index into the teams array (or crossPoolTeams array)
     */
    public function getTeamPairings() {
        $parings = [];

        switch ($this->poolType) {
            case self::ROUND_ROBIN_EVEN:
            case self::ROUND_ROBIN_ODD:
                foreach ($this->pointPairings as $point1 => $point2) {
                        $parings[$this->pointAssignments[$point1]] = $this->pointAssignments[$point2];
                }
                break;

            case self::CROSS_POOL_EVEN:
                foreach ($this->pointPairings as $point1 => $point2) {
                    $parings[$this->pointAssignments[$point1]] = $point2 - 1;
                }
                break;

            default:
                Precondition::isTrue(false, "$this->poolType is not yet supported");
        }

        return $parings;
    }

    /**
     * Shirt the pointAssignments on the outer edges of the Polygon to prepare for next team pairings
     */
    public function shift() {
        switch ($this->poolType) {
            case self::ROUND_ROBIN_EVEN:
                $prior = $this->pointAssignments[$this->points - 1];
                for ($i = 1; $i < $this->points; $i++) {
                    $current = $this->pointAssignments[$i];
                    $this->pointAssignments[$i] = $prior;
                    $prior = $current;
                }
                break;

            case self::ROUND_ROBIN_ODD:
                $prior = $this->pointAssignments[$this->points];
                for ($i = 1; $i <= $this->points; $i++) {
                    $current = $this->pointAssignments[$i];
                    $this->pointAssignments[$i] = $prior;
                    $prior = $current;
                }
                break;

            case self::CROSS_POOL_EVEN:
                $prior = $this->pointAssignments[$this->points];
                for ($i = 1; $i <= $this->points; $i++) {
                    $current = $this->pointAssignments[$i];
                    $this->pointAssignments[$i] = $prior;
                    $prior = $current;
                }
                break;

            default:
                Precondition::isTrue(false, "$this->poolType is not yet supported");
        }
    }
}