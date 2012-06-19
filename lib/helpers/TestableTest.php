<?php

class Testable
{
	private static $calls;

	public static function __callStatic($name, $arguments)
	{
		if (!isset(self::$calls[$name])) self::$calls[$name] = array();
		self::$calls[$name][] = $arguments;
		
		$callId = self::getCalledCount($name) - 1;
		
		if (isset(self::$returns[$name][$callId])) return self::$returns[$name][$callId];
	}
	
	public static function refresh()
	{
		self::$calls = array();
		self::$returns = array();
	}
	
	public static function getCalledCount($function)
	{
		return isset(self::$calls[$function]) ? count(self::$calls[$function]) : 0;
	}
	
	public static function getArguments($function, $callId = null)
	{
		if (is_null($callId))
		{
			return isset(self::$calls[$function]) ? self::$calls[$function] : array();
		}
		else
		{
			return isset(self::$calls[$function][$callId]) ? self::$calls[$function][$callId] : null;
		}
	}
	
	private static $returns;
	
	public static function setReturnValue($function, $callId, $returnValue)
	{
		if (!isset(self::$returns[$function])) self::$returns[$function] = array();
		
		self::$returns[$function][$callId] = $returnValue;
	}
}