<?php

namespace DAG\Framework\Orm;

use DAG\Framework\Exception\Assertion;
use DAG\Framework\Exception\Precondition;
use DAG\Framework\Utils\TimeUtils;

/**
 * Validates field values against the field rules (referred to as a ruleSet):
 *     - Checks that field values are of the correct data type.
 *     - Checks that field values pass the constraints.
 *     - Applies defaults for fields with undefined values.
 *
 * A ruleSet (as referenced below) is built in this format:
 *     [<data type>, <constraints>, <default>] OR [<data type>, <constraints>] OR [<data type>]
 *
 * <data type>:
 *     One of the PHP types class constants
 *
 * <constraints>:
 *     An array of these forms:
 *         [NO_CONSTRAINTS] (this is the default when not specified)
 *         [POSITIVE] (only applies for int/float)
 *         [RANGE, MIN, MAX] (only applies to int/float)
 *         [LENGTH, MIN, MAX] (only applies to strings)
 *         [ENUM, <array of allowed values>] (only applies to strings and int/float)
 *
 * <default values>:
 *     The default value for the field when the field value is missing
 *     Can be CURRENT_TIME for date/dateTime fields
 *     If no default is specified, there is no default, and the field value must always be provided.
 */
class FieldValidator
{
    // PHP types
    const BOOL           = '__bool';
    const INT            = '__int';
    const FLOAT          = '__float';
    const STRING         = '__string';
    const DATE           = '__date';
    const TIME           = '__time';
    const DATE_TIME      = '__dateTime';

    // Constraints
    const NO_CONSTRAINTS = '__none';
    const POSITIVE       = '__positive';
    const RANGE          = '__range';
    const ENUM           = '__enum';
    const LENGTH         = '__length';

    // Default values
    const CURRENT_TIME   = '__currentTime';

    // RuleSet indexes
    const RULESET_INDEX_DATATYPE   = 0;
    const RULESET_INDEX_CONSTRAINT = 1;
    const RULESET_INDEX_DEFAULT    = 2;

    // RuleSet constraint indexes
    const CONSTRAINT_INDEX_TYPE        = 0;
    const CONSTRAINT_INDEX_ENUM_VALUES = 1;
    const CONSTRAINT_INDEX_RANGE_MIN   = 1;
    const CONSTRAINT_INDEX_RANGE_MAX   = 2;
    const CONSTRAINT_INDEX_LENGTH_MIN  = 1;
    const CONSTRAINT_INDEX_LENGTH_MAX  = 2;

    /**
     * Validate a single value against a ruleSet.
     *
     * @param mixed $value
     * @param array $ruleSet an array of the form defined above
     *
     * @throws \PreconditionException if the value is invalid
     */
    public static function validateValue($value, $ruleSet)
    {
        // Null is allowed if and only if null is the default
        if ($value === null) {
            Precondition::isTrue(
                array_key_exists(self::RULESET_INDEX_DEFAULT, $ruleSet)
                    && $ruleSet[self::RULESET_INDEX_DEFAULT] === null,
                'null not allowed here'
            );
            return;
        }

        $dataType = $ruleSet[self::RULESET_INDEX_DATATYPE];

        $constraintType = null;
        if (isset($ruleSet[self::RULESET_INDEX_CONSTRAINT])) {
            Precondition::isArray($ruleSet[self::RULESET_INDEX_CONSTRAINT], 'ruleSet.constraints');
            $constraintType = $ruleSet[self::RULESET_INDEX_CONSTRAINT][self::CONSTRAINT_INDEX_TYPE];
        }

        switch ($dataType) {
            case self::BOOL:
                Precondition::isBool($value, 'value');
                break;

            case self::INT:
            case self::FLOAT:
                if ($dataType == self::INT) {
                    Precondition::isInt($value, 'value');
                } else {
                    Precondition::isFloat($value, 'value');
                }

                if ($constraintType == self::RANGE) {
                    Precondition::isTrue(
                        $value >= $ruleSet[self::RULESET_INDEX_CONSTRAINT][self::CONSTRAINT_INDEX_RANGE_MIN]
                            && $value <= $ruleSet[self::RULESET_INDEX_CONSTRAINT][self::CONSTRAINT_INDEX_RANGE_MAX],
                        'number not in range [' . $value . ']['
                            . $ruleSet[self::RULESET_INDEX_CONSTRAINT][self::CONSTRAINT_INDEX_RANGE_MIN] . ', '
                            . $ruleSet[self::RULESET_INDEX_CONSTRAINT][self::CONSTRAINT_INDEX_RANGE_MAX] . ']'
                    );
                } elseif ($constraintType == self::POSITIVE) {
                    Precondition::isTrue(
                        $value >= 0,
                        'number not positive [' . $value . ']'
                    );
                } elseif ($constraintType == self::ENUM) {
                    Precondition::isTrue(
                        in_array($value, $ruleSet[self::RULESET_INDEX_CONSTRAINT][self::CONSTRAINT_INDEX_ENUM_VALUES]),
                        'int/float not in enumeration: ' . $value
                    );
                } elseif ($constraintType != self::NO_CONSTRAINTS && $constraintType !== null) {
                    Assertion::isTrue(false, 'bad constraint type for int/float ' . $constraintType);
                }
                break;

            case self::STRING:
                Precondition::isString($value, 'value');

                if ($constraintType == self::LENGTH) {
                    $length = strlen($value);
                    Precondition::isTrue(
                        $length >= $ruleSet[self::RULESET_INDEX_CONSTRAINT][self::CONSTRAINT_INDEX_LENGTH_MIN]
                            && $length <= $ruleSet[self::RULESET_INDEX_CONSTRAINT][self::CONSTRAINT_INDEX_LENGTH_MAX],
                        'string length not allowed [' . $length . ']['
                            . $ruleSet[self::RULESET_INDEX_CONSTRAINT][self::CONSTRAINT_INDEX_LENGTH_MIN] . ', '
                            . $ruleSet[self::RULESET_INDEX_CONSTRAINT][self::CONSTRAINT_INDEX_LENGTH_MAX] . ']'
                    );
                } elseif ($constraintType == self::ENUM) {
                    Precondition::isTrue(
                        in_array($value, $ruleSet[self::RULESET_INDEX_CONSTRAINT][self::CONSTRAINT_INDEX_ENUM_VALUES]),
                        'string not in enumeration: ' . $value
                    );
                } elseif ($constraintType != self::NO_CONSTRAINTS && $constraintType !== null) {
                    Assertion::isTrue(false, 'bad constraint type for string ' . $constraintType);
                }
                break;

            case self::DATE:
                // The checks are loose here.
                Precondition::isString($value, 'value');
                break;

            case self::TIME:
                // The checks are loose here.
                Precondition::isString($value, 'value');
                break;

            case self::DATE_TIME:
                // The checks are loose here.
                Precondition::isString($value, 'value');
                break;

            default:
                Assertion::isTrue(false, 'bad data type ' . $dataType);
        }
    }

    /**
     * Validates multiple values against ruleSets.
     * For each provided value, we use the array key to lookup the corresponding ruleSet.
     * A ruleSet must be provided for each value.
     *
     * @param array   $values   key/value array of values to validate
     * @param array[] $ruleSets key/value array of applicable ruleSets; each ruleSet is assumed to be valid
     *
     * @throws \PreconditionException if any values are invalid
     */
    public static function multiValidateValues($values, $ruleSets)
    {
        foreach ($values as $key => $value) {
            Precondition::arrayKeyExists($ruleSets, $key, 'ruleSets');
            try {
                self::validateValue($value, $ruleSets[$key]);
            } catch (\PreconditionException $e) {
                Precondition::isTrue(false, 'invalid ' . $key . ': ' . $e->getMessage());
            }
        }
    }

    /**
     * Adds default values to $values (such that we have a value for each ruleSet) and returns the result.
     *
     * @param array   $values   key/value array of values to add to
     * @param array[] $ruleSets key/value array of applicable ruleSets
     *
     * @throws \PreconditionException if a value is missing and no default is specified
     * @return array[]
     */
    public static function applyDefaults($values, $ruleSets)
    {
        foreach ($ruleSets as $key => $ruleSet) {
            if (!array_key_exists($key, $values)) {
                Precondition::isTrue(array_key_exists(2, $ruleSet), 'no default exists for ' . $key);

                if (is_string($ruleSet[self::RULESET_INDEX_DEFAULT]) and $ruleSet[self::RULESET_INDEX_DEFAULT] == self::CURRENT_TIME) {
                    if ($ruleSet[self::RULESET_INDEX_DATATYPE] == self::DATE) {
                        $values[$key] = date(TimeUtils::DATE_SHORT_FORMAT);
                    } elseif ($ruleSet[self::RULESET_INDEX_DATATYPE] == self::DATE_TIME) {
                        $values[$key] = date(TimeUtils::SQL_DATETIME_FORMAT);
                    } elseif ($ruleSet[self::RULESET_INDEX_DATATYPE] == self::TIME) {
                        $values[$key] = date(TimeUtils::SQL_TIME_FORMAT);
                    } else {
                        Precondition::isTrue(
                            false,
                            'bad ruleSet, currentTime as default does not apply for ' .
                                $ruleSet[self::RULESET_INDEX_DEFAULT]
                        );
                    }
                } else {
                    $values[$key] = $ruleSet[self::RULESET_INDEX_DEFAULT];
                }
            }
        }

        return $values;
    }

}
