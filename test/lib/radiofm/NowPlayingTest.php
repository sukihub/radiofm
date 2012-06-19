<?php

namespace RadioFM;

require_once 'test/TestHelper.php';
require_once 'lib/radiofm/NowPlaying.php';

class NowPlayingTest extends \TestCase
{
	private $sampleResponse = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<playlist>
	<track>
		<id>16854</id>
		<time_unix>1339943325</time_unix>
		<time>17.06.2012 16:28:45</time>
		<artist>The Asteroids Galaxy Tour</artist>
		<song>Major (radio Edit)</song>
		<album></album>
		<cover>http://www.sepia.sk/tuner/cover/cddisc.jpg</cover>
		<program>Víkend_FM</program>
	</track>
</playlist>
XML;

	private function setUpSuccessfulCurl()
	{
		\Testable::refresh();
		\Testable::setReturnValue('curl_getinfo', 0, [ 'http_code' => 200 ]);
		\Testable::setReturnValue('curl_exec', 0, $this->sampleResponse);
	}

	public function testGetShouldCallLastsongService()
	{			
		$this->setUpSuccessfulCurl();	
		$now = NowPlaying::get();
		
		$this->assertEquals(1, \Testable::getCalledCount('curl_init'));
		$this->assertEquals(1, \Testable::getCalledCount('curl_exec'));
		$this->assertEquals(1, \Testable::getCalledCount('curl_close'));
		
		$this->assertEquals(['http://radiofm.sk/sepia/lastsong_fm.xml'], \Testable::getArguments('curl_init', 0));
	}
	
	public function testGetShouldSetReturnTransferOption()
	{			
		$this->setUpSuccessfulCurl();
		$now = NowPlaying::get();
		
		$args = \Testable::getArguments('curl_setopt', 0);
		
		$this->assertEquals(CURLOPT_RETURNTRANSFER, $args[1]);
		$this->assertTrue($args[2]);
	}
	
	/**
	 * @expectedException RadioFM\ServiceException
	 */
	public function testGetShouldThrowExceptionIfCurlExecReturnsFalse()
	{
		\Testable::refresh();
		\Testable::setReturnValue('curl_exec', 0, false);
		
		$now = NowPlaying::get();
	}
	
	public function testGetShouldSendRequestWithAcceptXmlHeader()
	{
		$this->setUpSuccessfulCurl();
		$now = NowPlaying::get();
		
		$args = \Testable::getArguments('curl_setopt', 1);

		$this->assertEquals(CURLOPT_HTTPHEADER, $args[1]);		
		$this->assertContains('Accept: application/xml', $args[2]);
	}
	
	public function testGetShouldReadHttpStatusCode()
	{
		$this->setUpSuccessfulCurl();
		$now = NowPlaying::get();
		
		$this->assertEquals(1, \Testable::getCalledCount('curl_getinfo'));
	}
	
	/**
	 * @expectedException RadioFM\ServiceException
	 */
	public function testGetShouldThrowExceptionIfHttpStatusIsOtherThan200()
	{
		\Testable::refresh();					
		\Testable::setReturnValue('curl_getinfo', 0, [ 'http_code' => 500 ]);
				
		$now = NowPlaying::get();
	}
	
	public function testGetShouldReturnArray()
	{
		$this->setUpSuccessfulCurl();
		$now = NowPlaying::get();
		
		$this->assertInternalType('array', $now);
	}
	
	public function testGetShouldCorrectlyParseXML()
	{
		$this->setUpSuccessfulCurl();
		$now = NowPlaying::get();
		
		$this->assertInternalType('integer', $now['id']);
		$this->assertEquals(16854, $now['id']);
		
		$this->assertInternalType('integer', $now['time_unix']);
		$this->assertEquals(1339943325, $now['time_unix']);
		
		$this->assertInternalType('string', $now['artist']);
		$this->assertEquals('The Asteroids Galaxy Tour', $now['artist']);
		
		$this->assertInternalType('string', $now['album']);
		$this->assertEquals('', $now['album']);
		
		$this->assertInternalType('string', $now['song']);
		$this->assertEquals('Major (radio Edit)', $now['song']);
		
		$this->assertInternalType('string', $now['cover']);
		$this->assertEquals('http://www.sepia.sk/tuner/cover/cddisc.jpg', $now['cover']);
		
		$this->assertInternalType('string', $now['program']);
		$this->assertEquals('Víkend_FM', $now['program']);
	}
	
	public function testGetShouldSetRequestWithIfModifiedSinceIfLastTimeGiven()
	{
		$this->setUpSuccessfulCurl();
		$now = NowPlaying::get(['time_unix' => 1339943325]);
		
		$args = \Testable::getArguments('curl_setopt', 1);

		$this->assertEquals(CURLOPT_HTTPHEADER, $args[1]);		
		$this->assertContains('If-Modified-Since: Sun, 17 Jun 2012 14:28:45 +0000', $args[2]);			
	}
	
	public function testGetShouldNotThrowServiceExceptionIfLastTimeGivenAndStatusCodeIs304()
	{
		\Testable::refresh();					
		\Testable::setReturnValue('curl_exec', 0, '');
		\Testable::setReturnValue('curl_getinfo', 0, [ 'http_code' => 304 ]);
				
		$now = NowPlaying::get(['time_unix' => 1339943325]);
	}
	
	public function testGetShouldReturnInputIfLastTimeGivenAndStatusCodeIs304()
	{
		\Testable::refresh();					
		\Testable::setReturnValue('curl_exec', 0, '');
		\Testable::setReturnValue('curl_getinfo', 0, [ 'http_code' => 304 ]);
				
		$last = ['time_unix' => 1339943325];
				
		$now = NowPlaying::get($last);
		
		$this->assertSame($last, $now);
	}
	
	/**
	 * @expectedException RadioFM\ServiceException
	 */
	public function testGetShouldThrowExceptionIfStatusCodeIs304AndNoLastTimeWasGiven()
	{
		\Testable::refresh();					
		\Testable::setReturnValue('curl_exec', 0, '');
		\Testable::setReturnValue('curl_getinfo', 0, [ 'http_code' => 304 ]);
		
		$now = NowPlaying::get();
	}
	
	public function testGetShouldNotReturnInputIfLastTimeGivenAndStatusCodeIs200()
	{
		$this->setUpSuccessfulCurl();
				
		$last = ['time_unix' => 1339943325];				
		$now = NowPlaying::get($last);
		
		$this->assertNotSame($last, $now);
	}
}
