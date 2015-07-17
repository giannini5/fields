<?php

/**
 * @brief Select the Days of Week and the Times of day for the reservation.
 *
 * @param $controller - Controller that contains data used when rendering this view.
 */
class View_SelectDayTime extends View_Base {
    public function __construct($controller) {
        parent::__construct(self::SELECT_DAY_TIME_PAGE, $controller);
    }

    /**
     * @brief Render data for display on the page.
     */
    public function render() {
        print "Select your days and times!!! </br>";
        print "<a href='selectFacility'>Select Facility</a></br>";
        print "<a href='welcome'>Home</a></br>";
    }
}