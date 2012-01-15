<?php
require_once( dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) . DIRECTORY_SEPARATOR . 'site.bootstrap.php');

$REST = new REST_Response();
if ( isset($_GET['callback']) ) {
	$REST->setCallback( $_GET['callback'] );
}

// Input args
$args = new StdClass;
$args->path = '';

if ( preg_match('#/folder/(.*)$#', $_SERVER['REQUEST_URI'], $matched ) ) {
	$args->path = $matched[1];
} else {
	$REST->error('Invalid path specified');
	exit;
}

if ( REST_verb_is('GET') ) {
	$listing = dpweb_list_folder($args->path);
	
	if ( !$listing ) {
		$REST->error("Path '{$args->path}' not found");
		exit;
	}
	
	$REST->header('status', 302);
	$REST->success($listing);
} else {
	$REST->error('This verb has not been implemented yet');
}