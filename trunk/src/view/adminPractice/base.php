<?php

/**
 * @brief: Abstract base class for all adminPractice views.
 */
abstract class View_AdminPractice_Base extends View_Base {

    /**
     * @brief: Construct a new instance of this base class.
     *
     * @param string            $page       - Name of the page being constructed.  Must be defined as a const
     *                                          in the above list.
     * @param Controller_Base   $controller - Controller that contains data used when rendering this view.
     */
    public function __construct($page, $controller)
    {
        $navigation         = new View_AdminPractice_Navigation($controller, $page);
        $collapsibleCount   = count($controller->getFacilities());

        parent::__construct($navigation, $page, "Administer Practice Fields", $controller, $collapsibleCount);
    }
}