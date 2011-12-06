<?php

//--|INCLUDES----------

require_once 'ModuleTestCase.absclass.php';

require_once 'toolbox/ToolBox.class.php';



//--|CLASS----------

class FileSystemTest extends ModuleTestCase {

	// ***
	public function __construct(){
		parent::__construct('FileSystemTest');
	}
	// ***
	
	
	
	//--|TESTS----------	
	
	public function testIsEmptyDirectory(){
		$this->assertFalse(ToolBox::_filesystem_()->isEmptyDirectory(dirname(__FILE__).'/../misc'), 'directory containing another directory and files is not empty <%s>');
		$this->assertTrue(ToolBox::_filesystem_()->isEmptyDirectory(dirname(__FILE__).'/../misc/mustbeempty'), 'directory containing nothing is empty <%s>');
		
		$this->expectException('Exception', 'missing directory causes exception <%s>');
		ToolBox::_filesystem_()->isEmptyDirectory(dirname(__FILE__).'/../misc/doesnotexist');
	}
	
	
	
	public function testCountDirectoryFiles(){
		$this->assertEqual(ToolBox::_filesystem_()->countDirectoryFiles(dirname(__FILE__).'/../misc/'), 2, 'testdirectory contains certain number of true files (no directories counted) <%s>');
		$this->assertEqual(ToolBox::_filesystem_()->countDirectoryFiles(dirname(__FILE__).'/../misc/mustbeempty'), 0, 'testdirectory contains no true files at all <%s>');
		
		$this->expectException('Exception', 'missing directory causes exception <%s>');
		ToolBox::_filesystem_()->countDirectoryFiles(dirname(__FILE__).'/../misc/doesnotexist');
	}
	
	
	
	public function testSearchForFiles(){
		$this->assertEqual(ToolBox::_filesystem_()->searchForFiles(dirname(__FILE__).'/../misc', 'abc-file1.txt'), array('/abc-file1.txt'), 'search for existing name must come up with a result <%s>');
		$this->assertEqual(ToolBox::_filesystem_()->searchForFiles(dirname(__FILE__).'/../misc', 'def-file1.txt'), array(), 'search for non-existing name must come up with an empty result <%s>');
		$this->assertEqual(ToolBox::_filesystem_()->searchForFiles(dirname(__FILE__).'/../misc', '/^[a-z]{3}\-file[0-9]\.txt$/', true), array('/abc-file1.txt', '/def-file2.txt', '/mustntbeempty/ghi-file3.txt'), 'recursive search for all files matching regexp must find list of matching files <%s>');
		
		$this->expectException('Exception', 'missing directory causes exception <%s>');
		ToolBox::_filesystem_()->searchForFiles(dirname(__FILE__).'/../misc/doesnotexist', 'nothing');
	}
	
}

?>