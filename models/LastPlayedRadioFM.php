<?php

namespace Models;

class LastPlayedRadioFM
{	
	private static $db;
	
	public static function createFromResponse($track)
	{
		$statement = self::$db->prepare(
			"INSERT INTO last_played_radiofm
			 ( radiofm_id,  played_at,  artist,  album,  song,  cover,  program) VALUES
			 (:radiofm_id, :played_at, :artist, :album, :song, :cover, :program)"
		);
		
		$playedAt = new \DateTime('@' . $track['time_unix']);
		$playedAt->setTimezone(new \DateTimeZone('UTC'));
		$formated = $playedAt->format('Y-m-d H:i:s');
		
		$statement->bindParam(':radiofm_id', $track['id'], \PDO::PARAM_INT); 
		$statement->bindParam(':played_at', $formated, \PDO::PARAM_STR);
		$statement->bindParam(':artist', $track['artist'], \PDO::PARAM_STR);
		$statement->bindParam(':album', $track['album'], \PDO::PARAM_STR);
		$statement->bindParam(':song', $track['song'], \PDO::PARAM_STR);
		$statement->bindParam(':cover', $track['cover'], \PDO::PARAM_STR);
		$statement->bindParam(':program', $track['program'], \PDO::PARAM_STR);
		
		$statement->execute();
	}
	
	public static function last()
	{
		$statement = self::$db->query(
			"SELECT radiofm_id AS id, UNIX_TIMESTAMP(played_at) as time_unix, artist, album, song, cover, program 
			 FROM last_played_radiofm ORDER BY id DESC LIMIT 1"
		);
		
		$rowCount = $statement->rowCount();
		if ($rowCount != 1) throw new LastPlayedException("1 row was expected, {$rowCount} fetched");
		
		$row = $statement->fetch(\PDO::FETCH_ASSOC);
		
		return $row;
	}
	
	public static function connect($db)
	{
		self::$db = $db;
	}
}

class LastPlayedException extends \Exception {}