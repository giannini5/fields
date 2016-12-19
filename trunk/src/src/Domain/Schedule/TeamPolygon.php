<?php

namespace DAG\Domain\Schedule;

use \DAG\Framework\Exception\Assertion;

/**
 * Class TeamPolygon
 * 
 * Represents an event number of teams as a Polygon for game scheduling.
 * See //https://nrich.maths.org/1443 for more information.
 */
class TeamPolygon
{
    private $points;
    private $pointPairings      = [];
    private $pointAssignments   = [];
    private $pairingCount       = 0;

    public function __construct($teams)
    {
        Assertion::isTrue(count($teams) % 2 == 0, "Number of teams must be an even number: " . count($teams));

        $this->points = count($teams);
        for ($i = 0; $i < $this->points/2; $i++) {
            // For 6 points
            // 1 => 8, 2 => 7, 3 => 6, 4 => 5
            $this->pointPairings[$i+1] = $this->points - $i;
        }

        for ($i = 1; $i <= $this->points; $i++) {
            $this->pointAssignments[$i] = $i-1;
        }
    }

    /**
     * Get team pairings based on polygon point team assignments.
     *
     * @return int[] indexTeam1 => indexTeam2 where index is the index into the $teams array passed to constructor
     */
    public function getTeamPairings() {
        $this->pairingCount += 1;
        $parings = [];
        foreach ($this->pointPairings as $point1 => $point2) {
            if ($point2 == $this->points and ($this->pairingCount % 2) == 0) {
                $parings[$this->pointAssignments[$point2]] = $this->pointAssignments[$point1];
            } else {
                $parings[$this->pointAssignments[$point1]] = $this->pointAssignments[$point2];
            }
        }
        return $parings;
    }

    /**
     * Shirt the pointAssignments on the outer edges of the Polygon to prepare for next team pairings
     */
    public function shift() {
        $prior = $this->pointAssignments[$this->points - 1];
        for ($i = 1; $i < $this->points; $i++) {
            $current                    = $this->pointAssignments[$i];
            $this->pointAssignments[$i] = $prior;
            $prior                      = $current;
        }
    }
}