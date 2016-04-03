<?php

/**
 * Class Controller_Image
 *
 * @brief Controller to render a specified image
 */
class Controller_Fields_Image extends Controller_Fields_Base {

    public $m_image;

    public function __construct() {
        parent::__construct();

        $this->m_image = isset($_REQUEST[View_Base::IMAGE]) ? $_REQUEST[View_Base::IMAGE] : '';
    }

    /**
     * @brief On GET, render the page to ask user for create account attributes
     */
    public function process() {
        $view = new View_Fields_Image($this);
        $view->displayPage();
    }
}