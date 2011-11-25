<?php

abstract class ModuleTestCase extends UnitTestCase {
	
	//--|EXCEPTION-HANDLING----------
	
	protected function expectExceptionStack(Array $cases){
		foreach( $cases as $message => $closure ){
			try {
				$closure();
				$this->fail($message);
			} catch( Exception $ex ){
				$this->pass(sprintf($message, $ex->getMessage()));
			}
		}
	}
	
}

?>