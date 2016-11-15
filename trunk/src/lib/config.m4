<?php
M4_PHP_DEFINE('DB_FIELDS_RW', 'M4_DB_FIELDS_RW');
M4_PHP_DEFINE('DB_SCHEDULE_RW', 'M4_DB_SCHEDULE_RW');

M4_PHP_DEFINE('DATABASE_CONNECTION_TIMEOUT', M4_DATABASE_CONNECTION_TIMEOUT);
M4_PHP_DEFINE('DB_DEFAULT_PORT', M4_DB_DEFAULT_PORT);
M4_PHP_DEFINE('DB_DEFAULT_PERSISTENT', M4_DB_DEFAULT_PERSISTENT);
M4_PHP_DEFINE('DB_DEFAULT_TIMEOUT', M4_DB_DEFAULT_TIMEOUT);

global $gDBConnectInfo;
$gDBConnectInfo = array(
    DB_FIELDS_RW => array(
        'host'=>'M4_DB_HOST',
        'port'=>'M4_DB_PORT',
        'db'=>'M4_DB_SCHEMA',
        'user'=>'M4_DB_USER',
        'pwd'=>'M4_DB_PASSWORD',
        'timeout'=>M4_DB_TIMEOUT,
        'persistant'=>M4_DB_PERSISTANT),

    DB_SCHEDULE_RW => array(
        'host'=>'M4_DB_HOST',
        'port'=>'M4_DB_PORT',
        'db'=>'M4_DB_SCHEDULE_SCHEMA',
        'user'=>'M4_DB_USER',
        'pwd'=>'M4_DB_PASSWORD',
        'timeout'=>M4_DB_TIMEOUT,
        'persistant'=>M4_DB_PERSISTANT),
    );
?>
