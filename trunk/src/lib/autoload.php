<?php
/**
 * Define the autoloader.
 * All classes called will be automatically required into the system
 */


// Define path to the base path directory
defined('SRC_LIB') || define('SRC_LIB', realpath(dirname(__FILE__)) . '/');
defined('SRC') || define('SRC', SRC_LIB . '../');
defined('SRC_ROOT') || define('SRC_ROOT', SRC);

// Set the include path to be
// lib
set_include_path(
                 implode(PATH_SEPARATOR,
                        array(
                            realpath(SRC),
                            realpath(SRC_LIB),
                            get_include_path(),)
                        )
                );

// Always used so require them now
require_once('config.php');
require_once('common/exception.php');
require_once('common/object.php');
require_once('common/dataObject.php');
require_once('common/databaseConfig.php');
require_once('common/database.php');
require_once('common/factory.php');

/* New Framework */
require_once SRC . 'src/Framework/Exception/DAGException.php';
require_once SRC . 'src/Framework/Exception/PreconditionException.php';
require_once SRC . 'src/Framework/Exception/AssertionException.php';

// The vendor autoloading file
if (!file_exists(SRC . 'vendor/autoload.php')) {
    die('Please run `composer install` to generate your vendor directory');
}
require_once SRC . '/vendor/autoload.php';


// Register the auto loader
spl_autoload_register('dag_autoloader');

/**
 * dag_autoloader will do the auto loading of models.
 *
 * @param string $className is the class name and must adhere to the following format:
 *                          class DIR_DIR2_Name => dir1/dir2/.../dirN/Name.php
 */
function dag_autoloader($className)
{
    $bits = explode('_', $className);
    $fileName = array_pop($bits);
    $fileName = lcfirst($fileName);

    for ($i = 0; $i < count($bits); ++$i) {
        $bits[$i] = lcfirst($bits[$i]);
    }

    $path = implode('/', $bits);

    // $path = strtolower(implode('/', $bits));
    $filePath = (!empty($path)) ? "$path/$fileName.php" : "$fileName.php";

    // print "loading $className -- $filePath <BR>";
    // Need for backwards compatibility
    if (stream_resolve_include_path($filePath) !== FALSE) {
        require_once $filePath;
    }
}
