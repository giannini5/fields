<?php

namespace DAG\Domain\Schedule;

use DAG\Domain\Domain;
use DAG\Framework\Exception\Assertion;
use DAG\Orm\Schedule\SeasonOrm;
use DAG\Framework\Exception\Precondition;


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
                case "name":
                case "startDate":
                case "endDate":
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
    public function populateDivisions($data, $ignoreHeaderRow = true)
    {
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
            $teamName = sprintf('%s-%02d', $teamNameAttributes[0], $teamNameAttributes[1]);

            $divisionName   = $teamNameAttributes[0];
            $division       = Division::create($this, $divisionName, true);
            $team           = Team::create($division, null, $teamName, true);

            switch (strtolower($fields[2])) {
                case 'coach':
                    Coach::create($team, null, $fields[5], $fields[8], $fields[6], $fields[7], true);
                    break;
                default:
                    AssistantCoach::create($team, null, $fields[5], $fields[8], $fields[6], $fields[7], true);
                    break;
            }
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
     *          Division is of the format <Division><Gender>.  For example: U6G
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
        $lines = explode("\n", $data);
        $processedLines = 0;

        foreach ($lines as $line) {
            $processedLines += 1;
            if ($processedLines == 1 and $ignoreHeaderRow) {
                continue;
            }

            $fields = explode(',', $line);
            Assertion::isTrue(count($fields) == 10, "Invalid line: $line");

            $divisionName   = $fields[1];
            $teamName       = sprintf('%s-%02d', $fields[1], $fields[2]);
            $division       = Division::create($this, $divisionName, true);
            $team           = Team::create($division, null, $teamName, true);
            $player         = Player::create($team, null, $fields[5], '', $fields[6]);
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