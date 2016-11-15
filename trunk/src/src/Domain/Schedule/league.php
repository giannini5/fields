<?php

namespace DAG\Domain\Schedule;

use DAG\Domain\Domain;
use DAG\Orm\Schedule\LeagueOrm;
use DAG\Framework\Exception\Precondition;
use DAG\Orm\Schedule\SeasonOrm;

/**
 * @property int    $id
 * @property string $name
 */
class League extends Domain
{
    /** @var LeagueOrm */
    private $leagueOrm;

    /**
     * @param LeagueOrm $leagueOrm
     */
    protected function __construct(LeagueOrm $leagueOrm)
    {
        $this->leagueOrm = $leagueOrm;
    }

    /**
     * @param string $name
     *
     * @return League
     */
    public static function create($name)
    {
        $leagueOrm = LeagueOrm::create($name);
        return new static($leagueOrm);
    }

    /**
     * @param int $leagueId
     *
     * @return League
     */
    public static function lookupById($leagueId)
    {
        $leagueOrm = LeagueOrm::loadById($leagueId);
        return new static($leagueOrm);
    }

    /**
     * @param $name
     *
     * @return League
     */
    public static function lookupByName($name)
    {
        Precondition::isNonEmpty($name, 'name should not be empty');
        $leagueOrm = LeagueOrm::loadByName($name);
        return new static($leagueOrm);
    }

    /**
     * @param $propertyName
     * @return int|string
     */
    public function __get($propertyName)
    {
        switch ($propertyName) {
            case "id":
                return $this->leagueOrm->id;
                break;

            case "name":
                return $this->leagueOrm->name;
                break;

            default:
                Precondition::isTrue(false, "Unrecognized property: $propertyName");
                break;
        }
    }

    /**
     *  Delete the league
     */
    public function delete()
    {
        $seasons = Season::lookupByLeague($this);
        foreach($seasons as $season) {
            $season->delete();
        }

        $this->leagueOrm->delete();
    }
}