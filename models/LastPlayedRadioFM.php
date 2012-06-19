<?php

namespace Models;

class LastPlayedRadioFM
{	
	private static $db;
	
	public static function createFromResponse($track)
	{
		$statement = self::$db->prepare(
			"INSERT INTO last_played_radiofm
			 (radiofm_id, played_at, artist, album, song, cover, program) VALUES
			 (?, ?, ?, ?, ?, ?, ?)"
		);
		
		$playedAt = new \DateTime('@' . $track['time_unix']);
		$playedAt->setTimezone(new \DateTimeZone('UTC'));
		
		$statement->bind_param(
			'issssss',
			$track['id'], 
			$playedAt->format('Y-m-d H:i:s'),
			$track['artist'],
			$track['album'],
			$track['song'],
			$track['cover'],
			$track['program']
		);
		
		$statement->execute();
		$statement->close();
	}
	
	public static function last()
	{
		$result = self::$db->query(
			"SELECT * FROM last_played_radiofm ORDER BY id DESC LIMIT 1"
		);
		
		if ($result->num_rows != 1) throw new Exception("1 row was expected, {$result->num_rows} fetched");
		
		$row = $result->fetch_assoc();
		$result->close();
		
		return $row;
	}
	
	public static function connect($db)
	{
		self::$db = $db;
	}
}