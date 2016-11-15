<?php

/**
 * Class Controller_Schedules_Team
 *
 * @brief Select a field to administer or create a new team
 */
class Controller_Schedules_Team extends Controller_Schedules_Base {

    public $teams = [];

    public function __construct() {
        parent::__construct();

        if (isset($_SERVER['REQUEST_METHOD']) and $_SERVER['REQUEST_METHOD'] == 'POST') {
            if(isset($_POST[View_Base::SUBMIT]) and ($_POST[View_Base::SUBMIT] == View_Base::UPLOAD_FILE)) {
                $this->m_operation = View_Base::UPLOAD_FILE;
            }
        }
    }

    /**
     * @brief On GET, render the page to administer seasons
     *        On POST, complete the transaction and then render the page (with error message if any)
     */
    public function process() {
        if ($this->m_missingAttributes == 0) {
            switch ($this->m_operation) {
                case View_Base::UPLOAD_FILE:
                    $fileData = $this->_getFileData();
                    $this->m_season->populateDivisions($fileData);
                    break;

                case View_Base::UPDATE:
                    // TODO
                    break;
            }
        }

        if ($this->m_isAuthenticated) {
            $view = new View_Schedules_Team($this);
        } else {
            $view = new View_Schedules_Home($this);
        }

        $view->displayPage();
    }

    /**
     * @return string $fileData
     */
    private function _getFileData()
    {
        $fileName = $_FILES["fileToUpload"]["tmp_name"];
        // $fileType = pathinfo($fileName, PATHINFO_EXTENSION);

        $handle = fopen($fileName, "r");
        \DAG\Framework\Exception\Precondition::isTrue($handle != false, "Unable to open file: $fileName");

        $fileData = '';
        $data = fread($handle, 1024);
        while ($data) {
            $fileData .= $data;
            $data = fread($handle, 1024);
        }
        fclose($handle);

        return $fileData;
    }

    /**
     * @brief Create Team
     */
    private function _createTeam() {
        // TODO
    }

    /**
     * @brief Update Team
     */
    private function _updateTeam() {
        // TODO
    }
}