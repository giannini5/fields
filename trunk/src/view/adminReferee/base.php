<?php

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