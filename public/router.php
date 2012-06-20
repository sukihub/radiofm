<?php

class Resources
{
	private static $db = null;

	public static function database()
	{
		if (self::$db !== null) return self::$db;
		
		self::$db = new PDO(
			"mysql:unix_socket=/tmp/mysql51.sock;dbname=uvidime",
			'meno',	'heslo'
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
		
		$fragments = explode('/', $_REQEST['path']);
		
		$serviceName = ucfirst($fragments[0]);
		$method = strtolower($_SERVER['REQUEST_METHOD']);
		
		$serviceFile = "services/$serviceName.php";
		$serviceClass = "\\Services\\$serviceName";
		
		if (!file_exists($serviceFile)) throw new NotFoundException("Service '$serviceName' does not exist");
		if (!class_exists($serviceClass)) throw new NotFoundException("Service '$serviceName' does not exist");
		
		$action = isset($_REQUEST['id']) ? "item_$method" : "collection_$method";
		
		$service = new $serviceClass(Resources::database(), Resources::memcache());
		
		if (!is_callable(array($service, $action))) throw new NotFoundException("Service '$serviceName' does not provide action '$action'");

		echo $service->$action();
	}
}

date_default_timezone_set('Europe/Bratislava');

try
{
	Router::route($_REQUEST['path']);
}
catch (HttpException $e)
{
	header("HTTP/1.1 {$e->getCode()} {$e->getMessage()}");
	echo $e->details();
}