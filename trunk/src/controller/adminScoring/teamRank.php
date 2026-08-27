<?php

use \DAG\Domain\Schedule\Division;
use \DAG\Domain\Schedule\Team;
use \DAG\Framework\Exception\Precondition;

/**
 * Class Controller_AdminScoring_Home
 *
 * @brief Controller for team rank
 */
class Controller_AdminScoring_TeamRank extends Controller_AdminScoring_Base
{
    const TEAM_RANK = 'teamRank';

    public $m_thirdPartyId;
    public $m_rank;
    private $m_countUpdated = 0;

    public function __construct()
    {
        parent::__construct();

        if (isset($_SERVER['REQUEST_METHOD']) and $_SERVER['REQUEST_METHOD'] == 'POST') {
            if ($this->m_operation == View_Base::SUBMIT) {
                $this->m_email = $this->getPostAttribute(
                    Model_Fields_CoachDB::DB_COLUMN_EMAIL,
                    '* Email Address is required'
                );
                $this->m_password = $this->getPostAttribute(
                    Model_Fields_CoachDB::DB_COLUMN_PASSWORD,
                    '* Password is required'
                );
            }

            if(isset($_POST[View_Base::SUBMIT]) and ($_POST[View_Base::SUBMIT] == View_Base::UPLOAD_FILE)) {
                $this->m_operation = View_Base::UPLOAD_FILE;
            }
        }
    }

    /**
     * @brief On GET, render the page to administer seasons
     *        On POST, complete the transaction and then render the page (with error message if any)
     */
    public function process()
    {
        switch ($this->m_operation) {
            case View_Base::SUBMIT:
                $this->_login();
                break;

            case View_Base::SIGN_OUT:
                $this->signOut();
                break;

            case View_Base::UPLOAD_FILE:
                $fileData = $this->_getFileData();
                if (isset($this->m_season)) {
                    $this->processTeamRank($fileData);
                } else {
                    $this->m_errorString = 'Unable to find an enabled Season.  Click on SEASON tab first to create/enable a Season';
                }
                break;

            case View_Base::SIGN_IN:
            default:
                break;
        }

        // Display Home page with error message if login failed
        // or successful login with next steps for administration
        $view = new View_AdminScoring_TeamRank($this);
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
        Precondition::isTrue($handle != false, "Unable to open file: $fileName");

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
     * Update team rank for teams
     * @param string $fileData
     * @return void
     */
    private function processTeamRank($fileData)
    {
        $lines = explode("\n", $fileData);
        $count = 0;
        foreach ($lines as $line) {
            if ($count == 0) {
                # Skip the header line
                $count++;
                continue;
            }
            if (empty($line)) {
                # Skip the empty line
                continue;
            }

            $data = explode(",", $line);
            $thirdPartyId = $data[0];
            $rank = $data[1];
            $team = Team::lookupByThirdPartyId($thirdPartyId);
            $team->rank = $rank;
            $count++;
        }
        $this->m_countUpdated = count($lines);
        $this->m_messageString = $this->m_countUpdated > 0 ? "Team Rank Updated for $this->m_countUpdated teams" : "";
    }
}
