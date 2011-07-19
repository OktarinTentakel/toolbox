<?php

//--|INCLUDES----------

require_once 'ToolBoxModule.absclass.php';



//--|CLASS----------

class ToolBoxModuleImage extends ToolBoxModule {
	
	// ***
	public function __construct($moduleName, $addedArgs){
		parent::__construct($moduleName, $addedArgs);
	}
	// ***
	
	
	
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

}

?>