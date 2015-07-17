<?php

/**
 * @brief Show the current reservation (if any) for the coach/team.
 */
class View_ShowReservation extends View_Base {
    /**
     * @brief Construct he View
     *
     * @param $controller - Controller that contains data used when rendering this view.
     */
    public function __construct($controller) {
        parent::__construct(self::SHOW_RESERVATION_PAGE, $controller);
    }

    /**
     * @brief Render data for display on the page.
     */
    public function render() {
        print "Uh, here's your schedule!!! </br>";
        print "<a href='selectFacility'>Select Facility</a></br>";
        print "<a href='welcome'>Home</a></br>";
    }

}