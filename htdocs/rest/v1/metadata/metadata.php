<?php
require_once( dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) . DIRECTORY_SEPARATOR . 'site.bootstrap.php');

$REST = new REST_Response();
if ( isset($_GET['callback']) ) {
	$REST->setCallback( $_GET['callback'] );
}

// Input args
$args = new StdClass;
$args->path = '';

if ( preg_match('#metadata/(.*)$#', $_SERVER['REQUEST_URI'], $matched ) ) {
	$args->path = $matched[1];
} else {
	$REST->error('Invalid path specified');
	exit;
}

if ( REST_verb_is('GET') ) {
	$path = dpweb_safe_library_path($args->path);
	
	// We're going to try and guess if this is a file
	if ( preg_match('/(\.[a-z0-9]{2,4})$/i', $args->path) && file_exists($path) ) {
		$metadata = dpweb_file_metadata($args->path);
	} else if ( is_dir($path) ) {
		$metadata = dpweb_folder_metadata($args->path);
	} else {
		$REST->error("Path '{$args->path}' does not exist");
		exit;
	}
	
	if ( !$metadata ) {
		$REST->error("Path '{$args->path}' not found");
		exit;
	}
	
	$REST->header('status', 302);
	$REST->success($metadata);
} else {
	$REST->error('This verb has not been implemented yet');
}