<?php

/**
 * @brief: Abstract base class for all schedule views.
 */
abstract class View_Games_Base extends View_Base {

    /**
     * @brief: Construct a new instance of this base class.
     *
     * @param string            $page       - Name of the page being constructed.
     * @param Controller_Base   $controller - Controller that contains data used when rendering this view.
     */
    public function __construct($page, $controller)
    {
        $navigation = new View_Games_Navigation($controller, $page);
        parent::__construct($navigation, $page, "Schedules and Standings", $controller);
    }
}