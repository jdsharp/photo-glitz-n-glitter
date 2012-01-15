<?php

function dpweb_safe_library_path($path)
{
	$path = str_replace(DPWEB_LIBRARY, '', $path);
	$path = preg_replace('#^([/\\\]+)#', '', $path);
	return DPWEB_LIBRARY . DIRECTORY_SEPARATOR . $path;
}

function dpweb_safe_cache_path($path)
{
	$path = str_replace(DPWEB_CACHE, '', $path);
	$path = preg_replace('#^([/\\\]+)#', '', $path);
	return DPWEB_CACHE . DIRECTORY_SEPARATOR . $path;
}

function dpweb_create_cache_path($path)
{
	$path = str_replace(DPWEB_CACHE, '', $path);
	$path = preg_replace('#^([/\\\]+)#', '', $path);
	$path = preg_replace('#([/\\\]+[^/\\\]+\.[a-z0-9]{2,4})$#i', '', $path);
	$path = DPWEB_CACHE . DIRECTORY_SEPARATOR . $path;
	if ( !is_dir($path) ) {
		if ( !mkdir($path, 0777, true) ) {
			return false;
		}
	}
	return true;
}

function dpweb_filter($test, $filter = null)
{
	if ( $test == '.' || $test == '..' ) {
		return false;
	}
	if ( DPWEB_LIBRARY_INCLUDE != '' ) {
		return preg_match(DPWEB_LIBRARY_INCLUDE, $test);
	}
	if ( $filter !== null ) {
		return preg_match($filter, $test);
	}
	return true;
}

function dpweb_folder_metadata($url = '', $filter = null)
{
	$path = dpweb_safe_library_path($url);
	if ( is_dir($path) ) {
		$dh = dir($path);
		$entries = array( 'folders' => array(), 'files' => array() );
		while ( ($entry = $dh->read()) !== false ) {
			if ( $entry == '.' || $entry == '..' ) {
				continue;
			}
			if ( is_dir( $path . DIRECTORY_SEPARATOR . $entry ) ) {
				$entries['folders'][] = $entry;
			} else if ( dpweb_filter($entry, $filter) ) {
				$entries['files'][] = $entry;
			}
		}
		$dh->close();
		
		if ( file_exists($path . DIRECTORY_SEPARATOR . DPWEB_LIBRARY_METADATA) ) {
			$metadata = json_decode( file_get_contents($path . DIRECTORY_SEPARATOR . DPWEB_LIBRARY_METADATA) );
		} else {
			$metadata = array(
				'title' => basename($path),
				'image' => isset($entries['files'][0]) ? $url . '/' . $entries['files'][0] : ''
			);
		}
		$metadata['file_count'] = count($entries['files']);
		return $metadata;
	}
	return false;
}

function dpweb_list_folder($path = '', $filter = null)
{
	$path = dpweb_safe_library_path($path);
	
	if ( is_dir($path) ) {
		$dh = dir($path);
		$paths = str_replace(DPWEB_LIBRARY, '', $path);
		if ( substr($paths, 0, 1) == '/' ) {
			$paths = substr($paths, 1);
		}
		if ( substr($paths, -1) == '/' ) {
			$paths = substr($paths, 0, -1);
		}
		$entries = array( 'path' => explode('/', $paths), 'folders' => array(), 'files' => array() );
		
		while ( ($entry = $dh->read()) !== false ) {
			if ( $entry == '.' || $entry == '..' || ( ( $filter !== null ) && ( !preg_match('#' . $filter . '#', $entry) ) ) ) {
				continue;
			}
			if ( is_dir( $path . DIRECTORY_SEPARATOR . $entry ) ) {
				$entries['folders'][] = $entry;
			} else if ( dpweb_filter($entry, $filter) ) {
				$entries['files'][] = $entry;
			}
		}
		$dh->close();
		
		asort($entries['folders']);
		asort($entries['files']);
		
		return $entries;
	}
	return false;
}

function dpweb_file_metadata($path)
{
	$path = dpweb_safe_library_path($path);
	if ( !file_exists($path) ) {
		return false;
	}
	
	$basename = basename($path);
	$basename = preg_replace('/(\.[a-z0-9]{2,4})$/i', '', $basename);
	
	$metadata_file = $path . DIRECTORY_SEPARATOR . $basename . DPWEB_FILE_METADATA;
	if ( file_exists($metadata_file) ) {
		$metadata = json_decode( file_get_contents($metadata_file) );
		if ( $metadata ) {
			return $metadata;
		}
	} else {
		$metadata = array(
			'datetime' => '0000-00-00 00:00:00',
			'tags'     => array(),
			'basename' => $basename,
			'ext'      => strtolower( preg_replace('/^(.*)(\.[a-z0-9]{2,4})$/i', '$2', $path) ),
			'filesize' => 0,
			'datetime' => '0000-00-00 00:00:00',
			'width'    => 0,
			'height'   => 0,
			'mime'     => ''
		);
	}
	
	$exif = exif_read_data($path, 'FILE');
	if ( $exif ) {		
		$metadata['orientation'] = $exif['Orientation'];
		$metadata['filesize']    = $exif['FileSize'];
		$metadata['datetime']    = date('Y-m-d H:i:s', strtotime($exif['DateTime']));
		$metadata['width']       = $exif['COMPUTED']['Width'];
		$metadata['height']      = $exif['COMPUTED']['Height'];
		$metadata['mime']        = $exif['MimeType'];
	}
	return $metadata;
}

// The ratio will be maintained, so it won't be larger than either the width or height provided
function dpweb_resize_image($src, $width, $height, $output, $compression = 55, $rotate = null)
{
	if ( !file_exists($src) ) {
		return false;
	}
	
	$img = new SimpleImage;
	if ( !$img->load($src) ) {
		return false;
	}
	$img->resizeWithMax($width, $height);
	
	if ( $rotate !== null ) {
		$img->rotate($rotate);
	}
	
	if ( $output !== true ) {
		$img->save($output, IMAGETYPE_JPEG, $compression);
	} else {
		header('Content-type: image/jpeg');
		$img->output(IMAGETYPE_JPEG);
	}
	return true;
}