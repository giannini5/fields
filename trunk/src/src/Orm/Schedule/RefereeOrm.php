<?php

namespace DAG\Orm\Schedule;

use DAG\Framework\Orm\FieldValidator as FV;
use DAG\Framework\Orm\PersistenceConfig as PC;
use DAG\Framework\Orm\PersistenceModel;


/**
 * @property int    $id
 * @property int    $seasonId
 * @property int    $familyId
 * @property string $name
 * @property string $email
 * @property string $phone
 * @property int    $badgeId
 * @property int    $maxGamesPerDay
 * @property string $specialInstructions
 */
class RefereeOrm extends PersistenceModel
{
    const FIELD_ID                      = 'id';
    const FIELD_SEASON_ID               = 'seasonId';
    const FIELD_FAMILY_ID               = 'familyId';
    const FIELD_NAME                    = 'name';
    const FIELD_EMAIL                   = 'email';
    const FIELD_PHONE                   = 'phone';
    const FIELD_BADGE_ID                = 'badgeId';
    const FIELD_MAX_GAMES_PER_DAY       = 'maxGamesPerDay';
    const FIELD_SPECIAL_INSTRUCTiONS    = 'specialInstructions';

    const UNKNOWN       = 'U';
    const REGIONAL      = 'R';
    const INTERMEDIATE  = 'I';
    const ADVANCED      = 'A';
    const NATIONAL      = 'N';

    public static $badgeLevels = [
        self::UNKNOWN       => 'Unknown',
        self::REGIONAL      => 'Regional',
        self::INTERMEDIATE  => 'Intermediate',
        self::ADVANCED      => 'Advanced',
        self::NATIONAL      => 'National',
    ];

    protected static $fields = [
        self::FIELD_ID                      => [FV::INT,    [FV::NO_CONSTRAINTS], null],
        self::FIELD_SEASON_ID               => [FV::INT,    [FV::NO_CONSTRAINTS]],
        self::FIELD_FAMILY_ID               => [FV::INT,    [FV::NO_CONSTRAINTS], null],
        self::FIELD_NAME                    => [FV::STRING, [FV::NO_CONSTRAINTS]],
        self::FIELD_EMAIL                   => [FV::STRING, [FV::NO_CONSTRAINTS]],
        self::FIELD_PHONE                   => [FV::STRING, [FV::NO_CONSTRAINTS], ''],
        self::FIELD_BADGE_ID                => [FV::STRING, [FV::ENUM, [self::UNKNOWN, self::REGIONAL, self::INTERMEDIATE, self::ADVANCED, self::NATIONAL]], self::UNKNOWN],
        self::FIELD_MAX_GAMES_PER_DAY       => [FV::INT,    [FV::NO_CONSTRAINTS]],
        self::FIELD_SPECIAL_INSTRUCTiONS    => [FV::STRING, [FV::NO_CONSTRAINTS], ''],
    ];

    protected static $config = [
        PC::PERSISTENCE_DRIVER => PC::DRIVER_MYSQL,
        PC::SCHEMA             => 'schedule_rw',
        PC::TABLE              => 'referee',
        PC::AUTO_INC_FIELD     => self::FIELD_ID,
        PC::PRIMARY_KEYS       => [self::FIELD_SEASON_ID, self::FIELD_EMAIL],
    ];

    /**
     * Create a Referee
     *
     * @param int    $seasonId
     * @param int    $familyId
     * @param string $name
     * @param string $email
     * @param string $phone
     * @param string $badgeId
     * @param int    $maxGamesPerDay
     * @param string $specialInstructions
     *
     * @return RefereeOrm
     * @throws \DAG\Framework\Orm\DuplicateEntryException
     */
    public static function create(
        $seasonId,
        $familyId,
        $name,
        $email,
        $phone,
        $badgeId,
        $maxGamesPerDay,
        $specialInstructions)
    {
        $result = self::getPersistenceDriver()->create(
            array_filter(
                [
                    self::FIELD_SEASON_ID               => $seasonId,
                    self::FIELD_FAMILY_ID               => $familyId,
                    self::FIELD_NAME                    => $name,
                    self::FIELD_EMAIL                   => $email,
                    self::FIELD_PHONE                   => $phone,
                    self::FIELD_BADGE_ID                => $badgeId,
                    self::FIELD_MAX_GAMES_PER_DAY       => $maxGamesPerDay,
                    self::FIELD_SPECIAL_INSTRUCTiONS    => $specialInstructions,
                ],
                function ($item) {
                    return $item !== null;
                }
            )
        );

        return new static($result);
    }

    /**
     * Load a RefereeOrm by id
     *
     * @param int $id
     *
     * @return RefereeOrm
     */
    public static function loadById($id)
    {
        $result = self::getPersistenceDriver()->getOne([self::FIELD_ID => $id]);

        return new static($result);
    }

    /**
     * Load a RefereeOrm by seasonId, email and name
     *
     * @param int       $seasonId
     * @param string    $email
     * @param string    $name
     *
     * @return RefereeOrm
     */
    public static function loadBySeasonIdEmailAndName($seasonId, $email, $name)
    {
        $result = self::getPersistenceDriver()->getOne(
            [
                self::FIELD_SEASON_ID  => $seasonId,
                self::FIELD_EMAIL      => $email,
                self::FIELD_NAME       => $name,
            ]);

        return new static($result);
    }

    /**
     * Load a RefereeOrm by seasonId, email
     *
     * @param int       $seasonId
     * @param string    $email
     *
     * @return RefereeOrm[]
     */
    public static function loadBySeasonIdAndEmail($seasonId, $email)
    {
        $results = self::getPersistenceDriver()->getMany(
            [
                self::FIELD_SEASON_ID  => $seasonId,
                self::FIELD_EMAIL      => $email
            ]);

        $refereeOrms = [];
        foreach ($results as $result) {
            $refereeOrms[] = new static($result);
        }

        return $refereeOrms;
    }

    /**
     * Load a RefereeOrm by seasonId, name
     *
     * @param int       $seasonId
     * @param string    $name
     *
     * @return RefereeOrm[]
     */
    public static function loadBySeasonIdAndName($seasonId, $name)
    {
        $results = self::getPersistenceDriver()->getMany(
            [
                self::FIELD_SEASON_ID   => $seasonId,
                self::FIELD_NAME        => $name
            ]);

        $refereeOrms = [];
        foreach ($results as $result) {
            $refereeOrms[] = new static($result);
        }

        return $refereeOrms;
    }

    /**
     * Load all RefereeOrms by seasonId
     *
     * @param int $seasonId
     *
     * @return RefereeOrm[]
     */
    public static function loadBySeasonId($seasonId)
    {
        $results = self::getPersistenceDriver()->getMany(
            [
                self::FIELD_SEASON_ID  => $seasonId
            ]);

        $refereeOrms = [];
        foreach ($results as $result) {
            $refereeOrms[] = new static($result);
        }

        return $refereeOrms;
    }

    /**
     * Load all RefereeOrms by familyId
     *
     * @param int $familyId
     *
     * @return RefereeOrm[]
     */
    public static function loadByFamilyId($familyId)
    {
        $results = self::getPersistenceDriver()->getMany(
            [
                self::FIELD_FAMILY_ID  => $familyId
            ]);

        $refereeOrms = [];
        foreach ($results as $result) {
            $refereeOrms[] = new static($result);
        }

        return $refereeOrms;
    }

    /**
     * @return string
     */
    public function getBadge()
    {
        return self::$badgeLevels[$this->badgeId];
    }
}