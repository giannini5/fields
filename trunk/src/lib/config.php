<?php
define('DB_FIELDS_RW', 'fields_rw');
define('DATABASE_CONNECTION_TIMEOUT', 15);

global $gDBConnectInfo;
$gDBConnectInfo = array(
    DB_FIELDS_RW => array(
        'host'=>'localhost',
        'port'=>'3306',
        'db'=>'fields',
        'user'=>'dag',
        'pwd'=>'dag',
        'timeout'=>5,
        'persistant'=>0));
?>
