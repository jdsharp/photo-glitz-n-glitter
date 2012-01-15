<?php
require_once( dirname(__FILE__) . DIRECTORY_SEPARATOR . 'site.bootstrap.php' );

// Input args
$args = new StdClass;
$args->file   = '';
$args->src    = '';
$args->width  = null;
$args->height = null;
$args->cache  = true;

$valid_sizes = array(50,100,125,150,200,225,250,400,640,800,1024,1280,768);

if ( preg_match('#library/(.*)$#', $_SERVER['REQUEST_URI'], $matched ) ) {
	$args->file = $matched[1];
	$args->src  = $matched[1];
	if ( preg_match('#^(.+)(?:x(\d+)-(\d+))(\..+)$#', $args->file, $matched) ) {
		if ( in_array($matched[2], $valid_sizes) && in_array($matched[3], $valid_sizes)  ) {
			$args->src    = $matched[1] . $matched[4];
			$args->width  = $matched[2];
			$args->height = $matched[3];
		} else {
			echo 'Invalid size (' . $matched[2] . ',' . $matched[3] . ') valid sizes are: ' . implode(', ', $valid_sizes);
			exit;
		}
	}
} else {
	echo 'Invalid path';
	exit;
}

// Handle cover photos
if ( basename($args->src) == '_.jpg' ) {
	$folder   = dpweb_safe_library_path( dirname($args->src) );
	$metadata = dpweb_folder_metadata($folder);
	if ( !empty($metadata['image']) ) {
		$args->src = $metadata['image'];
	} else {
		$args->src   = 'default_thumbnail.jpg';
		$args->cache = false;
	}
}

$src = dpweb_safe_library_path($args->src);
if ( !file_exists($src) ) {
	echo 'Invalid path';
	exit;
}

$metadata = dpweb_file_metadata($src);

if ( $args->width === null ) {
	// We're returning the original one
	header('Content-Type: '   . $metadata['meta']);
	header('Content-Length: ' . $metadata['filesize']);
	$fp = fopen($src, 'rb');
	if ( $fp ) {
		fpassthru($fp);
		fclose($fp);
	}
	exit;
} else if ( $args->width == 0 ) {
	echo 'Invalid size';
	exit;
}

$cache = dpweb_safe_cache_path($args->file);
if ( !dpweb_create_cache_path($cache) ) {
	echo 'Failed to create cache path';
	exit;
}

$transform = array(
	6 => 270,
	8 => 90
);

$rotate = isset($transform[$metadata['orientation']]) ? $transform[$metadata['orientation']] : null;

if ( dpweb_resize_image($src, $args->width, $args->height, ( $args->cache === true ? $cache : true ), 55, $rotate ) ) {
	if ( $args->cache ) {
		header('Content-Type: image/jpeg');
		$filesize = filesize($cache);
		header('Content-Length: ' . $filesize);
		$fp = fopen($cache, 'rb');
		if ( $fp ) {
			fpassthru($fp);
			fclose($fp);
		}
	}
} else {
	echo 'Failed to create cache thumbnail';
}