<?php

//--|INCLUDES----------

require_once 'ToolBoxModule.absclass.php';



//--|CLASS----------

class ToolBoxModuleString extends ToolBoxModule {

	// ***
	public function __construct($moduleName, $addedArgs){
		parent::__construct($moduleName, $addedArgs);
	}
	// ***
	
	
	
	public function escapeForRegExp($string){
		return str_replace('/', '\/', preg_quote($string));
	}
	
	
	
	public function decodeUnicodeCodes($chrs){
		$delim = substr($chrs, 0, 1);
		$utf8 = '';
		$strlen_chrs = strlen($chrs);

		for($i = 0; $i < $strlen_chrs; $i++) {
			$substr_chrs_c_2 = substr($chrs, $i, 2);
			$ord_chrs_c = ord($chrs[$i]);

			switch (true) {
				case preg_match('/\\\u[0-9A-F]{4}/i', substr($chrs, $i, 6)):
					$utf16 = chr(hexdec(substr($chrs, ($i + 2), 2)))
									. chr(hexdec(substr($chrs, ($i + 4), 2)));
					$utf8 .= mb_convert_encoding($utf16, 'UTF-8', 'UTF-16');
					$i += 5;
				break;
				
				case ($ord_chrs_c >= 0x20) && ($ord_chrs_c <= 0x7F):
					$utf8 .= $chrs{$i};
				break;
				
				case ($ord_chrs_c & 0xE0) == 0xC0:
					$utf8 .= substr($chrs, $i, 2);
					++$i;
				break;
				
				case ($ord_chrs_c & 0xF0) == 0xE0:
					$utf8 .= substr($chrs, $i, 3);
					$i += 2;
				break;
				
				case ($ord_chrs_c & 0xF8) == 0xF0:
					$utf8 .= substr($chrs, $i, 4);
					$i += 3;	
				break;
				
				case ($ord_chrs_c & 0xFC) == 0xF8:
					$utf8 .= substr($chrs, $i, 5);
					$i += 4;
				break;
				
				case ($ord_chrs_c & 0xFE) == 0xFC:
					$utf8 .= substr($chrs, $i, 6);
					$i += 5;
				break;
			}
		}

		return $utf8;
	}
	
	
	
	public function parseDbDistinctInformation($string, $separator=':'){
		$res = new StdClass();
		
		$string = preg_replace('/\r\n|\r[^\n]|[^\r]\n/u', '..:NL:..', "$string");
		$pieces = explode('..:NL:..', $string);
		
		foreach( $pieces as $keyValue ){
			$keyValue = explode($separator, $keyValue, 2);
			$res->{trim($keyValue[0])} = trim($keyValue[1]);
		}
		
		return $res;
	}
	
	
	
	public function truncate($string, $charCount = 80, $suffix = '...'){
		$truncString = mb_substr("$string", 0, $charCount); 
		return (mb_strlen($string) != mb_strlen($truncString)) ? $truncString.$suffix : $truncString;
	}
	
}

?>