<?php
date_default_timezone_set('UTC');

ini_set('display_startup_errors', true);
ini_set('display_errors', true);
ini_set('error_reporting', E_ALL & ~E_NOTICE);

// Set includes path to include this folder
ini_set('include_path', ini_get('include_path') . ':' . dirname(__FILE__) . ':' . dirname( dirname( __FILE__ ) ) . '/lib' );

require_once( dirname( dirname(__FILE__) ) . DIRECTORY_SEPARATOR . 'config.php' );

require_once( dirname(__FILE__) . DIRECTORY_SEPARATOR . 'restkit.php' );
require_once( dirname(__FILE__) . DIRECTORY_SEPARATOR . 'simpleimage.php' );
require_once( dirname(__FILE__) . DIRECTORY_SEPARATOR . 'dplibrary.func.php' );