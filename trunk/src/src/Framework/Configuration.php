<?php

namespace DAG\Framework;

/**
 * Provides access to configuration values from config.php
 * Optionally allows configuration items to be overridden
 */
class Configuration
{
    // Class constants
    const CONFIG_LOG_LEVEL = "LOG_LEVEL";
    const PVN_STUB_ENABLED = "PVN_STUB_ENABLED";

    /** @var array Set of overridden configs */
    protected $overrides;

    /** @var array Temporary location to store overrides when overrides are overridden (ie for automated testing) */
    protected $overriddenOverrides;

    /**
     * Creates a Configuration with the specified overrides
     *
     * @param array $overrides key/value array which takes precedence over the values in config.php
     */
    public function __construct(array $overrides = array())
    {
        $this->overrides = $overrides;
        $this->overriddenOverrides = array();
    }

    /**
     * Get the value for a configuration item
     *
     * @param string $key the name of the configuration item
     *
     * @return mixed value for the configuration item
     */
    public function get($key)
    {
        precondition(defined($key), "$key is not defined");

        if (array_key_exists($key, $this->overrides)) {
            return $this->overrides[$key];
        }

        return constant($key);
    }

    /**
     * Adds a temporary override item to the configuration, and saves the current override value; only 1 such value can be backed-up
     *
     * @param string $key   the name of the configuration item to override
     * @param mixed  $value the value to override with
     */
    public function addOverride($key, $value)
    {
        precondition(defined($key), "$key is not defined");

        if (array_key_exists($key, $this->overrides)) {
            $this->overriddenOverrides[$key] = $this->overrides[$key];
        }
        $this->overrides[$key] = $value;
    }

    /**
     * Removes an override item from the configuration, or puts back an overridden override
     *
     * @param string $key the name of the configuration item to remove or restore
     */
    public function removeOverride($key)
    {
        precondition(defined($key), "$key is not defined");

        if (array_key_exists($key, $this->overriddenOverrides)) {
            $this->overrides[$key] = $this->overriddenOverrides[$key];
            unset($this->overriddenOverrides[$key]);
        } else {
            unset($this->overrides[$key]);
        }
    }
}
