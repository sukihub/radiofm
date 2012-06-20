<?php

class HttpException extends RuntimeException
{
	private static $codes = array(
		400 => 'Bad Request',
		401 => 'Unauthorized',
		403 => 'Forbidden',
		404 => 'Not Found',
		405 => 'Method Not Allowed',
		500 => 'Internal Server Error'
	);
	
	protected $details = null;
	protected $headerFields = array();
	
	public function __construct($statusCode, $details = null)
	{
		if (!array_key_exists($statusCode, self::$codes)) $statusCode = 500;
		
		parent::__construct(self::$codes[$statusCode], $statusCode);
		
		$this->details = $details;
	}
	
	public function details()
	{
		return $this->details;
	}
	
	public function headerFields()
	{
		return $this->headerFields;
	}
}

class BadRequestException extends HttpException
{
	public function __construct($details = null)
	{
		parent::__construct(400, $details);
	}
}

class NotFoundException extends HttpException
{
	public function __construct($details = null)
	{
		parent::__construct(404, $details);
	}
}

class MethodNotAllowedException extends HttpException
{
	public function __construct($allowedMethods = array(), $details = null)
	{
		parent::__construct(405, $details);
		
		$this->headerFields['Allow'] = implode(', ', $allowedMethods);
	}
}

class UnauthorizedException extends HttpException
{
	public function __construct($details = null)
	{
		parent::__construct(401, $details);
		
		$this->headerFields['WWW-Authenticate'] = 'Custom';
	}
}

class InternalServerError extends HttpException
{
	public function __construct($details = null)
	{
		parent::__construct(500, $details);
	}
}

class ForbiddenException extends HttpException
{
	public function __construct($details = null)
	{
		parent::__construct(403, $details);
	}
}
