<?php
/*
 * File: SimpleImage.php
 * Author: Simon Jarvis
 * Copyright: 2006 Simon Jarvis
 * Date: 08/11/06
 * Link: http://www.white-hat-web-design.co.uk/articles/php-image-resizing.php
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details:
 * http://www.gnu.org/licenses/gpl.html
 *
 */
/**
 * Modified by Jonathan Sharp to include the resizeAndCrop method
 */

class SimpleImage {
 
   var $image;
   var $image_type;
 
   function load($filename) { 
      $image_info = getimagesize($filename);
      $this->image_type = $image_info[2];
      if( $this->image_type == IMAGETYPE_JPEG ) {
 
         $this->image = imagecreatefromjpeg($filename);
      } elseif( $this->image_type == IMAGETYPE_GIF ) {
 
         $this->image = imagecreatefromgif($filename);
      } elseif( $this->image_type == IMAGETYPE_PNG ) {
 
         $this->image = imagecreatefrompng($filename);
      }
	  if ( $this->image ) {
	     return true;
	  }
	  return false;
   }
   function save($filename, $image_type = IMAGETYPE_JPEG, $compression=75, $permissions = null) {
      if( $image_type == IMAGETYPE_JPEG ) {
         imagejpeg($this->image,$filename,$compression);
      } elseif( $image_type == IMAGETYPE_GIF ) {
 
         imagegif($this->image,$filename);
      } elseif( $image_type == IMAGETYPE_PNG ) {
 
         imagepng($this->image,$filename);
      }
      if( $permissions != null) {
 
         chmod($filename,$permissions);
      }
   }
   function output($image_type=IMAGETYPE_JPEG) {
 
      if( $image_type == IMAGETYPE_JPEG ) {
         imagejpeg($this->image);
      } elseif( $image_type == IMAGETYPE_GIF ) {
 
         imagegif($this->image);
      } elseif( $image_type == IMAGETYPE_PNG ) {
 
         imagepng($this->image);
      }
   }
   function getWidth() {
 
      return imagesx($this->image);
   }
   function getHeight() {
 
      return imagesy($this->image);
   }

   function resizeWithMax($width,$height)
   {
      $ratio_w = $width / $this->getWidth();
      $ratio_h = $height / $this->getHeight();
      if ( $ratio_w < $ratio_h ) {
	     $height = floor( $ratio_w * $this->getHeight() );
	  } else {
	     $width = floor( $ratio_h * $this->getWidth() );
	  }
	  return $this->resize($width, $height);
   }
   
   function resizeToHeight($height) {
 
      $ratio = $height / $this->getHeight();
      $width = $this->getWidth() * $ratio;
      $this->resize($width,$height);
   }
 
   function resizeToWidth($width) {
      $ratio = $width / $this->getWidth();
      $height = $this->getheight() * $ratio;
      $this->resize($width,$height);
   }
 
   function scale($scale) {
      $width = $this->getWidth() * $scale/100;
      $height = $this->getheight() * $scale/100;
      $this->resize($width,$height);
   }
 
   function resize($width,$height) {
      $new_image = imagecreatetruecolor($width, $height);
      imagecopyresampled($new_image, $this->image, 0, 0, 0, 0, $width, $height, $this->getWidth(), $this->getHeight());
      $this->image = $new_image;
   }
   
	function rotate($degrees)
	{
		$tmp = imagerotate($this->image, $degrees, 0);
		$this->image = null;
		$this->image = $tmp;
	}


	function resizeAndCrop($width, $height)
	{
		$tmp = new self();
		$tmp->image = imagecreatetruecolor($width, $height);
		$tmp->image_type = $this->image_type;
		
		// Start with the bigger side:
		$output = array( $width, $height );
		$source = array( $this->getWidth(), $this->getHeight() );
		$swapped = false;
		if ( $width < $height ) {
			$swapped = true;
			// First index is always bigger
			$output[] = array_unshift($output);
			$source[] = array_unshift($source);
		}
		
		$ratio = $output[1] / $output[0];
		$sizes = array(
			0,					// Our 'x' coordinate
			$source[0],			// Our 'width'
			round( ( $source[1] - ( $source[1] * $ratio ) ) / 2 ), // Our 'y' coordinate
			$source[1] * $ratio // Our 'height'
		);
		
		if ( $swapped ) {
			$sizes[]  = array_unshift($sizes);
			$sizes[]  = array_unshift($sizes);
		}
		
		$x = $sizes[0];
		$y = $sizes[2];
		$srcW = $sizes[1];
		$srcH = $sizes[3];
		
		imagecopyresampled($tmp->image, $this->image, 0, 0, $x, $y, $width, $height, $srcW, $srcH);
		return $tmp;
	} 
}