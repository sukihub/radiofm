<?php

namespace Services;

require_once 'models/LastPlayedRadioFM.php';
require_once 'lib/radiofm/NowPlaying.php';

class Update
{	
	private static $KEY = 'last';

	public function collection_get()
	{
		$NowPlaying = self::$NowPlaying;
		$LastPlayed = self::$LastPlayed;
			
		$last = $this->mc->get(self::$KEY);
		
		if ($last === false) $now = $NowPlaying::get();
		else $now = $NowPlaying::get($last);
		
		if ($now == $last) return;
		
		$LastPlayed::createFromResponse($now);
		$this->mc->set(self::$KEY, $now);
	}

	public function __construct($db, $mc)
	{
		$this->db = $db;
		$this->mc = $mc;
	}
	
	public static $LastPlayed = '\Models\LastPlayedRadioFM';
	public static $NowPlaying = '\RadioFM\NowPlaying';
}