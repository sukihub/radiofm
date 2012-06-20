<?php

namespace Services;

require_once 'test/TestHelper.php';
require_once 'services/Update.php';

class Memcache
{
	public function get($key = '') {}
	public function set($key = '', $value = '') {}
}

class UpdateTest extends \TestCase 
{
	public function setUp()
	{
		$this->db = $this->getMock('\MockPDO');
		$this->mc = $this->getMock('\Services\Memcache', [], [], '', false);
		
		$this->lastPlayed = $this->getMockClass('\Models\LastPlayedRadioFM', [], [], '', false);
		\Services\Update::$LastPlayed = $this->lastPlayed; 
		
		$this->nowPlaying = $this->getMockClass('\RadioFM\NowPlaying', [], [], '', false);
		\Services\Update::$NowPlaying = $this->nowPlaying; 
		
		$this->service = new \Services\Update($this->db, $this->mc);
	}
	
	private function setupMemcache($key, $value)
	{
		$this->mc
			->expects($this->any())
			->method('get')
			->with($key)
			->will($this->returnValue($value));
	}
	
	public function testCollectionGetShouldGetLastResultFromMemcache() 
	{
		$this->mc
			->expects($this->once())
			->method('get')
			->with('last');
			
		$this->service->collection_get();
	}
		
	//public function testCollectionGetShouldGetLastResultFromDatabaseIfMemcacheIsEmpty() 
	//{
	//	$this->setupMemcache('last', false);
	//	
	//	$this->lastPlayed
	//		::staticExpects($this->once())
	//		->method('last')
	//		->will($this->returnValue( [ 'radiofm_id' => 154 ] ));
	//		
	//	$this->service->collection_get();		
	//}
	
	public function testCollectionGetShouldCallNowPlayingWithNoLastResultIfNotInMemcache() 
	{
		$this->setupMemcache('last', false);
				
		$np = $this->nowPlaying;
		$np::staticExpects($this->once())
			->method('get')
			->with($this->isNull());
			
		$this->service->collection_get();
	}
	
	public function testCollectionGetShouldCallNowPlayingWithLastResultIfInMemcache() 
	{
		$last = [ 'id' => 15654, 'time_unix' => 68798 ];
		$this->setupMemcache('last', $last);
		
		$np = $this->nowPlaying;
		$np::staticExpects($this->once())
			->method('get')
			->with($last);
			
		$this->service->collection_get();
	}	
		
	public function testCollectionGetShouldInsertNewSongIntoMemcacheIfNowAndLastAreNotSame()
	{
		$now = [ 'id' => 15654, 'time_unix' => 68798 ];
		$last = [ 'id' => 15653, 'time_unix' => 68795 ];
		$this->setupMemcache('last', $last);
	
		$np = $this->nowPlaying;
		$np::staticExpects($this->once())
			->method('get')
			->will($this->returnValue($now));
			
		$this->mc
			->expects($this->once())
			->method('set')
			->with('last', $now);
		
		$this->service->collection_get();
	}
	
	public function testCollectionGetShouldSaveNewSongIntoDBIfNowAndLastAreNotSame()
	{
		$now = [ 'id' => 15654, 'time_unix' => 68798 ];
		$last = [ 'id' => 15653, 'time_unix' => 68795 ];
		$this->setupMemcache('last', $last);
	
		$np = $this->nowPlaying;
		$np::staticExpects($this->once())
			->method('get')
			->will($this->returnValue($now));
			
		$lp = $this->lastPlayed;
		$lp::staticExpects($this->once())
			->method('createFromResponse')
			->with($now);
		
		$this->service->collection_get();
	}
	
	public function testCollectionGetShouldDoNothingIfNowAndLastAreSame()
	{
		$last = [ 'id' => 15654, 'time_unix' => 68798 ];
		$this->setupMemcache('last', $last);
	
		$np = $this->nowPlaying;
		$np::staticExpects($this->once())
			->method('get')
			->will($this->returnValue($last));
	
		$this->mc
			->expects($this->never())
			->method('set');
			
		$lp = $this->lastPlayed;
		$lp::staticExpects($this->never())
			->method('createFromResponse');
			
		$this->service->collection_get();
	}
}