<?php

namespace RadioFM;

class NowPlaying
{
	private static $URL = 'http://radiofm.sk/sepia/lastsong_fm.xml';

	public static function get($last = null)
	{
		$response = self::request(is_null($last) ? null : $last['time_unix']);
		
		if (self::shouldUseCached($last, $response)) return $last;
		else return self::parse($response['content']);
	}
	
	private static function request($lastTime = null)
	{
		$curl = \Testable::curl_init(self::$URL);

		\Testable::curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		\Testable::curl_setopt($curl, CURLOPT_HTTPHEADER, self::requestHeaders($lastTime));

		$content = \Testable::curl_exec($curl);		
		$info = \Testable::curl_getinfo($curl);
				
		if ($info['http_code'] != 200 && $info['http_code'] != 304)	throw new ServiceException("Service lastsong_fm.xml returned status code {$info['http_code']}, with response: '$content'", $info['http_code']);	
		if ($content === false) throw new ServiceException(\Testable::curl_error($curl), \Testable::curl_errno($curl));
			
		\Testable::curl_close($curl);
		
		return [ 'info' => $info, 'content' => $content ];
	}
	
	private static function requestHeaders($lastTime = null)
	{
		$headers = [
			'Accept: application/xml'
		];
							
		if (!is_null($lastTime))
		{
			$date = new \DateTime('@'.$lastTime);
			$headers[] = 'If-Modified-Since: ' . $date->format(\DateTime::RFC1123);
		}
		
		return $headers;
	}
	
	private static function shouldUseCached($last, $response)
	{
		if ($response['info']['http_code'] == 304)
		{
			if (is_null($last))
			{
				throw new ServiceException("Service lastsong_fm.xml returned status code 304, but no last data were used", 304);
			}
			else
			{
				return true;
			}
		}
		else
		{
			return false;
		}
	}
	
	private static function parse($xml)
	{
		$playlist = new \SimpleXMLElement($xml);
		$track = $playlist->track;	
		
		return [
			'id' => (int) $track->id,
			'time_unix' => (int) $track->time_unix,
			'artist' => (string) $track->artist,
			'album' => (string) $track->album,
			'song' => (string) $track->song,
			'cover' => (string) $track->cover,
			'program' => (string) $track->program
		];
	}
}

class ServiceException extends \Exception {}
