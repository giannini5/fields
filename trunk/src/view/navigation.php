<?php

/**
 * @brief: Abstract navigation class for all views.
 *          - Abstract methods must be implemented by child classes
 */
abstract class View_Navigation
{
    /** @var Controller_Base */
    protected $controller;

    /** @var string */
    protected $pageName;

    /**
     * @param Controller_Base   $controller
     * @param string            $pageName
     */
    public function __construct($controller, $pageName)
    {
        $this->controller   = $controller;
        $this->pageName     = $pageName;
    }

    /**
     * @brief Display HTML start and header
     *
     * @param View_Styles   $styles             - CSS
     * @param string        $title              - page title
     * @param int           $collapsibleCounter - defaults to 0
     */
    public function displayHeader($styles, $title, $collapsibleCounter = 0)
    {
        print "
            <head>
                <title>$title</title>
                <script type='text/JavaScript' src='../js/scw.js'></script>";

        $styles->render($collapsibleCounter);

        print "
            </head>";
    }

    /**
     * @brief Display Body Header (section above navigation)
     *
     * @param string            $urlParams
     */
    abstract public function displayBodyHeader($urlParams);

    /**
     * @brief Display Header Navigation
     */
    abstract public function displayNavigation();
}
