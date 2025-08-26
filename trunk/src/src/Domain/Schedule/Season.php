<?php

namespace DAG\Domain\Schedule;

use DAG\Domain\Domain;
use DAG\Framework\Exception\Assertion;
use DAG\Orm\Schedule\RefereeOrm;
use DAG\Orm\Schedule\SeasonOrm;
use DAG\Orm\Schedule\ScheduleOrm;
use DAG\Framework\Exception\Precondition;
use DAG_Exception;


/**
 * @property int    $id
 * @property League $league
 * @property string $name
 * @property string $startDate
 * @property string $endDate
 * @property string $startTime
 * @property string $endTime
 * @property string $daysOfWeek
 * @property int    $enabled
 */
class Season extends Domain
{
    public static $phoneNumbersToSkip = ['000-000-0000', '555-555-5555', '805-111-1111', ''];

    /** @var SeasonOrm */
    private $seasonOrm;

    /** @var League */
    private $league;

    /**
     * @param SeasonOrm $seasonOrm
     * @param League    $league (defaults to null)
     */
    protected function __construct(SeasonOrm $seasonOrm, $league = null)
    {
        $this->seasonOrm = $seasonOrm;
        $this->league = isset($league) ? $league : League::lookupById($seasonOrm->leagueId);
    }

    /**
     * @param League $league
     * @param string $name
     * @param string $startDate (SQL Format)
     * @param string $endDate (SQL Format)
     * @param string $startTime (SQL Format)
     * @param string $endTime (SQL Format)
     * @param string $daysOfWeek defaults to "0000011" (Saturday, Sunday only)
     * @param int    $enabled (defaults to 1)
     *
     * @return Season
     */
    public static function create(
        $league,
        $name,
        $startDate,
        $endDate,
        $startTime,
        $endTime,
        $daysOfWeek = "0000011",
        $enabled = 1)
    {
        Precondition::isTrue($startDate < $endDate, "StartDate ($startDate) must be less than EndDate ($endDate)");

        $seasonOrm = SeasonOrm::create($league->id, $name, $startDate, $endDate, $startTime, $endTime, $daysOfWeek, $enabled);
        return new static($seasonOrm, $league);
    }

    /**
     * @param int $seasonId
     *
     * @return Season
     */
    public static function lookupById($seasonId)
    {
        $seasonOrm = SeasonOrm::loadById($seasonId);
        return new static($seasonOrm);
    }

    /**
     * @param League $league
     * @param string $name
     *
     * @return Season
     */
    public static function lookupByName($league, $name)
    {
        Precondition::isNonEmpty($name, 'name should not be empty');
        $seasonOrm = SeasonOrm::loadByLeagueIdAndName($league->id, $name);
        return new static($seasonOrm, $league);
    }

    /**
     * @param League $league
     *
     * @return array Seasons
     */
    public static function lookupByLeague($league)
    {
        $seasons = [];

        $seasonOrms = SeasonOrm::loadByLeagueId($league->id);
        foreach ($seasonOrms as $seasonOrm) {
            $seasons[] = new static($seasonOrm, $league);
        }

        return $seasons;
    }

    /**
     * @param $propertyName
     * @return int|string
     */
    public function __get($propertyName)
    {
        switch ($propertyName) {
            case "id":
            case "name":
            case "startDate":
            case "endDate":
            case "startTime":
            case "endTime":
            case "daysOfWeek":
            case "enabled":
                return $this->seasonOrm->{$propertyName};

            case "league":
                return $this->{$propertyName};

            default:
                Precondition::isTrue(false, "Unrecognized property: $propertyName");
                return false;
        }
    }

    /**
     * @param $propertyName
     * @param $value
     */
    public function __set($propertyName, $value)
    {
        try {
            switch ($propertyName) {
                case "startDate":
                    Precondition::isTrue($value < $this->endDate, "StartDate ($value) must be less than EndDate ($this->endDate)");
                    if ($this->seasonOrm->{$propertyName} != $value) {
                        $this->seasonOrm->{$propertyName} = $value;
                        $this->seasonOrm->save();
                    }
                    break;

                case "endDate":
                    Precondition::isTrue($this->startDate < $value, "StartDate ($this->startDate) must be less than EndDate ($value)");
                    if ($this->seasonOrm->{$propertyName} != $value) {
                        $this->seasonOrm->{$propertyName} = $value;
                        $this->seasonOrm->save();
                    }
                    break;

                case "name":
                case "startTime":
                case "endTime":
                case "daysOfWeek":
                case "enabled":
                    if ($this->seasonOrm->{$propertyName} != $value) {
                        $this->seasonOrm->{$propertyName} = $value;
                        $this->seasonOrm->save();
                    }
                    break;

                default:
                    Precondition::isTrue(false, "Unrecognized property: $propertyName");
                    break;
            }
        } catch (\Exception $e) {
            var_dump($e);
        }

    }

    /**
     * Populate Divisions, Teams, Coaches, Assistant Coaches
     *
     * @param string $data - Expected format:
     *      Approved,Team,Type,eAYSO Vol App,AYSO ID,Name,Phone,Cell,Email,Certifications
     *      Multi line data where fields are comma separated.  Example:
     *      Yes,U12G-3,Coach,,58222441,Raul Baez,805-845-8124,805-680-1687,familyrb805@yahoo.com,"Intermediate Coach,Safe Haven Coach,U-12 Coach,Needs training"
     *
     *      where:
     *          Approved is ignored
     *          Team is of the format <Division><Gender>-<TeamNumber>.  For example: U6G-5
     *          Type is "Coach" or "Asst"
     *          eAYSO Vol App is ignored
     *          AYSO ID is ignored
     *          Name is coaches name
     *          Phone is coaches phone number
     *          Cell is coaches phone number
     *          Email is coaches email address
     *          Certifications is ignored
     *
     * @param bool  $ignoreHeaderRow - defaults to true
     */
    public function populateDivisions_old($data, $ignoreHeaderRow = true )
    {
        $line = '';

        try {
            $lines = explode("\n", $data);
            $processedLines = 0;

            foreach ($lines as $line) {
                $processedLines += 1;

                // Skip first line if requested
                if ($processedLines == 1 and $ignoreHeaderRow) {
                    continue;
                }

                // Skip empty lines
                if (empty(trim($line))) {
                    continue;
                }

                $fields = explode(',', $line);
                Assertion::isTrue(count($fields) >= 10, "Invalid line: $line");

                $teamNameAttributes = explode('-', $fields[1]);
                if (count($teamNameAttributes) == 3) {
                    $teamNameAttributes = explode('--', $fields[1]);
                }
                Assertion::isTrue(count($teamNameAttributes) == 2, "Invalid team name: $fields[1]");

                $teamName               = sprintf('%s-%02d', $teamNameAttributes[0], $teamNameAttributes[1]);
                $gender                 = (strstr($teamName, 'B') == false) ? "Girls" : "Boys";
                $divisionName           = str_replace('B', '', str_replace('G', '', $teamNameAttributes[0]));
                $displayOrder           = $this->getDivisionDisplayOrder($divisionName);
                $gameDurationMinutes    = $this->getGameDurationMinutes($divisionName);
                $maxPlayersPerTeam      = $this->getMaxPlayersPerTeam($divisionName);

                $name           = $fields[5];
                $email          = $fields[8];
                $phone1         = in_array($fields[6], self::$phoneNumbersToSkip) ? '' : $fields[6];
                $phone2         = in_array($fields[7], self::$phoneNumbersToSkip) ? '' : $fields[7];

                // Do not store the same phone number a second time for a  coach
                $phone2 = ($phone1 == $phone2) ? '' : $phone2;

                // Skip teams where team number is 0
                if ($teamNameAttributes[1] == 0) {
                    continue;
                }

                // Skip teams where there is no coach
                if (empty($name)) {
                    continue;
                }

                // Create division, team and coach or assistant coach
                $division       = Division::create($this, $divisionName, $gender, $maxPlayersPerTeam, $gameDurationMinutes, $displayOrder, true);
                $team           = Team::create($division, null, $teamName, '', '', '', true);

                switch (strtolower($fields[2])) {
                    case 'coach':
                        Coach::create($team, null, $name, $email, $phone1, $phone2, true);
                        break;
                    default:
                        AssistantCoach::create($team, null, $name, $email, $phone1, $phone2, true);
                        break;
                }
            }
        } catch (\Exception $e) {
            print ("Error: Invalid line in uploaded file: '$line'<br>" . $e->getMessage());
        }
    }

    /**
     * Populate Divisions, Teams, Coaches, Assistant Coaches
     *
     * @param string $data - Expected format:
     *      TeamName,TeamId,Region,City,Division,Gender,CoachType,CoachName,CoachPhone,CoachCell,CoachEmail
     *      Multi line data where fields are comma separated.  Example:
     *      Rattlesnakes,U12G-3,122,Santa Barbara,U10,G,Coach,Raul Baez,805-845-8124,805-680-1687,familyrb805@yahoo.com
     *
     *      where:
     *          CoachType is "Coach" or "Asst"
     *
     * @param bool  $ignoreHeaderRow - defaults to true
     */
    public function populateDivisions($data, $ignoreHeaderRow = true )
    {
        $line = '';

        try {
            $lines = explode("\n", $data);
            $processedLines = 0;

            foreach ($lines as $line) {
                $processedLines += 1;

                // Skip first line if requested
                if ($processedLines == 1 and $ignoreHeaderRow) {
                    continue;
                }

                // Skip empty lines
                if (empty(trim($line))) {
                    continue;
                }

                $fields = explode(',', $line);
                Assertion::isTrue(count($fields) >= 11, "Invalid line: $line");

                $teamName       = $fields[0];
                $teamId         = $fields[1];
                $region         = $fields[2];
                $city           = $fields[3];
                $divisionName   = $fields[4];
                $gender         = $fields[5] == 'B' ? "Boys" : "Girls";
                $coachType      = $fields[6];
                $coachName      = $fields[7];
                $coachPhone     = in_array($fields[8], self::$phoneNumbersToSkip) ? '' : $fields[8];
                $coachCell      = in_array($fields[9], self::$phoneNumbersToSkip) ? '' : $fields[9];
                $coachEmail     = $fields[10];

                $displayOrder           = $this->getDivisionDisplayOrder($divisionName);
                $gameDurationMinutes    = $this->getGameDurationMinutes($divisionName);
                $maxPlayersPerTeam      = $this->getMaxPlayersPerTeam($divisionName);

                // Do not store the same phone number a second time for a  coach
                $coachPhone = ($coachPhone == $coachCell) ? '' : $coachPhone;

                // Skip teams where there is no coach (or the team number is 0)
                $teamIdAttributes   = explode("-", $teamId);
                $teamNumber         = $teamIdAttributes[count($teamIdAttributes) - 1];
                unset($teamIdAttributes[count($teamIdAttributes) - 1]);
                // $teamId             = implode("-", $teamIdAttributes);
                // $teamId             = sprintf("%s-%02d", $teamId, $teamNumber);

                if (empty($teamName)) {
                    $teamName = $teamId;
                }

                if (empty($coachName) or $teamNumber == 0) {
                    continue;
                }

                // Create division, team and coach or assistant coach
                $division       = Division::create($this, $divisionName, $gender, $maxPlayersPerTeam, $gameDurationMinutes, $displayOrder, true);
                $team           = Team::create($division, null, $teamName, $teamId, $region, $city, true);

                switch (strtolower($coachType)) {
                    case 'coach':
                        Coach::create($team, null, $coachName, $coachEmail, $coachPhone, $coachCell, true);
                        break;
                    default:
                        AssistantCoach::create($team, null, $coachName, $coachEmail, $coachPhone, $coachCell, true);
                        break;
                }

                // Create Assistant coach only (division and team must already exist)
                /*
                $division   = Division::lookupByNameAndGender($this, $divisionName, $gender);
                $team       = Team::lookupByNameId($division, $teamId);
                AssistantCoach::create($team, null, $coachName, $coachEmail, $coachPhone, $coachCell, true);
                */
            }
        } catch (\Exception $e) {
            print ("Error: Invalid line in uploaded file: '$line'<br>" . $e->getMessage());
        }
    }

    /**
     * @param $divisionName
     * @return int Order Division should be displayed
     */
    private function getDivisionDisplayOrder($divisionName)
    {
        switch ($divisionName) {
            case 'U5':
            case '5U':
            case '5U-2013':
                return 10;

            case 'U6':
            case '6U':
            case '6U-2012':
                return 20;

            case 'U7':
            case '7U':
            case '7U-2011':
                return 30;

            case 'U8':
            case '8U':
            case '8U-2010':
                return 40;

            case 'U9':
            case '9U':
                return 50;

            case 'U10':
            case '10U':
            case '10U-2009-8':
                return 60;

            case 'U11':
            case '11U':
                return 70;

            case 'U12':
            case '12U':
            case '12U-2007-6':
                return 80;

            case 'U13':
            case '13U':
                return 90;

            case 'U14':
            case '14U':
            case '14U-2005-4':
                return 100;

            case 'U15':
            case '15U':
                return 110;

            case 'U16':
            case '16U':
                return 120;

            case 'U17':
            case '17U':
                return 130;

            case 'U18':
            case '18U':
            case '18U-2003-0':
                return 140;

            case 'U19':
            case '19U':
                return 150;

            case 'U16/19':
                return 160;

            default:
                return 200;
        }
    }

    /**
     * Return the duration of the game, including half time and time needed after the game before the next game starts.
     *
     * @param string $divisionName
     *
     * @return int
     */
    private function getGameDurationMinutes($divisionName)
    {
        switch ($divisionName) {
            case 'U5':
            case 'U6':
            case 'U7':
            case 'U8':
            case '5U-2013':
            case '6U-2012':
            case '7U-2011':
            case '8U-2010':
            case '5U':
            case '6U':
            case '7U':
            case '8U':
                return 60;

            case 'U9':
            case 'U10':
            case '10U-2009-8':
            case '10U':
                return 75;

            case 'U11':
            case 'U12':
            case '12U':
            case '12U-2007-6':
                return 75;

            case 'U13':
            case 'U14':
            case 'U15':
            case 'U16':
            case 'U17':
            case 'U18':
            case 'U19':
            case 'U16/19':
            case '14U-2005-4':
            case '18U-2003-0':
            case '14U':
            case '18U':
            default:
                return 90;
        }
    }

    /**
     * Return the duration of the game, including half time and time needed after the game before the next game starts.
     *
     * @param string $divisionName
     *
     * @return int
     */
    private function getMaxPlayersPerTeam($divisionName)
    {
        switch ($divisionName) {
            case 'U5':
            case 'U6':
            case 'U7':
            case 'U8':
            case '5U-2013':
            case '6U-2012':
            case '7U-2011':
            case '8U-2010':
            case '5U':
            case '6U':
            case '7U':
            case '8U':
                return 10;

            case 'U9':
            case 'U10':
            case '10U-2009-8':
            case '10U':
                return 10;

            case 'U11':
            case 'U12':
            case '12U-2007-6':
            case '12U':
                return 14;

            case 'U13':
            case 'U14':
            case '14U':
            case '14U-2005-4':
                return 18;

            case 'U15':
            case 'U16':
            case 'U17':
            case 'U18':
            case 'U19':
            case 'U16/19':
            case '18U-2003-0':
            case '18U':
            default:
                return 22;
        }
    }

    /**
     * Populate Divisions, Teams, Players
     *
     * @param string $data - Expected format:
     *      Region, Division, Team, Status, ID, Name, Phone, RegDate, PreReg, Fee
     *      Multi line data where fields are comma separated
     *
     *      where:
     *          Region is ignored
     *          Division is of the format <Gender><Division>.  For example: GU6 or U6G
     *          Team is of the format <Number>
     *          Status is ignored
     *          ID is ignored
     *          Name is the player's name
     *          Phone is player's phone number
     *          RegDate is ignored
     *          PreReg is ignored
     *          Fee is ignored
     *
     * @param bool  $ignoreHeaderRow - defaults to true
     */
    public function populatePlayers($data, $ignoreHeaderRow = true)
    {
        $line = "";
        try {
            $lines = explode("\n", $data);
            $processedLines = 0;

            foreach ($lines as $line) {
                $processedLines += 1;
                if ($processedLines == 1 and $ignoreHeaderRow) {
                    continue;
                }

                $fields = explode(',', $line);
                Assertion::isTrue(count($fields) == 10, "Invalid line: $line");

                // Skip if team name is empty - registration that is is progress
                if (empty($fields[1])) {
                    continue;
                }

                // Skip if team number is 0 - no coach yet so team is not formed
                if (empty($fields[2]) or $fields[2] == 0) {
                    continue;
                }

                $divisionName   = str_replace('B', '', str_replace('G', '', $fields[1]));
                $gender         = (strstr($fields[1], 'B') == false) ? "Girls" : "Boys";
                $playerName     = ucfirst(str_replace(';', ',', $fields[5]));
                $division       = Division::lookupByNameAndGender($this, $divisionName, $gender);

                if (isset($division)) {
                    $teamIdPrefix = $this->getTeamIdPrefixFromDivision($division);
                    # $teamId = sprintf('%s-%d', $teamIdPrefix, $fields[2]);
                    $teamId = sprintf('%s-%02d', $teamIdPrefix, $fields[2]);
                    $team = Team::lookupByNameId($division, $teamId);
                    if (isset($team)) {
                        Player::create($team, null, $playerName, '', $fields[6], true);
                    }
                }
            }
        } catch (\Exception $e) {
            print ("Error: Invalid line in uploaded file: '$line'<br>" . $e->getMessage());
        }
    }

    /**
     * @param Division  $division
     * @return string
     */
    private function getTeamIdPrefixFromDivision($division)
    {
        $teamIdPrefix = $division->gender == Division::$BOYS ? 'B' : 'G';
        $teamIdPrefix .= $division->name;
        return $teamIdPrefix;
/*
        switch ($division->name) {
            case '5U':
                $teamIdPrefix .= '2013';
                break;
            case '6U':
                $teamIdPrefix .= '2012';
                break;
            case '7U':
                $teamIdPrefix .= '2011';
                break;
            case '8U':
                $teamIdPrefix .= '2010';
                break;
            case '10U':
                $teamIdPrefix .= '2009-8';
                break;
            case '12U':
                $teamIdPrefix .= '2007-6';
                break;
            case '14U':
                $teamIdPrefix .= '2005-4';
                break;
            case '19U':
                $teamIdPrefix .= '2003-0';
                break;
            default:
                Precondition::isTrue(false, "Unrecognized division name: $division->name");
                break;
        }

        return $teamIdPrefix;
*/
    }

    /**
     * Populate Facilities
     *
     * @param string $data - Expected format:
     *      FacilityName,Address1,Address2,City,State,ZipCode,ContactName,ContactEmail,ContactPhone,Enabled<br>
     *
     *      Multi line data where fields are comma separated
     *
     *      where:
     *          FacilityName    - name of the facility
     *          Address1        - Address of the facility
     *          Address2        - Additional address info if any
     *          City            - City of the facility
     *          State           - State of the facility
     *          ZipCode         - Zip code for the facility
     *          ContactName     - Person to contact to reserve facility
     *          ContactEmail    - Email address of contact person
     *          ContactPhone    - Phone number of contact person
     *          Enabled         - 1 if enabled; 0 if disabled
     *
     * @param bool  $ignoreHeaderRow - defaults to true
     */
    public function populateFacilities($data, $ignoreHeaderRow = true)
    {
        $line = "";
        try {
            $lines = explode("\n", $data);
            $processedLines = 0;

            foreach ($lines as $line) {
                $processedLines += 1;
                if ($processedLines == 1 and $ignoreHeaderRow) {
                    continue;
                }

                $fields = explode(',', $line);
                Assertion::isTrue(count($fields) == 10, "Invalid line: $line");

                $facilityName = $fields[0];
                $address1       = $fields[1];
                $address2       = $fields[2];
                $city           = $fields[3];
                $state          = $fields[4];
                $zipCode        = $fields[5];
                $contactName    = $fields[6];
                $contactEmail   = $fields[7];
                $contactPhone   = $fields[8];
                $enabled        = $fields[9];

                Facility::create($this, $facilityName,$address1, $address2, $city, $state, $zipCode, 'USA', $contactName, $contactEmail, $contactPhone, '', $enabled, true);
            }
        } catch (\Exception $e) {
            print ("Error: Invalid line in uploaded file: '$line'<br>" . $e->getMessage());
        }
    }

    /**
     * Populate Fields and DivisionFields
     *
     * @param string $data - Expected format:
     *      FacilityName,FieldName,Enabled,DivisionList
     *
     *      Multi line data where fields are comma separated
     *
     *      where:
     *          FacilityName    - Name of the facility
     *          FieldName       - Name of the field
     *          Enabled         - 1 if enabled; 0 if disabled
     *          DivisionList    - List of divisions that can use the facility, ; between division names
     *
     * @param bool  $ignoreHeaderRow - defaults to true
     */
    public function populateFields($data, $ignoreHeaderRow = true)
    {
        $line = "";
        try {
            $lines = explode("\n", $data);
            $processedLines = 0;

            foreach ($lines as $line) {
                // Skip header line
                $processedLines += 1;
                if ($processedLines == 1 and $ignoreHeaderRow) {
                    continue;
                }

                // Skip blank lines
                if ($line == '') {
                    continue;
                }

                $fields = explode(',', $line);
                Assertion::isTrue(count($fields) == 4, "Invalid line: $line");

                $facilityName   = $fields[0];
                $fieldName      = $fields[1];
                $enabled        = $fields[2];
                $divisionNames  = explode(";", $fields[3]);

                // Create Field
                $facility   = Facility::lookupByName($this, $facilityName);
                $field      = Field::create($facility, $fieldName, $enabled, true);

                // Create DivisionFields
                foreach ($divisionNames as $divisionName) {
                    $divisions = Division::lookupByName($this, $divisionName);
                    foreach ($divisions as $division) {
                        DivisionField::create($division, $field, true);
                    }
                }

                // Create GameTimes
                $this->createGameTimes($field, false);
            }
        } catch (\Exception $e) {
            print ("Error: Invalid line in uploaded file: '$line'<br>" . $e->getMessage());
        }
    }

    /**
     * Populate Divisions, Teams, Coaches
     *
     * @param string $fielName - Expected format:
     *      UserID,<blank>,Team Designation,Team Letter/Number,Division, Had/Co-Coaches,Head/Co-Coach Emails
     *      Examples:
     *          "26D65666-3E58-41A9-9994-8E822D9BA483","","B12-13","13","B12","Kelly Griffin, Jesse Mccue","griffin.ke@gmail.com, marqueeconstructioninc@gmail.com"
     *          "1CCEFB68-5081-4CC2-8322-10885013D0E9","","B8-10 -Foothill Elementary-","10 -Foothill Elementary-","B8","Chris Link","cjlink@ucla.edu"
     *
     * @param bool  $ignoreHeaderRow - defaults to true
     */
    public function populateInLeagueDivisions($fileName, $ignoreHeaderRow = true )
    {
        $processedLines = 0;
        $line = '';
        try {
            if (($handle = fopen($fileName, "r")) !== FALSE) {
                while (($line = fgetcsv($handle, 3000, ",")) !== FALSE) {
                    // Skip header line
                    $processedLines += 1;
                    if ($processedLines == 1 and $ignoreHeaderRow) {
                        continue;
                    }

                    // Skip blank lines
                    if ($line == '') {
                        continue;
                    }

                    $count = count($line);
                    Assertion::isTrue($count == 7, "Invalid line: $line, count: $count");

                    // Get Division
                    // Example: B12 changed to 12U
                    $divisionName = ltrim($line[4], 'BG');
                    $divisionName .= 'U';

                    // Get TeamId
                    // Example: B8-10 -Foothill Elementary- shouuld be B8-10
                    $teamName   = '';
                    $teamId     = explode(' ', $line[2])[0];
                    if (substr($teamId, 0, strlen('B14-B14-')) == 'B14-B14-') {
                        $teamId     = substr_replace($teamId, 'B14-', 0, strlen('B14-B14-'));
                    }

                    // Other attributes
                    $region                 = '122';
                    $city                   = 'Santa Barbara';
                    $gender                 = $teamId[0] == 'B' ? "Boys" : "Girls";
                    $coachName              = explode(',', $line[5])[0];
                    $coachPhone             = '';
                    $coachCell              = '';
                    $coachEmail             = explode(',', $line[6])[0];
                    $displayOrder           = $this->getDivisionDisplayOrder($divisionName);
                    $gameDurationMinutes    = $this->getGameDurationMinutes($divisionName);
                    $maxPlayersPerTeam      = $this->getMaxPlayersPerTeam($divisionName);
                    $teamName               = empty($teamName) ? $teamId : $teamName;

                    // Do not store the same phone number a second time for a  coach
                    $coachPhone = ($coachPhone == $coachCell) ? '' : $coachPhone;

                    print("<p>divisionName:$divisionName, gender:$gender, division:$divisionName, teamName/Id: $teamName ($teamId), coach:$coachName, email:$coachEmail, phone:$coachPhone, cell:$coachCell</p>");

                    // Create division, team and coach
                    $division   = Division::create($this, $divisionName, $gender, $maxPlayersPerTeam, $gameDurationMinutes, $displayOrder, true);
                    $team       = Team::create($division, null, $teamName, $teamId, $region, $city, true);
                    Coach::create($team, null, $coachName, $coachEmail, $coachPhone, $coachCell, true);
                }
                fclose($handle);
            }
        } catch (\Exception $e) {
            print ("Error: Invalid line in uploaded file: '$line'<br>" . $e->getMessage());
        }
    }

    /**
     * Populate or Update inLeague Coaches
     *
     * @param string $fielName - Expected format:
     *      UserID,<blank>,First Name,Last Name,Email Address,Home Phone,Work Phone,Cell Phone,Secondary Email,Tertiary Email,Coaching Assignments
     *      Examples:
     *          "01254A3E-F36B-1410-8752-00FFFFFFFFFF","","Brandon","Friesen","brandon.friesen@ucsb.edu","","","805-698-8184","","","B10U"
     *          "128B503E-F36B-1410-8752-00FFFFFFFFFF","","Martin","Cabello","mcabello44@yahoo.com","","","805-252-4922","","","G8U B14U"
     *
     * @param bool  $ignoreHeaderRow - defaults to true
     */
    public function populateInLeagueCoaches($fileName, $ignoreHeaderRow = true )
    {
        $processedLines = 0;
        $line = '';
        try {
            if (($handle = fopen($fileName, "r")) !== FALSE) {
                while (($line = fgetcsv($handle, 3000, ",")) !== FALSE) {
                    // Skip header line
                    $processedLines += 1;
                    if ($processedLines == 1 and $ignoreHeaderRow) {
                        continue;
                    }

                    // Skip blank lines
                    if ($line == '') {
                        continue;
                    }

                    $count = count($line);
                    Assertion::isTrue($count == 11, "Invalid line: $line, count: $count");

                    // Get Coach Email and Phone Numbers
                    $coachName  = $line[2] . ' ' . $line[3];
                    $coachEmail = $line[4];
                    $coachPhone = $line[5];
                    $coachCell  = $line[7];
                    $coachPhone = ($coachPhone == $coachCell) ? '' : $coachPhone;

                    // Find Coach by name and then update phone numbers (this season coaches only)
                    $coaches = Coach::findByName($coachName);
                    foreach ($coaches as $coach) {
                        if ($coach->team->division->season->id == $this->id) {
                            print("<p>name:($coach->name, $coachName), email:($coach->email, $coachEmail), phone1:($coach->phone1, $coachPhone), phone2:($coach->phone2, $coachCell)</p>");
                            $coach->phone1 = $coachPhone;
                            $coach->phone2 = $coachCell;
                        }
                    }
                }
                fclose($handle);
            }
        } catch (\Exception $e) {
            print ("Error: Invalid line in uploaded file: '$line'<br>" . $e->getMessage());
        }
    }

    /**
     * Populate Facilities, Fields and DivisionFields
     *
     * @param string $fileName - Expected file format:
     *      Field,Active,Favored Divisions,Competitions, Street, City, Zip
     *      Example: Girsh Park, Field 01, 7U (Girsh01_7U),Yes,B7,G7,Fall League,Girsh Park 7050 Phelps Rd,Goleta,93117
     *
     * @param bool  $ignoreHeaderRow - defaults to true
     */
    public function populateInLeagueFields($fileName, $ignoreHeaderRow = true)
    {
        $processedLines = 0;
        $line = '';
        try {
            if (($handle = fopen($fileName, "r")) !== FALSE) {
                while (($line = fgetcsv($handle, 1000, ",")) !== FALSE) {
                    // Skip header line
                    $processedLines += 1;
                    if ($processedLines == 1 and $ignoreHeaderRow) {
                        continue;
                    }

                    // Skip blank lines
                    if ($line == '') {
                        continue;
                    }

                    $count = count($line);
                    Assertion::isTrue($count == 7, "Invalid line: $line, count: $count");

                    // Get facilityName
                    $offset = strpos($line[0], ',', 0);
                    $facilityName = $line[0];
                    if (!is_bool($offset)) {
                        $facilityName = substr($line[0], 0, $offset);
                    }

                    // Get fieldName
                    $startOffset = strpos($line[0], '(', 0);
                    $endOffset = strpos($line[0], ')', 0);
                    Assertion::isTrue(!is_bool($startOffset), "Invalid Field Format, missing (: $line[0]");
                    Assertion::isTrue(!is_bool($endOffset), "Invalid Field Format, missing ): $line[0]");
                    $fieldName = substr($line[0], $startOffset + 1, $endOffset - ($startOffset + 1));

                    // Get enabled
                    $enabled = $line[1] == 'Yes' ? 1 : 0;

                    // Get Division List
                    $divisionList = $line[2];

                    // Facility address
                    $address = $line[4];
                    $city = $line[5];
                    $state = 'California';
                    $zipCode = $line[6];
                    $country = 'United States';

                    echo "<p> Facility: $facilityName, Field: $fieldName, Enabled: $enabled, Divisions: $divisionList<br /></p>\n";

                    // Get or Create Facility
                    $facility = Facility::create($this, $facilityName, $address, '', $city, $state, $zipCode, $country, '', '', '', '', $enabled, true);

                    // Get or Create the Field
                    $field = Field::create($facility, $fieldName, $enabled, true);

                    // Create DivisionFields
                    $divisionNames  = explode(",", $divisionList);
                    foreach ($divisionNames as $divisionName) {
                        $divisionName = ltrim($divisionName, 'BG');
                        $divisionName .= 'U';
                        $divisions = Division::lookupByName($this, $divisionName);
                        foreach ($divisions as $division) {
                            DivisionField::create($division, $field, true);
                        }
                    }

                    // Create GameTimes if divisions are set
                    if ($divisionList != '') {
                        $this->createGameTimes($field, false);
                    }
                }
                fclose($handle);
            }
        } catch (\Exception $e) {
            throw new \Exception("Error: Invalid line in uploaded file: '$line'<br>, caused by: " . $e->getMessage());
        }
    }

    /**
     * Populate Games and FamilyGames
     *
     * @param string $fileName - Expected file format:
     *      GameID,<blank>,Division,Game Date,Game Time,Field,HomeTeam,Visiting Team,Game Number
     *      Example:
     *          "00FD573E-F36B-1410-8753-00FFFFFFFFFF","","B8","Sat, Nov 9, 2024","2:00 PM","Girsh14_8U","B8-23 - Peabody Charter -","B8-22 - Harding Elementary -","1441"
     *
     * @param bool  $ignoreHeaderRow - defaults to true
     */
    public function populateInLeagueGames($fileName, $ignoreHeaderRow = true)
    {
        $processedLines = 0;
        $line = '';
        try {
            if (($handle = fopen($fileName, "r")) !== FALSE) {
                while (($line = fgetcsv($handle, 2000, ",")) !== FALSE) {
                    // Skip header line
                    $processedLines += 1;
                    if ($processedLines == 1 and $ignoreHeaderRow) {
                        continue;
                    }

                    // Skip blank lines
                    if ($line == '') {
                        continue;
                    }

                    $count = count($line);
                    Assertion::isTrue($count == 9 or $count == 10, "Invalid line: $line, count: $count");

                    // Get game data
                    $divisionName = ltrim($line[2], 'BG');
                    $divisionName .= 'U';
                    $gameDateStr = $line[3];
                    $gameDateAttributes = explode(' ', $gameDateStr);
                    $month = $gameDateAttributes[1];
                    switch ($month) {
                        case 'Jan':
                            $month = '01';
                            break;
                        case 'Feb':
                            $month = '02';
                            break;
                        case 'Mar':
                            $month = '03';
                            break;
                        case 'Apr':
                            $month = '04';
                            break;
                        case 'May':
                            $month = '05';
                            break;
                        case 'Jun':
                            $month = '06';
                            break;
                        case 'Jul':
                            $month = '07';
                            break;
                        case 'Aug':
                            $month = '08';
                            break;
                        case 'Sep':
                            $month = '09';
                            break;
                        case 'Oct':
                            $month = '10';
                            break;
                        case 'Nov':
                            $month = '11';
                            break;
                        case 'Dec':
                            $month = '12';
                            break;
                        default:
                            throw new \Exception("Invalid month field: $month");
                    }
                    $gameDateStr = $gameDateAttributes[3] . '-' . $month . '-' . rtrim($gameDateAttributes[2], ",");
                    if ($gameDateStr == '2024-06-29') {
                        continue;
                    }
                    $gameDate = GameDate::lookupByDay($this, $gameDateStr);

                    $facilities = Facility::lookupBySeason($this);
                    $field = null;
                    foreach ($facilities as $facility) {
                        $fields = Field::lookupByFacility($facility);
                        foreach ($fields as $field) {
                            if ($field->name == $line[5]) {
                                break;
                            }
                        }
                        if (!is_null($field) and $field->name == $line[5]) {
                            break;
                        }
                    }
                    assertion(!is_null($field), "Unable to find field for: $line[5]");

                    $new_time = \DateTime::createFromFormat('h:i A', $line[4]);
                    $gameTimeStr = $new_time->format('H:i:s');

                    $actualTimeStr = null;
                    if ($count == 10) {
                        $new_time = \DateTime::createFromFormat('h:i A', $line[9]);
                        $actualTimeStr = $new_time->format('H:i:s');
                    }

                    // $gameTimeStr = $gameTimeAttributes[0];
                    // if ($gameTimeAttributes[1] == 'PM') {
                    //     $gameTimeAttributes = explode(':', $gameTimeAttributes[0]);
                    //     $gameTimeStr = 12 + $gameTimeAttributes[0] . ':' . '$gameTimeAttributes[1]';
                    // }
                    $gameTimes = GameTime::lookupByGameDateAndField($gameDate, $field);
                    $gameTime = null;
                    foreach ($gameTimes as $gameTime) {
                        if ($gameTime->startTime == $gameTimeStr) {
                            break;
                        }
                    }
                    assertion(!is_null($gameTime), "Unable to find gimeTime for: $line[4] on field $field->name");
                    assertion($gameTime->startTime == $gameTimeStr, "Unable to find matching start time for $gameTimeStr with inLeague id $line[8]");

                    $homeTeamId = explode(' ', $line[6])[0];

                    if (substr($homeTeamId, 0, strlen('B14-B14-')) == 'B14-B14-') {
                        $homeTeamId = substr_replace($homeTeamId, 'B14-', 0, strlen('B14-B14-'));
                    }
                    // If $homeTeamId has more than one '-' character, keep everything before the second '-'
                    $dashCount = substr_count($homeTeamId, '-');
                    if ($dashCount > 1) {
                        $parts = explode('-', $homeTeamId);
                        $homeTeamId = $parts[0] . '-' . $parts[1];
                    }

                    $visitingTeamId = explode(' ', $line[7])[0];
                    if (substr($visitingTeamId, 0, strlen('B14-B14-')) == 'B14-B14-') {
                        $visitingTeamId = substr_replace($visitingTeamId, 'B14-', 0, strlen('B14-B14-'));
                    }
                    // If $visitingTeamId has more than one '-' character, keep everything before the second '-'
                    $dashCount = substr_count($visitingTeamId, '-');
                    if ($dashCount > 1) {
                        $parts = explode('-', $visitingTeamId);
                        $homeTeamId = $parts[0] . '-' . $parts[1];
                    }

                    // $divisionName = $homeTeamId == '(TBD)' ? explode('-', $visitingTeamId)[0] : explode('-', $homeTeamId)[0];
                    $gender = $line[2][0] == 'B' ? 'Boys' : 'Girls';
                    // $divisionName = ltrim($divisionName, 'BG');
                    // $divisionName .= 'U';
                    $division = Division::lookupByNameAndGender($this, $divisionName, $gender);

                    $homeTeam = $homeTeamId == '(TBD)' ? null : Team::lookupByNameId($division, $homeTeamId);
                    $visitingTeam = $visitingTeamId == '(TBD)' ? null : Team::lookupByNameId($division, $visitingTeamId);

                    print("<p>date:$gameDate->day, time:($gameTime->startTime, $line[4]), field:$field->name, home:$homeTeamId, visit:$visitingTeamId</p>");

                    // Find or create schedule
                    $schedules = Schedule::lookupByDivision($division);
                    $schedule = null;
                    foreach ($schedules as $schedule) {
                        if ($schedule->startDate == $this->startDate and $schedule->endDate == $this->endDate) {
                            break;
                        }
                    }
                    if (is_null($schedule)) {
                        $schedule = Schedule::create($division, $this->name, ScheduleOrm::SCHEDULE_TYPE_LEAGUE, 10, $this->startDate, $this->endDate, $this->startTime, $this->endTime);
                    }

                    // Find or create flight
                    $flights = Flight::lookupBySchedule($schedule);
                    $flight = null;
                    foreach ($flights as $flight) {
                        if ($flight->name == 'Flight A') {
                            break;
                        }
                    }
                    if (is_null($flight)) {
                        $flight = Flight::create($schedule, 'Flight A', 0, 0, 0, 0);
                    }

                    // Find or create pool
                    $pools = Pool::lookupByFlight($flight);
                    $pool = null;
                    foreach ($pools as $pool) {
                        if ($pool->name == 'A') {
                            break;
                        }
                    }
                    if (is_null($pool)) {
                        $pool = Pool::create($flight, $schedule, 'A');
                    }

                    // Use schedule to createGame (family games also created)
                    $game = $schedule->createGame($this, $flight, $pool, $gameTime, $homeTeam, $visitingTeam);

                    // Set the inLeague game id in the notes field
                    $game->notes = "inLeague_Game#:$line[8]";

                    if (!is_null($actualTimeStr)) {
                        $gameTime->actualStartTime = $actualTimeStr;
                    }
                }
                fclose($handle);
            }
        } catch (\Exception $e) {
            print $e;
            throw new \Exception("Error: Invalid line in uploaded file: '$line'<br>, caused by: " . $e->getMessage());
        }
    }

    /**
     * Populate Players
     *
     * @param string $fileName - Expected file format:
     *      RegistrationID,<blank>,First Name,Last Name,Gender,Division,Home Phone,Cell Phone,Teams
     *      Example:
     *          "0007533E-F36B-1410-8752-00FFFFFFFFFF","","Alida","Babcock","G","G10","","336-782-6862","G10-09"
     *          "0032513E-F36B-1410-8752-00FFFFFFFFFF","","Quinn","Shaefer","B","B14","","805-680-0788","B14-B14-04"
     *
     * @param bool  $ignoreHeaderRow - defaults to true
     */
    public function populateInLeaguePlayers($fileName, $ignoreHeaderRow = true)
    {
        $processedLines = 0;
        $line = '';
        try {
            if (($handle = fopen($fileName, "r")) !== FALSE) {
                while (($line = fgetcsv($handle, 2000, ",")) !== FALSE) {
                    // Skip header line
                    $processedLines += 1;
                    if ($processedLines == 1 and $ignoreHeaderRow) {
                        continue;
                    }

                    // Skip blank lines
                    if ($line == '') {
                        continue;
                    }

                    $count = count($line);
                    Assertion::isTrue($count == 9, "Invalid line: $line, count: $count");

                    // Get game data
                    $divisionName = ltrim($line[5], 'BG');
                    $divisionName .= 'U';
                    $gender = $line[5][0] == 'B' ? 'Boys' : 'Girls';
                    $teamId = explode(' ', $line[8])[0];
                    if (substr($teamId, 0, strlen('B14-B14-')) == 'B14-B14-') {
                        $teamId = substr_replace($teamId, 'B14-', 0, strlen('B14-B14-'));
                    }
                    if ($teamId == '') {
                        // Not yet assigned to a team, skip import
                        continue;
                    }

                    $name = $line[2] . ' ' . $line[3];
                    $division = Division::lookupByNameAndGender($this, $divisionName, $gender);
                    // print("<p>$division->name, $teamId, $name</p>");

                    try {
                        $team = Team::lookupByNameId($division, $teamId);
                    } catch (\DAG\Framework\Orm\NoResultsException $e) {
                        print("<p>ERROR: team not found for $division->name, $teamId, $name</p>");
                        continue;
                    }

                    $homePhone = $line[6];
                    $cellPhone = $line[7];

                    Player::create($team, null, $name, '', $cellPhone == '' ? $homePhone : $cellPhone, true);
                }
                fclose($handle);
            }
        } catch (\Exception $e) {
            print get_class($e);
            throw new \Exception("Error: Invalid line in uploaded file: '$line'<br>, caused by: " . $e->getMessage());
        }
    }

    /**
     * Populate Referees
     *
     * @param string $data - Expected format:
     *      Approved,Last Seen,eAYSO Vol App,AYSO ID,Name,Years,Games,Badge,Phone,Email
     *
     *      Multi line data where fields are comma separated
     *
     *      where:
     *          Approved        - Unused
     *          Last Seen       - Unused
     *          eAYSO Vol App   - Unused
     *          AYSO ID         - Unused
     *          Name            - Referee's name
     *          Years           - Unused
     *          Games           - Unused
     *          Badge           - Referee's badge level
     *          Phone           - Referee's phone number
     *          Email           - Referee's email address
     *
     * @param bool  $ignoreHeaderRow - defaults to true
     */
    public function populateReferees($data, $ignoreHeaderRow = true)
    {
        $line = "";
        try {
            $lines = explode("\n", $data);
            $processedLines = 0;

            foreach ($lines as $line) {
                // Skip header line
                $processedLines += 1;
                if ($processedLines == 1 and $ignoreHeaderRow) {
                    continue;
                }

                // Skip blank lines
                if ($line == '') {
                    continue;
                }

                $fields = explode(',', $line);
                Assertion::isTrue(count($fields) == 10, "Invalid line: $line");

                $name       = $fields[4];
                $badgeLevel = $fields[7];
                $phone      = $fields[8];
                $email      = $fields[9];

                // Create or Update Referee
                /** @var Referee $referee */
                $referee    = null;
                $badgeId    = $this->getBadgeId($badgeLevel);
                $family     = null;
                Family::findByPhone($this, $phone, $family);
                if (Referee::findByEmailAndName($this, $email, $name, $referee)) {
                    $referee->phone     = $phone;
                    $referee->badgeId   = $badgeId;
                    $referee->family    = $family;
                } else {
                    Referee::create($this, $family, $name, $email, $phone, $badgeId, 0, '');
                }
            }
        } catch (\Exception $e) {
            print ("Error: Invalid line in uploaded file: '$line'<br>" . $e->getMessage());
        }
    }

    /**
     * Delete all team referees and re-populate from file data
     *
     * @param string $data - Expected format:
     *      Division,Team#,TeamID,Coach,Referee1,Referee2,Referee3,Referee4,Referee5,Referee6,Referee7,Referee8,Referee9,Referee10
     *
     *      Multi line data where fields are comma separated
     *
     *      where:
     *          Division        - Division Name <Gender><Age>, B10U
     *          Team#           - <number>, 5
     *          TeamID          - <Genger><Age>-<NN>, B10U-05
     *          Coach           - <Name>, Dave Giannini
     *          Referee1        - <Name>, Dave Giannini
     *          ...
     *          Referee10       - <Name>, Theresa Giannini (or empty)
     *
     * @param bool  $ignoreHeaderRow - defaults to true
     */
    public function populateRefereesByTeam($data, $ignoreHeaderRow = true)
    {
        // Delete all team referees and re-populate from file data
        $divisions = Division::lookupBySeason($this);
        foreach ($divisions as $division) {
            $teams = Team::lookupByDivision($division);
            foreach ($teams as $team) {
                $teamReferees = TeamReferee::lookupByTeam($team);
                foreach ($teamReferees as $teamReferee) {
                    $teamReferee->delete();
                }
            }
        }

        // Populate team referees from file data
        $line = "";
        try {
            $lines = explode("\n", $data);
            $processedLines = 0;

            foreach ($lines as $line) {
                // Skip header line
                $processedLines += 1;
                if ($processedLines == 1 and $ignoreHeaderRow) {
                    continue;
                }

                // Skip blank lines
                if ($line == '') {
                    continue;
                }

                // Trim the new line
                $line = trim($line);

                // Break into fields
                $fields = explode(',', $line);
                Assertion::isTrue(count($fields) >= 5, "Invalid line: $line");

                $divisionName   = $fields[0];
                $teamId         = $fields[2];

                // Lookup team
                $gender     = $divisionName[0] == 'B' ? Division::$BOYS : Division::$GIRLS;
                $division   = Division::lookupByNameAndGender($this, substr($divisionName, 1), $gender);
                $team       = Team::lookupByNameId($division, $teamId);

                // Find/create TeamReferees (idempotent) for each referee
                for ($i = 4; $i < count($fields); ++$i) {
                    if (empty($fields[$i])) {
                        continue;
                    }

                    $referees = Referee::lookupByName($this, $fields[$i]);
                    if (count($referees) == 0) {
                        print "Referee($i): '$fields[$i]' for team '$teamId' not found<br>";
                    }
                    else if (count($referees) > 1) {
                        print "Referee($i): '$fields[$i]' cannot be added to team '$teamId' because there are " . count($referees) . " with the same name<br>";
                    } else {
                        $teamReferee = TeamReferee::create($team, $referees[0], true);
                        $teamReferee->referee->setDefaultPreferences();
                    }
                }
            }
        } catch (\Exception $e) {
            print ("Error: Invalid line in uploaded file: '$line'<br>" . $e->getMessage());
        }
    }

    /**
     * @param $badgeLevel
     * @return string
     * @throws \Exception
     */
    private function getBadgeId($badgeLevel)
    {
        switch ($badgeLevel) {
            case 'R':
                return RefereeOrm::REGIONAL;
            case 'I':
            case 'INT':
            case 'INTc':
                return RefereeOrm::INTERMEDIATE;
            case 'A':
            case 'ADV':
                return RefereeOrm::ADVANCED;
            case 'N':
            case 'N1':
            case 'N2':
            case 'Nc':
                return RefereeOrm::NATIONAL;
            case 'NEW':
            default:
                return RefereeOrm::UNKNOWN;
        }
    }

    /**
     * Create GameTimes for Field.  Delete existing GameTimes if requested.
     *
     * @param Field $field
     * @param bool  $deleteExistingGameTimes
     */
    public function createGameTimes($field, $deleteExistingGameTimes)
    {
        // Delete game times for field.  Exception thrown if a gameTime has an assigned field
        if ($deleteExistingGameTimes) {
            $field->deleteGameTimes();
        }

        // Get the gameDates, startTime and endTime from Season
        $gameDates = GameDate::lookupBySeason($this);
        $startTime = $this->startTime;
        $endTime   = $this->endTime;

        // Create games times for the field across all of the game dates
        $ignoreDuplicates = !$deleteExistingGameTimes;
        GameTime::createByGameDates($gameDates, $field, $startTime, $endTime, $ignoreDuplicates);
    }

    /**
     * Populate game day referees
     *
     * @param GameDate  $gameDate
     * @param string    $divisionName - defaults to '' causing all divisions to be populated
     */
    public function populateGameDayReferees($gameDate, $divisionName = '')
    {
        // Get Divisions
        $divisions = Division::lookupBySeason($this);

        // For each division, populate game day referees via team assignments
        foreach ($divisions as $division) {
            if ($division->isScoringTracked and ($divisionName == '' or $division->name == $divisionName)) {
                $division->populateGameDayReferees($gameDate, Referee::TEAM_REFEREE);
            }
        }

        // For each division, populate game day referees via floating referees
        foreach ($divisions as $division) {
            if ($division->isScoringTracked and ($divisionName == '' or $division->name == $divisionName)) {
                $division->populateGameDayReferees($gameDate, Referee::NON_TEAM_REFEREE);
            }
        }

        // For each division, make two more passes to try to fill in open slots
        foreach ($divisions as $division) {
            if ($division->isScoringTracked and ($divisionName == '' or $division->name == $divisionName)) {
                $division->populateGameDayReferees($gameDate, Referee::ALL_REFEREES);
            }
        }

        foreach ($divisions as $division) {
            if ($division->isScoringTracked and ($divisionName == '' or $division->name == $divisionName)) {
                $division->populateGameDayReferees($gameDate, Referee::ALL_REFEREES);
            }
        }

        // TODO: Return counts of assignments that still need to be filled
    }

    /**
     * Clear game day referees
     *
     * @param GameDate  $gameDate
     * @param string    $divisionName
     */
    public function clearGameDayReferees($gameDate, $divisionName)
    {
        // Get Divisions
        $divisions = Division::lookupByName($this, $divisionName);

        // For each division, populate game day referees via team assignments
        foreach ($divisions as $division) {
            $division->clearGameDayReferees($gameDate);
        }
    }

    /**
     *  Delete the season
     */
    public function delete()
    {
        $divisions = Division::lookupBySeason($this);
        foreach ($divisions as $division) {
            $division->delete();
        }

        $families = Family::lookupBySeason($this);
        foreach ($families as $family) {
            $family->delete();
        }

        $gameDates = GameDate::lookupBySeason($this);
        foreach ($gameDates as $gameDate) {
            $gameDate->delete();
        }

        $facilities = Facility::lookupBySeason($this);
        foreach ($facilities as $facility) {
            $facility->delete();
        }

        $this->seasonOrm->delete();
    }
}