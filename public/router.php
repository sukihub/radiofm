<?php

set_include_path(get_include_path() . PATH_SEPARATOR . $_SERVER['DOCUMENT_ROOT']);
date_default_timezone_set('Europe/Bratislava');

require_once 'lib/helpers/HttpExceptions.php';
require_once 'lib/helpers/Testable.php';

class Resources
{
	private static $db = null;

	public static function database()
	{
		if (self::$db !== null) return self::$db;
		
		self::$db = new PDO(
			"mysql:unix_socket=/tmp/mysql51.sock;dbname=now_playing",
			'now_playing',	':odguvbej1'
		);
		
		return self::$db;
	}
	
	private static $mc = null;
	
	public static function memcache()
	{
		if (self::$mc !== null) return self::$mc;
		
		self::$mc = new Memcache();
		self::$mc->connect('localhost', '11211');
		
		return self::$mc;
	}
}

class Router
{
	public static function route($path)
	{
		parse_str(file_get_contents('php://input'), $_REQUEST);
		
		$fragments = explode('/', $path);
		
		$serviceName = ucfirst($fragments[0]);
		$method = strtolower($_SERVER['REQUEST_METHOD']);
		
		$serviceFile = "services/$serviceName.php";
		$serviceClass = "\\Services\\$serviceName";
		
		if (!file_exists($_SERVER['DOCUMENT_ROOT'] . '/' . $serviceFile)) throw new NotFoundException("Service file '$serviceName' does not exist");
		require_once $serviceFile;
		
		if (!class_exists($serviceClass)) throw new NotFoundException("Service '$serviceName' does not exist");
		
		$action = isset($_REQUEST['id']) ? "item_$method" : "collection_$method";
		
		$service = new $serviceClass(Resources::database(), Resources::memcache());
		
		if (!is_callable(array($service, $action))) throw new NotFoundException("Service '$serviceName' does not provide action '$action'");

		echo $service->$action();
	}
}

try
{
	Router::route($_REQUEST['path']);
}
catch (HttpException $e)
{
	header("HTTP/1.1 {$e->getCode()} {$e->getMessage()}");
	echo $e->details();
}