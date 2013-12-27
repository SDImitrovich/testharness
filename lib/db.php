<?php
// database connection and schema constants
define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_USER', 'testuser');
define('DB_PASSWORD', 'testpassword');
define('DB_SCHEMA', 'testharness');

// establish a connection to the database server
if (!$GLOBALS['DB'] = mysql_connect(DB_HOST, DB_USER, DB_PASSWORD))
{
    die('Error: Unable to connect to database server.');
}
if (!mysql_select_db(DB_SCHEMA, $GLOBALS['DB']))
{
    mysql_close($GLOBALS['DB']);
    die('Error: Unable to select database schema.');
}
?>
