<?php

require_once 'lib/helpers/TestableTest.php';

date_default_timezone_set("Europe/Bratislava");

class TestCase extends PHPUnit_Framework_TestCase
{		
	public function matchesQuery()
	{
		$parts = array();		
		for ($i = 0; $i < func_num_args(); $i++) $parts[] = str_replace(' ', '\s*', preg_quote(func_get_arg($i)));
	
		$regexp = implode('.*', $parts);
	
		return $this->matchesRegularExpression("/$regexp/ism");
	}
}

class MockPDO extends PDO 
{
	public function __construct() {}
}

class MockPDOStatement extends PDOStatement 
{ 
	public function __construct() {}
}