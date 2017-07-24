<?php

/**
 * @brief Display the specified image if it exists
 */
class View_Fields_Image extends View_Fields_Base {
    /**
     * @brief Construct he View
     *
     * @param Controller_Base   $controller - Controller that contains data used when rendering this view.
     * @param string            $page       - Page name
     */
    public function __construct($controller, $page = self::IMAGE_PAGE) {
        parent::__construct($page, $controller);
    }

    /**
     * @brief Render data for display on the page.
     */
    public function renderPage() {
        $result = strpos($this->m_controller->m_image, 'http://');

        if (is_bool($result)) {
            $image = 'images/' . $this->m_controller->m_image;
            $srcImage = SRC . "$image";
            if (!file_exists($srcImage)) {
                $image = 'dave.jpg';
            }

        } else {
            $image = $this->m_controller->m_image;
        }

        print "
            <img src='$image' alt='Sorry, image $image not found'>";
    }
}