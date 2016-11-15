<?php

namespace DAG\Framework\Orm;

use DAG\Framework\Exception\Precondition;

/**
 * This base class for persistence models holds the data for the model and manages the list of dirty fields.
 *
 * @see http://confluence.corp.pinger.com/display/Eng/Object+Relational+Mapping+%28ORM%29+Design
 */
abstract class PersistenceModel
{
    /** @var array key/value array (field-to-value map) */
    protected $data = array();

    /** @var array - keys are fields that have changed since the model was last persisted; values are the original */
    private $dirtyFields = array();

    // Each subclass should have a protected static $fields and $config

    /**
     * @return PersistenceDriver
     */
    protected static function getPersistenceDriver() {
        Precondition::isArray(static::$config, 'config should be an array');

        return new PersistenceDriver(new PersistenceConfig(static::$config), static::$fields);
    }

    /**
     * Instantiates the model with the specified data.
     *
     * @param array $data key/value array (field name to value map); all fields should be provided
     */
    protected function __construct($data)
    {
        Precondition::isArray(static::$fields, 'fields should be an array');
        Precondition::isArray(static::$config, 'config should be an array');
        Precondition::isTrue(count($data) == count(static::$fields), 'missing fields');

        FieldValidator::multiValidateValues($data, static::$fields);
        $this->data = $data;
    }

    /**
     * Saves (persists) the model.
     * PersistenceModels may override this function in order to implement "ON UPDATE" type functionality.
     */
    public function save()
    {
        self::getPersistenceDriver()->save($this->data, $this->getDirtyFields());
        $this->resetDirtyFields();
    }

    /**
     * Deletes the model.
     * PersistenceModels may override this to disable deleting, etc.
     */
    public function delete()
    {
        self::getPersistenceDriver()->deleteOne($this->data);
    }

    // Manage dirty fields

    /**
     * Magic method to access a field value.
     *
     * @param string $field field name
     *
     * @return mixed field value
     */
    public function __get($field) {
        Precondition::isTrue(
            array_key_exists($field, static::$fields),
            'field does not exist in this model: ' . $field
        );

        return $this->data[$field];
    }

    /**
     * Magic method to set a field value; this will mark the field as dirty / modified.
     *
     * @param string $field field name
     * @param string $value field value
     */
    public function __set($field, $value) {
        Precondition::isTrue(
            array_key_exists($field, static::$fields),
            'field does not exist in this model: ' . $field
        );

        FieldValidator::validateValue($value, static::$fields[$field]);
        if (!array_key_exists($field, $this->dirtyFields)) {
            $this->dirtyFields[$field] = $this->data[$field];
        }
        $this->data[$field] = $value;
    }

    /**
     * Magic method to check whether a field is set or not. This method is triggered by isset() or empty() functions.
     *
     * @param string $field
     *
     * @return bool
     */
    public function __isset($field)
    {
        return isset($this->data[$field]);
    }

    /**
     * Determines if a field has been modified since it was last persisted.
     *
     * @param string $field field name
     *
     * @return bool
     */
    protected function isDirty($field) {
        return array_key_exists($field, $this->dirtyFields);
    }

    /**
     * Provides an array of dirty fields.
     * This should be used when the object is persisted.
     *
     * @return array where the keys are the names of the dirty fields
     */
    protected function getDirtyFields() {
        $result = $this->dirtyFields;
        return $result;
    }

    /**
     * Clears the array of dirty fields, indicating that all data in the model has been persisted.
     */
    protected function resetDirtyFields()
    {
        $this->dirtyFields = array();
    }
}
