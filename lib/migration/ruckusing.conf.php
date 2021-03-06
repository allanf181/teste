<?php
include "inc/config.inc.php";

date_default_timezone_set('UTC');

//----------------------------
// DATABASE CONFIGURATION
//----------------------------

/*

Valid types (adapters) are Postgres & MySQL:

'type' must be one of: 'pgsql' or 'mysql' or 'sqlite'

*/
return array(
    'db' => array(
        'development' => array(
            'type' => 'mysql',
            'host' => MY_HOST,
            'port' => MY_PORT,
            'database' => MY_DB,
            'user' => MY_USER,
            'password' => MY_PASS,
            //'charset' => 'utf8',
            //'directory' => 'custom_name',
            //'socket' => '/var/run/mysqld/mysqld.sock'
        )
    ),
    'migrations_dir' => array('default' => RUCKUSING_WORKING_BASE . '/migrations'),
    'db_dir' => RUCKUSING_WORKING_BASE . DIRECTORY_SEPARATOR . 'db',
    'log_dir' => sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'logs',
    'ruckusing_base' => dirname(__FILE__) . DIRECTORY_SEPARATOR . ''
);
