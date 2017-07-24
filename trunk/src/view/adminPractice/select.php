<?php

/**
 * @brief Select the Facility/Field for the reservation.
 *
 * @param $controller - Controller that contains data used when rendering this view.
 */
class View_AdminPractice_Select extends View_Fields_SelectField {

    /**
     * @brief Construct the Admin Select Field View
     *
     * @param $controller - Controller that contains data used when rendering this view.
     */
    public function __construct($controller) {
        $navigation = new View_AdminPractice_Navigation($controller, self::ADMIN_SELECT_FIELD_PAGE);
        parent::__construct($controller, self::ADMIN_SELECT_FIELD_PAGE, $navigation, true);
    }

    /**
     * @brief Render data for display on the page.
     */
    public function renderPage() {
        parent::renderPage();
    }
}