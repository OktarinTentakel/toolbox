<?php

//--|INCLUDES----------

require_once 'ModuleTestCase.absclass.php';

require_once 'toolbox/ToolBox.class.php';



//--|CLASS----------

class DateTimeTest extends ModuleTestCase {

	// ***
	public function __construct(){
		parent::__construct('DateTimeTest');
	}
	// ***
	
	
	
	//--|PREPARATION----------
	
	public function setUp(){
		date_default_timezone_set('Europe/Berlin');
	}
	



	//--|TESTS----------
	
	public function testMiscToStandardDate(){
		$this->assertEqual(ToolBox::_datetime_()->miscToStandardDate('3. December 2001 10am'), '2001-12-03', 'natural datetime string results in ISO-date <%s>');		
		$this->assertEqual(ToolBox::_datetime_()->miscToStandardDate('2022-02-22T16:08:22+0100', DateTime::ISO8601), '2022-02-22', 'strict ISO-datetime results in ISO-date <%s>');
		$this->assertEqual(ToolBox::_datetime_()->miscToStandardDate('15/2011/01/03/02/01', 'H/Y/i/m/s/d', DateTime::ISO8601), '2011-03-01T15:01:02+0100', 'nonsensical datetime results in strict ISO-date <%s>');
	}
	
	
	
	public function testMiscToStandardTime(){
		$this->assertEqual(ToolBox::_datetime_()->miscToStandardTime('3. December 2001 10pm'), '22:00:00', 'natural datetime string results in ISO-time <%s>');
		$this->assertEqual(ToolBox::_datetime_()->miscToStandardTime('Monday, 15-Aug-05 15:52:01 UTC', DateTime::COOKIE), '15:52:01', 'cookie-datetime-string results in ISO-time <%s>');
		$this->assertEqual(ToolBox::_datetime_()->miscToStandardTime('2001-01-22', null, DateTime::COOKIE), 'Monday, 22-Jan-01 00:00:00 CET', 'simple iso date without time results in complete, completed cookie-date <%s>');
	}
	
	
	
	public function testMiscToStandardDateTime(){
		$this->assertEqual(ToolBox::_datetime_()->miscToStandardDateTime('01/31/2001 10pm'), '2001-01-31 22:00:00', 'american short datetime string results in ISO-datetime <%s>');
		$this->assertEqual(ToolBox::_datetime_()->miscToStandardDateTime('15/2011/01/03/02/01', 'H/Y/i/m/s/d'), '2011-03-01 15:01:02', 'nonsensical formatted datetime string results in ISO-datetime <%s>');
		$this->assertEqual(ToolBox::_datetime_()->miscToStandardTime('15:16:17', null, DateTime::ATOM), date('Y-m-d').'T15:16:17+01:00', 'simple iso time without date results in complete, completed atom-date <%s>');
	}
	
}

?>