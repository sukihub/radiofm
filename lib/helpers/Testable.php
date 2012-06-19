<?php

class Testable
{
	public static function __callStatic($name, $arguments)
	{
		return call_user_func_array($name, $arguments);
	}	
}