<?php

class Test {
	
	public function objectMethod($arg1, $arg2, $arg3 = null){
		echo 'objectMethod';
		print_r($arg1);
		print_r($arg2);
		print_r($arg3);
	}
	
	
	
	public function mixedGetMethod($arg1, $arg2, $arg3 = null){
		echo 'mixedGetMethod';
		print_r($arg1);
		print_r($arg2);
		print_r($arg3);
		print_r($_GET);
	}
	
	
	
	public static function staticFunction($arg1, $arg2, $arg3 = null){
		echo 'staticFunction';
		print_r($arg1);
		print_r($arg2);
		print_r($arg3);
	}
	
	
	
	public static function indexFunction(){
		echo 'INDEX';
	}
	
	
	
	public static function fourOfourFunction(){
		echo '404';
	}
	
}


function globalFunction($arg1, $arg2, $arg3 = null){
	echo 'globalFunction';
	print_r($arg1);
	print_r($arg2);
	print_r($arg3);
}

?>