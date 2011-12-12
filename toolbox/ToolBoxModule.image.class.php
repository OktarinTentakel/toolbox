<?php

//--|INCLUDES----------

require_once 'ToolBoxModule.absclass.php';



//--|CLASS----------

/**
 * ToolBoxModuleImage contains helper methods for dealing with images and image contents.
 * Normal use cases would be coversion of color values and getting special information about image
 * contents as well as manipulation.
 * 
 * @author Sebastian Schlapkohl
 * @version 0.25 alpha
 * @package modules
 * @subpackage media
 */
class ToolBoxModuleImage extends ToolBoxModule {
	
	// ***
	public function __construct($moduleName, Array $addedArgs = null){
		parent::__construct($moduleName, $addedArgs);
	}
	// ***
	
	
	
	//--|TOPLEVEL----------
	
	/**
	 * Calculates an array of dominant color-hexvalues by aggregating colors under rounded color codes.
	 * This method can be used for such things like finding out if an image is particularily bright or dark
	 * or contains unusual high amounts of yellow.
	 *
	 * @param String $image the filesystem path to the image to process
	 * @throws Exception if file could not be found or opened
	 * @return Array list of dominant color values for the given image
	 */
	public function getDominantColors($image){
		if( file_exists($image) ){
			$size = getimagesize($image);
			
			$scale=1;
			if( $size[0] > 0 ){
				$scale = min(150 / $size[0], 150 / $size[1]);
			}
				
			if( $scale < 1 ){
				$width = floor($scale * $size[0]);
				$height = floor($scale * $size[1]);
			}	else {
				$width = $size[0];
				$height = $size[1];
			}
			
			$resizedImage = imagecreatetruecolor($width, $height);
			
			switch( $size[2] ){
				case IMAGETYPE_GIF:
					$originalImage = imagecreatefromgif($image);
				break;
				
				case IMAGETYPE_JPEG:
					$originalImage = imagecreatefromjpeg($image);
				break;
				
				case IMAGETYPE_PNG:
					$originalImage = imagecreatefrompng($image);
				break;
			}
			
			imagecopyresized($resizedImage, $originalImage, 0, 0, 0, 0, $width, $height, $size[0], $size[1]);
			
			$hexArray = array();
			for( $y=0; $y < imagesy($resizedImage); $y++ ){
				for( $x=0; $x < imagesx($resizedImage); $x++ ){
					$index = imagecolorat($resizedImage, $x, $y);
					$colors = imagecolorsforindex($resizedImage, $index);
					$colors['red' ] = intval((($colors['red']) + 15) / 32) * 32;
					$colors['green'] = intval((($colors['green']) + 15) / 32) * 32;
					$colors['blue'] = intval((($colors['blue']) + 15) / 32) * 32;
					
					foreach( array('red', 'green', 'blue') as $colorName ){
						if( $colors[$colorName] >= 256 ){
							$colors[$colorName] = 240;
						}
					}
					
					$hexArray[] = substr('0'.dechex($colors['red']), -2).substr('0'.dechex($colors['green']), -2).substr('0'.dechex($colors['blue']), -2);
				}
			}
			
			$hexArray = array_count_values($hexArray);
			natsort($hexArray);
			$hexArray = array_reverse($hexArray, true);
			
			return $hexArray;
		} else {
			$this->throwModuleException(__FUNCTION__.': io-error, file not found');
		}
	}
	
	
	
	/**
	 * Reads a standard color hex-string and returns an array of decimal values, representing the given color.
	 *
	 * @param String $hexColorString the color to convert as a hex-string with or without leading hash
	 * @param Float $alpha alpha value between 0.0 and 1.0 to add to the RGB-value to create a RGBA-value, too high or low values will be normalized
	 * @param Boolean $asCssString determines if the output should be a css-compatible rgba-string instead of an array
	 * @throws Exception if hex-string format can not be read
	 * @return Array decimal representation of the color with 3 values (RGB)
	 */
	public function hexColorToDecArray($hexColorString, $alpha = null, $asCssString = false){
		$matches = array(); 
		if( preg_match('/^#?([0-9a-fA-F]{6})$/', $hexColorString, $matches) ){
			$hexColorString = $matches[1];
			$red = substr($hexColorString, 0, 2);
			$green = substr($hexColorString, 2, 2);
			$blue = substr($hexColorString, 4, 2);
			
			$alpha =
				is_null($alpha)
				? null
				: ((floatval($alpha) < 0.0)
					? 0.0 
					: ((floatval($alpha) > 1.0)
						? 1.0 
						: floatval($alpha)
					)
				)
			;
			
			if( is_null($alpha) ){
				return array(hexdec($red), hexdec($green), hexdec($blue));
			} else {
				if( !$asCssString ){
					return array(hexdec($red), hexdec($green), hexdec($blue), $alpha);
				} else {
					return 'rgba('.hexdec($red).', '.hexdec($green).', '.hexdec($blue).', '.$alpha.')';
				}
			}
		} else {
			$this->throwModuleException(__FUNCTION__.': wrong format, argument no hex-color-string');
		}
	}

}

?>