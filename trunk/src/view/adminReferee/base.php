<?php

use \DAG\Domain\Schedule\Referee;
use \DAG\Domain\Schedule\DivisionReferee;

/**
 * @param Referee $a
 * @param Referee $b
 * @return int
 */
function compareName($a, $b)
{
    $value = strcmp($a->name, $b->name);
    return $value;
}

/**
 * @param DivisionReferee $a
 * @param DivisionReferee $b
 * @return int
 */
function compareDivisionDisplayOrder($a, $b)
{
    if ($a->division->displayOrder == $b->division->displayOrder) {
        return 0;
    }

    return $a->division->displayOrder > $b->division->displayOrder ? 1 : -1;
}

/**
 * @brief: Abstract base class for all adminReferee views.
 */
abstract class View_AdminReferee_Base extends View_Base {

    /**
     * @brief: Construct a new instance of this base class.
     *
     * @param string            $page       - Name of the page being constructed.
     * @param Controller_Base   $controller - Controller that contains data used when rendering this view.
     */
    public function __construct($page, $controller)
    {
        $navigation         = new View_AdminReferee_Navigation($controller, $page);
        parent::__construct($navigation, $page, "Administer Referees", $controller, 0);
    }
}